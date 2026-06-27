<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\EditionPageText;
use App\Support\PdfExtractedTextSanitizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class EditionPdfTextExtractor
{
    /**
     * Resultado da extração para uso pelo controller/console.
     *
     * @var array{pages_with_text:int, pages_total:int, skipped_manual:int, has_text_layer:bool}
     */
    public array $lastSummary = [
        'pages_with_text' => 0,
        'pages_total' => 0,
        'skipped_manual' => 0,
        'has_text_layer' => false,
    ];

    /**
     * Extrai o texto de uma edição (com PDF) e popula edition_page_texts.
     *
     * Retorna o número de páginas com texto extraído.
     */
    public function extractFromEdition(Edition $edition, bool $force = false): int
    {
        $this->lastSummary = [
            'pages_with_text' => 0,
            'pages_total' => 0,
            'skipped_manual' => 0,
            'has_text_layer' => false,
        ];

        if (! $edition->pdf_file) {
            return 0;
        }

        if (Edition::isAbsoluteUrl($edition->pdf_file)) {
            Log::info('EditionPdfTextExtractor: PDF externo (URL), extração ignorada.', [
                'edition_id' => $edition->id,
                'pdf_file' => $edition->pdf_file,
            ]);

            return 0;
        }

        if (! Storage::disk('public')->exists($edition->pdf_file)) {
            Log::warning('EditionPdfTextExtractor: PDF não encontrado no storage.', [
                'edition_id' => $edition->id,
                'pdf_file' => $edition->pdf_file,
            ]);

            return 0;
        }

        $absolutePath = Storage::disk('public')->path($edition->pdf_file);

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($absolutePath);
            $pages = $pdf->getPages();
        } catch (\Throwable $e) {
            Log::error('EditionPdfTextExtractor: falha ao abrir o PDF.', [
                'edition_id' => $edition->id,
                'pdf_file' => $edition->pdf_file,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }

        $this->lastSummary['pages_total'] = count($pages);
        $existing = EditionPageText::where('edition_id', $edition->id)
            ->get()
            ->keyBy('page_label');

        $seenLabels = [];
        $pagesWithText = 0;

        foreach ($pages as $index => $page) {
            $pageNumber = $index + 1;
            $label = (string) $pageNumber;
            $seenLabels[] = $label;

            $current = $existing->get($label);
            if ($current && $current->manually_edited && ! $force) {
                $this->lastSummary['skipped_manual']++;

                continue;
            }

            $bodyHtml = $this->extractPageHtml($page);
            if ($bodyHtml !== null && $bodyHtml !== '') {
                $pagesWithText++;
                $this->lastSummary['has_text_layer'] = true;
            }

            EditionPageText::updateOrCreate(
                [
                    'edition_id' => $edition->id,
                    'page_label' => $label,
                ],
                [
                    'page_number' => $pageNumber,
                    'body_html' => $bodyHtml,
                    'manually_edited' => false,
                ]
            );
        }

        EditionPageText::where('edition_id', $edition->id)
            ->whereNotIn('page_label', $seenLabels)
            ->where('manually_edited', false)
            ->delete();

        $this->lastSummary['pages_with_text'] = $pagesWithText;

        return $pagesWithText;
    }

    /**
     * Converte o texto de uma página do PDF em HTML simples (um parágrafo por bloco).
     */
    protected function extractPageHtml($page): ?string
    {
        try {
            $text = $page->getText();
        } catch (\Throwable $e) {
            return null;
        }

        if (! is_string($text)) {
            return null;
        }

        $text = $this->cleanPageText($text);
        $text = PdfExtractedTextSanitizer::sanitize($text);
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        if ($this->isTableOfContentsPage($text)) {
            return $this->formatTableOfContentsHtml($text);
        }

        $paragraphs = [];
        foreach ($this->splitIntoParagraphs($text) as $block) {
            $block = $this->reflowBlock($block);
            if ($block === '') {
                continue;
            }
            $paragraphs[] = '<p>'.e($block).'</p>';
        }

        return $paragraphs === [] ? null : implode("\n", $paragraphs);
    }

    /**
     * Agrupa linhas do PDF em parágrafos.
     *
     * O extrator emite quebras simples no fim de cada linha da coluna; parágrafos
     * reais só aparecem como linha em branco ou quando uma frase termina e a
     * próxima começa com letra maiúscula.
     *
     * @return array<int, string>
     */
    protected function splitIntoParagraphs(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);
        $paragraphs = [];
        $current = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                if ($current !== '') {
                    $paragraphs[] = $current;
                    $current = '';
                }

                continue;
            }

            if ($current === '') {
                $current = $line;

                continue;
            }

            if ($this->shouldStartNewParagraph($current, $line)) {
                $paragraphs[] = $current;
                $current = $line;
            } else {
                $current = $this->joinContinuationLine($current, $line);
            }
        }

        if ($current !== '') {
            $paragraphs[] = $current;
        }

        return $paragraphs;
    }

    protected function shouldStartNewParagraph(string $previous, string $next): bool
    {
        $previous = rtrim($previous);
        $next = ltrim($next);

        if ($next === '' || $previous === '') {
            return false;
        }

        if (preg_match('/^(\d+[\.\)]|[-•●▪*])\s+\p{L}/u', $next)) {
            return true;
        }

        if (
            mb_strlen($next) <= 80
            && preg_match('/^\p{Lu}/u', $next)
            && mb_strtoupper($next, 'UTF-8') === $next
            && preg_match('/\p{L}/u', $next)
            && preg_match('/[.!?:…"\'»”\')]\s*$/u', $previous)
        ) {
            return true;
        }

        if (preg_match('/[.!?:…]["\'»”\')]*\s*$/u', $previous) && preg_match('/^["\'«“(]?\p{Lu}/u', $next)) {
            return true;
        }

        return false;
    }

    protected function joinContinuationLine(string $previous, string $next): string
    {
        if (preg_match('/(\p{L})[ \t]*-[ \t]*$/u', $previous) && preg_match('/^\p{Ll}/u', $next)) {
            return preg_replace('/[ \t]*-[ \t]*$/u', '', $previous).$next;
        }

        return rtrim($previous).' '.ltrim($next);
    }

    /**
     * Normaliza espaços em um parágrafo já montado (reflow final).
     */
    protected function reflowBlock(string $block): string
    {
        return trim(PdfExtractedTextSanitizer::normalizeInlineWhitespace($block));
    }

    protected function isTableOfContentsPage(string $text): bool
    {
        $normalized = PdfExtractedTextSanitizer::normalizeInlineWhitespace($text);

        if (preg_match('/\bSUM[ÁA]RIO\b/ui', $normalized)) {
            return true;
        }

        return preg_match_all(
            '/\b\d{1,3}\s+[A-ZÁÉÍÓÚÂÊÔÃÕÇ][A-ZÁÉÍÓÚÂÊÔÃÕÇA-Záéíóúâêôãõç\s\-–—\.]{2,}/u',
            $normalized
        ) >= 4;
    }

    protected function formatTableOfContentsHtml(string $text): ?string
    {
        $text = $this->stripAdobeAnnotationJunk($text);
        $text = PdfExtractedTextSanitizer::normalizeInlineWhitespace($text);
        $parts = [];

        if (preg_match('/^(.*?)\bSUM[ÁA]RIO\b\s*(.*)$/ui', $text, $headingMatch)) {
            $before = trim($headingMatch[1]);
            if ($before !== '') {
                $parts[] = '<p>'.e($before).'</p>';
            }
            $parts[] = '<h3 class="font-serif text-xl text-red-900 mb-4">Sumário</h3>';
            $text = trim($headingMatch[2]);
        }

        preg_match_all(
            '/(\d{1,3})\s+([A-ZÁÉÍÓÚÂÊÔÃÕÇ][A-ZÁÉÍÓÚÂÊÔÃÕÇA-Záéíóúâêôãõç\s\-–—\.\']+?)(?=\s\d{1,3}\s+[A-ZÁÉÍÓÚ]|$)/u',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        if (count($matches) >= 3) {
            $items = [];
            foreach ($matches as $match) {
                $page = trim($match[1]);
                $title = trim(PdfExtractedTextSanitizer::normalizeInlineWhitespace($match[2]));
                if ($title !== '') {
                    $items[] = '<li class="flex gap-3"><span class="shrink-0 font-medium tabular-nums">'.$page.'</span><span>'.e($title).'</span></li>';
                }
            }
            if ($items !== []) {
                $parts[] = '<ul class="list-none space-y-1.5">'.implode("\n", $items).'</ul>';
            }
        } else {
            foreach ($this->splitIntoParagraphs($text) as $block) {
                $block = $this->reflowBlock($block);
                if ($block !== '') {
                    $parts[] = '<p>'.e($block).'</p>';
                }
            }
        }

        return $parts === [] ? null : implode("\n", $parts);
    }

    protected function stripAdobeAnnotationJunk(string $text): string
    {
        $text = preg_replace('/\bID\s+DA\s+ANOTA[ÇC][ÃA]O\b.*$/ius', '', $text) ?? $text;

        return trim($text);
    }

    /**
     * Remove "informativos" comuns das edições da Revista Catolicismo que aparecem
     * misturados no texto extraído (expediente, cabeçalhos/rodapés de página, etc.).
     *
     * Esta função é pragmática e específica para o layout atual da revista —
     * pode (e deve) ser ajustada conforme novos padrões forem identificados.
     */
    public function cleanPageText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // 1) Remove o BLOCO DO EXPEDIENTE inteiro.
        //    Detectado pela presença simultânea de "Diretor:" + "Jornalista Responsável:"
        //    (ou pelo menos 2 marcadores muito específicos próximos no texto).
        $mastheadAnchors = [
            '/\bDiretor:\s*\n/u',
            '/\bJornalista\s+Respons[áa]vel:/u',
            '/\bAdministra[çc][ãa]o:\s*\n/u',
            '/\bServi[çc]o\s+de\s+Atendimento\b/u',
            '/\bPre[çc]os\s+da\s*\n*\s*assinatura/u',
            '/\bPublica[çc][ãa]o\s+mensal\s+da\s+Editora\b/u',
            '/\bISSN\s+\d{4}-\d{4}/u',
        ];

        $firstHit = null;
        $hits = 0;
        foreach ($mastheadAnchors as $rgx) {
            if (preg_match($rgx, $text, $m, PREG_OFFSET_CAPTURE)) {
                $hits++;
                if ($firstHit === null || $m[0][1] < $firstHit) {
                    $firstHit = $m[0][1];
                }
            }
        }

        if ($firstHit !== null && $hits >= 2) {
            $before = substr($text, 0, $firstHit);
            // Recua para incluir o título do bloco ("EDITORIAL\nCATOLICISMO\n" ou "CATOLICISMO\n")
            // que vem imediatamente antes do "Diretor:".
            $before = preg_replace(
                "/\n+(?:EDITORIAL\s*\n+)?CATOLICISMO\s*\n+\s*$/u",
                "\n",
                $before
            );
            $text = rtrim($before);
        }

        // 2) Remove o RODAPÉ "Petrus Editora Ltda." quando vier seguido de endereço/fones —
        //    é uma assinatura de anúncio recorrente da editora parceira.
        $text = preg_replace(
            "/\bPetrus\s+Editora\s+Ltda\.[\s\S]{0,400}?(?:E-mail:\s*petrus@livrariapetrus[^\s]*|www\.livrariapetrus\.com\.br)/u",
            '',
            $text
        );

        // 3) Remove HEADERS/FOOTERS de página: "NNwww.catolicismo.com.br" ou "NNCATOLICISMO .Mês AAAA".
        //    Estes aparecem coladinhos no número da página e sujam o início/fim do texto.
        $headerPatterns = [
            // ex.: "12www.catolicismo.com.br" (com ou sem espaços)
            '/(^|\n)\s*\d{1,3}\s*www\.catolicismo\.com\.br\s*(\n|$)/iu',
            // ex.: "12CATOLICISMO .Novembro 2025"
            '/(^|\n)\s*\d{1,3}\s*CATOLICISMO\s*\.?[A-Za-zÀ-ÿ]+\s+\d{4}\s*(\n|$)/u',
            // só "www.catolicismo.com.br" sozinho em uma linha
            '/(^|\n)\s*www\.catolicismo\.com\.br\s*(\n|$)/iu',
            // só "CATOLICISMO .Novembro 2025" em uma linha
            '/(^|\n)\s*CATOLICISMO\s*\.?[A-Za-zÀ-ÿ]+\s+\d{4}\s*(\n|$)/u',
        ];
        foreach ($headerPatterns as $rgx) {
            $text = preg_replace($rgx, "\n", $text);
        }

        // 4) Remove número de página solto no início (ex.: "3" sozinho, possivelmente seguido de URL/cabeçalho).
        $text = preg_replace("/^\s*\d{1,3}\s*\n/u", '', $text);

        // 5) Remove lixo de anotações Adobe colado no texto.
        $text = $this->stripAdobeAnnotationJunk($text);

        // 6) Normaliza múltiplas linhas em branco.
        $text = preg_replace("/\n{3,}/u", "\n\n", $text);

        return trim($text);
    }
}

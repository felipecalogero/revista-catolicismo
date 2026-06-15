<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\EditionPageText;
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

        // Limpa páginas obsoletas (ex: PDF substituído por versão com menos páginas),
        // preservando entradas marcadas como manualmente editadas.
        EditionPageText::where('edition_id', $edition->id)
            ->whereNotIn('page_label', $seenLabels)
            ->where('manually_edited', false)
            ->delete();

        $this->lastSummary['pages_with_text'] = $pagesWithText;

        return $pagesWithText;
    }

    /**
     * Converte o texto de uma página do PDF em HTML simples (1 parágrafo por bloco de texto).
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
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        // Normaliza quebras de linha e separa em parágrafos por linha em branco.
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $blocks = preg_split("/\n{2,}/u", $normalized) ?: [];

        $paragraphs = [];
        foreach ($blocks as $block) {
            $block = $this->reflowBlock($block);
            if ($block === '') {
                continue;
            }
            $paragraphs[] = '<p>'.e($block).'</p>';
        }

        return $paragraphs === [] ? null : implode("\n", $paragraphs);
    }

    /**
     * Reflui um bloco de texto extraído do PDF (que vem com quebras de linha
     * nas larguras da coluna original) em texto corrido, juntando linhas e
     * desfazendo hifenizações de fim de linha.
     */
    protected function reflowBlock(string $block): string
    {
        // Junta hifenizações de fim de linha. O PDF emite a hifenização de duas formas:
        //   - "inú-\nmeras"   (sem whitespace ao redor do hífen)
        //   - "per\t-\nguntou" (whitespace antes do hífen por justificação da linha)
        // Em ambos os casos, a próxima linha começa com letra MINÚSCULA (continuação da palavra).
        // Não interfere em em-dash (U+2014) usado entre frases.
        $block = preg_replace("/(\p{L})[ \t]*-[ \t]*\n[ \t]*(\p{Ll})/u", '$1$2', $block);

        // Junta linhas que pertencem ao mesmo parágrafo (separadas por \n simples).
        $block = preg_replace("/\s*\n+\s*/u", ' ', $block);

        // Normaliza múltiplos espaços.
        $block = preg_replace("/[ \t]+/u", ' ', $block);

        return trim($block);
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

        // 5) Normaliza múltiplas linhas em branco.
        $text = preg_replace("/\n{3,}/u", "\n\n", $text);

        return trim($text);
    }
}

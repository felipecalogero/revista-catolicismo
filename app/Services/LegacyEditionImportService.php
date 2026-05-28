<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\EditionArticle;
use App\Models\EditionPage;
use App\Models\User;
use Carbon\Carbon;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LegacyEditionImportService
{
    /**
     * Disco onde os assets locais são salvos (capa, páginas).
     */
    protected string $publicDisk = 'public';

    public function acervoNumBasePath(): string
    {
        $configured = config('revista.legacy_acervo_num_path');

        return $configured
            ? rtrim($configured, '/')
            : base_path('versaoantiga/Acervo/Num');
    }

    /**
     * Data de capa (regra do legado Cat_paginas.js).
     */
    public function releaseDateFromIssueNumber(int $num): Carbon
    {
        $ano = intdiv($num, 12) + 1951;
        $mes = $num - (($ano - 1951) * 12);
        if ($mes === 0) {
            $mes = 12;
            $ano -= 1;
        }

        return Carbon::create($ano, $mes, 1)->startOfDay();
    }

    public function paddedIssueDir(int $num): string
    {
        return str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Corpo do sumário como HTML simplificado (sem scripts).
     */
    public function sanitizeSumarioHtml(string $html): string
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8"><div id="legacy-sumario-root">'.$html.'</div>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//script|//style|//link') ?: [] as $node) {
            $node->parentNode?->removeChild($node);
        }

        foreach ($xpath->query('//secao') ?: [] as $node) {
            $span = $dom->createElement('span');
            $span->setAttribute('class', 'sumario-secao font-semibold text-red-900');
            while ($node->firstChild) {
                $span->appendChild($node->firstChild);
            }
            $node->parentNode?->replaceChild($span, $node);
        }

        // Remove âncoras antigas (que apontavam para pXX.html). O sumário fica como
        // texto simples; a navegação por matéria é feita pela seção "Matérias desta edição".
        foreach ($xpath->query('//a') ?: [] as $anchor) {
            $anchor->removeAttribute('href');
            $anchor->removeAttribute('target');
        }

        $root = $dom->getElementById('legacy-sumario-root');
        if (! $root) {
            return '';
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    public function extractSumarioBody(string $filePath): ?string
    {
        if (! is_readable($filePath)) {
            return null;
        }

        $raw = File::get($filePath);
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $raw, $m)) {
            return $this->sanitizeSumarioHtml($m[1]);
        }

        return $this->sanitizeSumarioHtml($raw);
    }

    public function resolveImportUserId(): int
    {
        $id = config('revista.legacy_import_user_id');
        if ($id && User::query()->whereKey($id)->exists()) {
            return (int) $id;
        }

        $admin = User::query()->where('role', 'admin')->orderBy('id')->first();
        if ($admin) {
            return $admin->id;
        }

        return User::query()->orderBy('id')->value('id')
            ?? throw new \RuntimeException('Nenhum usuário encontrado para associar às edições legado. Crie um usuário ou defina REVISTA_LEGACY_IMPORT_USER_ID.');
    }

    /**
     * @return array{edition: Edition, was_created: bool, was_updated: bool, was_skipped: bool, pages: int, articles: int}
     */
    public function importIssueFolder(int $issueNum, bool $dryRun, bool $force, bool $skipExistingAssets = false): array
    {
        $padded = $this->paddedIssueDir($issueNum);
        $base = $this->acervoNumBasePath();
        $dir = $base.'/'.$padded;
        $sumarioPath = $dir.'/Sumario_'.$padded.'.html';

        if (! File::isDirectory($dir)) {
            throw new \InvalidArgumentException("Pasta não encontrada: {$dir}");
        }

        $sumarioHtml = $this->extractSumarioBody($sumarioPath) ?? '';
        $release = $this->releaseDateFromIssueNumber($issueNum);
        $months = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];
        $title = 'Catolicismo nº '.$issueNum.' — '.$months[(int) $release->format('n')].' de '.$release->format('Y');
        $slug = 'catolicismo-'.$padded;

        $description = '<p>Edição do acervo digital (nº '.$issueNum.', '.$months[(int) $release->format('n')].' de '.$release->format('Y').').</p>';

        if ($dryRun) {
            $fake = new Edition([
                'title' => $title,
                'slug' => $slug,
                'is_legacy' => true,
                'legacy_issue_number' => $issueNum,
            ]);

            return [
                'edition' => $fake,
                'was_created' => false,
                'was_updated' => false,
                'was_skipped' => false,
                'pages' => 0,
                'articles' => 0,
            ];
        }

        $existing = Edition::query()
            ->where('is_legacy', true)
            ->where('legacy_issue_number', $issueNum)
            ->first();

        if ($existing && ! $force) {
            return [
                'edition' => $existing,
                'was_created' => false,
                'was_updated' => false,
                'was_skipped' => true,
                'pages' => $existing->pages()->count(),
                'articles' => $existing->articles()->count(),
            ];
        }

        // Capa: copia Miniatura.jpg (preferência) ou P01.jpg para storage local.
        $coverRelative = $this->copyCoverImage($dir, $padded, $skipExistingAssets);

        $payload = [
            'user_id' => $this->resolveImportUserId(),
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'table_of_contents' => $sumarioHtml !== '' ? $sumarioHtml : null,
            'cover_image' => $coverRelative,
            'pdf_file' => $existing?->pdf_file,
            'published' => true,
            'published_at' => $existing?->published_at ?? now(),
            'release_date' => $release,
            'is_legacy' => true,
            'legacy_issue_number' => $issueNum,
        ];

        if ($existing) {
            if ($existing->slug !== $slug) {
                $payload['slug'] = $this->uniqueSlug($slug, $existing->id);
            }
            $existing->fill($payload);
            $existing->save();
            $edition = $existing;
            $wasCreated = false;
            $wasUpdated = true;
        } else {
            $payload['slug'] = $this->uniqueSlug($slug, null);
            $edition = Edition::create($payload);
            $wasCreated = true;
            $wasUpdated = false;
        }

        $pages = $this->syncPages($edition, $dir, $padded, $skipExistingAssets);
        $articles = $this->syncArticles($edition, $dir);

        return [
            'edition' => $edition->refresh(),
            'was_created' => $wasCreated,
            'was_updated' => $wasUpdated,
            'was_skipped' => false,
            'pages' => $pages,
            'articles' => $articles,
        ];
    }

    /**
     * Copia Miniatura.jpg (ou P01.jpg como fallback) para storage local.
     * Retorna caminho relativo no disco public (ex: "editions/covers/legacy/0001.jpg") ou null.
     */
    protected function copyCoverImage(string $issueDir, string $padded, bool $skipIfExists): ?string
    {
        $candidates = [
            $issueDir.'/pages/Miniatura.jpg',
            $issueDir.'/pages/miniatura.jpg',
            $issueDir.'/pages/P01.jpg',
            $issueDir.'/pages/p01.jpg',
        ];

        $source = null;
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $source = $candidate;
                break;
            }
        }

        if ($source === null) {
            return null;
        }

        $relative = 'editions/covers/legacy/'.$padded.'.jpg';

        if ($skipIfExists && Storage::disk($this->publicDisk)->exists($relative)) {
            return $relative;
        }

        Storage::disk($this->publicDisk)->put($relative, File::get($source));

        return $relative;
    }

    /**
     * Copia todas as imagens de páginas e sincroniza com a tabela edition_pages.
     * Retorna número de páginas registradas.
     */
    protected function syncPages(Edition $edition, string $issueDir, string $padded, bool $skipExistingAssets): int
    {
        $pagesDir = $issueDir.'/pages';
        if (! is_dir($pagesDir)) {
            return 0;
        }

        $files = [];
        foreach (scandir($pagesDir) ?: [] as $name) {
            if (! is_string($name) || $name === '.' || $name === '..') {
                continue;
            }
            if (! preg_match('/^P(\d{2}(?:-\d{2})?)\.jpe?g$/i', $name, $m)) {
                continue;
            }
            $label = 'P'.$m[1];
            $files[] = [
                'label' => $label,
                'path' => $pagesDir.'/'.$name,
                'sort' => $this->labelToSortOrder($label),
                'is_spread' => str_contains($label, '-'),
            ];
        }

        if ($files === []) {
            return 0;
        }

        usort($files, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        $seenLabels = [];
        foreach ($files as $file) {
            $label = $file['label'];
            $seenLabels[] = $label;
            $relative = 'editions/pages/legacy/'.$padded.'/'.$label.'.jpg';

            $shouldCopy = ! ($skipExistingAssets && Storage::disk($this->publicDisk)->exists($relative));
            if ($shouldCopy) {
                Storage::disk($this->publicDisk)->put($relative, File::get($file['path']));
            }

            EditionPage::updateOrCreate(
                ['edition_id' => $edition->id, 'label' => $label],
                [
                    'image_path' => $relative,
                    'sort_order' => $file['sort'],
                    'is_spread' => $file['is_spread'],
                ]
            );
        }

        // Remove páginas obsoletas que não existem mais na pasta de origem.
        EditionPage::where('edition_id', $edition->id)
            ->whereNotIn('label', $seenLabels)
            ->delete();

        return count($files);
    }

    /**
     * Lê cada Texto_PXX.html, extrai os textos integrais e sincroniza edition_articles.
     */
    protected function syncArticles(Edition $edition, string $issueDir): int
    {
        $files = [];
        foreach (scandir($issueDir) ?: [] as $name) {
            if (! is_string($name)) {
                continue;
            }
            if (! preg_match('/^Texto_P(\d{2}(?:-\d{2})?)\.html$/i', $name, $m)) {
                continue;
            }
            $pageLabel = 'P'.$m[1];
            $files[] = [
                'page_label' => $pageLabel,
                'path' => $issueDir.'/'.$name,
                'sort' => $this->labelToSortOrder($pageLabel),
            ];
        }

        if ($files === []) {
            return 0;
        }

        usort($files, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        // Reimport idempotente: apaga tudo e recria, evitando que o slug ganhe
        // sufixos -1, -2, … a cada execução com --force.
        EditionArticle::where('edition_id', $edition->id)->delete();

        $rows = [];
        $usedSlugs = [];
        $globalSort = 0;
        foreach ($files as $file) {
            $segments = $this->parseTextoFile($file['path']);
            foreach ($segments as $idx => $segment) {
                $globalSort++;
                $rawTitle = $segment['title'] !== '' ? $segment['title'] : ('Página '.$file['page_label']);
                // Trunca o título quando o legado usou o primeiro parágrafo como cabeçalho.
                $title = mb_substr($rawTitle, 0, 220);

                $baseSlug = Str::slug($file['page_label'].'-'.$title);
                if ($baseSlug === '') {
                    $baseSlug = Str::slug($file['page_label']).'-'.($idx + 1);
                }
                if (strlen($baseSlug) > 180) {
                    $baseSlug = rtrim(substr($baseSlug, 0, 180), '-');
                }

                $slug = $baseSlug;
                $i = 1;
                while (isset($usedSlugs[$slug])) {
                    $slug = $baseSlug.'-'.$i;
                    $i++;
                }
                $usedSlugs[$slug] = true;

                $rows[] = [
                    'edition_id' => $edition->id,
                    'page_label' => $file['page_label'],
                    'title' => $title,
                    'slug' => $slug,
                    'body_html' => $segment['body_html'],
                    'sort_order' => $globalSort,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($rows !== []) {
            EditionArticle::insert($rows);
        }

        return count($rows);
    }

    /**
     * Divide um arquivo Texto_PXX.html em segmentos (cada h1/h2 começa um novo segmento).
     *
     * @return array<int, array{title: string, body_html: string}>
     */
    public function parseTextoFile(string $path): array
    {
        if (! is_readable($path)) {
            return [];
        }

        $raw = File::get($path);

        if (! preg_match('/<body[^>]*>(.*)<\/body>/is', $raw, $bm)) {
            $bm = [null, $raw];
        }

        $body = $bm[1];

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8"><div id="legacy-texto-root">'.$body.'</div>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//script|//style|//link|//noscript') ?: [] as $node) {
            $node->parentNode?->removeChild($node);
        }

        $root = $dom->getElementById('legacy-texto-root');
        if (! $root) {
            return [];
        }

        $segments = [];
        $current = null;

        $appendChild = function (DOMNode $node) use (&$current, $dom) {
            if ($current === null) {
                $current = ['title' => '', 'body_html' => ''];
            }
            $current['body_html'] .= $dom->saveHTML($node);
        };

        $isHeadingNode = function (\DOMNode $node): bool {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                return false;
            }
            $name = strtolower($node->nodeName);
            if (in_array($name, ['h1', 'h2'], true)) {
                return true;
            }
            // O legado usa muitas vezes <p class="h1">TITULO</p> como cabeçalho de matéria.
            if ($name === 'p') {
                $class = (string) $node->getAttribute('class');
                if (preg_match('/\b(h1|h2)\b/i', $class)) {
                    return true;
                }
            }

            return false;
        };

        foreach (iterator_to_array($root->childNodes) as $node) {
            $name = strtolower($node->nodeName);

            // Pular o <span id="Pagina_"> (badge "P.XX") e <hr> separadores estruturais soltos
            if ($name === 'span' && $node->nodeType === XML_ELEMENT_NODE && $node->getAttribute('id') === 'Pagina_') {
                continue;
            }

            if ($name === 'hr') {
                // Fecha o segmento atual e começa um novo "vazio".
                if ($current !== null && trim(strip_tags($current['body_html'])) !== '') {
                    $segments[] = $current;
                }
                $current = null;

                continue;
            }

            if ($isHeadingNode($node)) {
                if ($current !== null && trim(strip_tags($current['body_html'])) !== '') {
                    $segments[] = $current;
                }
                $current = [
                    'title' => trim(preg_replace('/\s+/u', ' ', $node->textContent ?? '')),
                    'body_html' => '',
                ];

                continue;
            }

            $appendChild($node);
        }

        if ($current !== null && trim(strip_tags($current['body_html'])) !== '') {
            $segments[] = $current;
        }

        // Caso o arquivo não tenha nenhum h1/h2, devolve um único segmento sem título.
        if ($segments === []) {
            $body_html = '';
            foreach ($root->childNodes as $node) {
                if (strtolower($node->nodeName) === 'span' && $node->nodeType === XML_ELEMENT_NODE && $node->getAttribute('id') === 'Pagina_') {
                    continue;
                }
                $body_html .= $dom->saveHTML($node);
            }
            $body_html = trim($body_html);
            if ($body_html !== '') {
                $segments[] = ['title' => '', 'body_html' => $body_html];
            }
        }

        return array_values(array_filter($segments, function ($s) {
            return trim(strip_tags($s['body_html'])) !== '' || $s['title'] !== '';
        }));
    }

    /**
     * Converte um label de página em um número para ordenação (P01 -> 1, P02-03 -> 2).
     */
    protected function labelToSortOrder(string $label): int
    {
        if (preg_match('/^P(\d{2})/i', $label, $m)) {
            return (int) $m[1];
        }

        return 999;
    }

    protected function uniqueSlug(string $slug, ?int $ignoreId): string
    {
        $candidate = $slug;
        $i = 1;
        while (Edition::query()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    /**
     * @return \Generator<int>
     */
    public function discoverIssueNumbers(): \Generator
    {
        $base = $this->acervoNumBasePath();
        if (! File::isDirectory($base)) {
            return;
        }

        foreach (File::directories($base) as $dir) {
            $name = basename($dir);
            if (! ctype_digit($name)) {
                continue;
            }
            $n = (int) $name;
            if ($n <= 0) {
                continue;
            }
            yield $n;
        }
    }
}

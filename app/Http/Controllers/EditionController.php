<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\EditionArticle;
use App\Models\EditionPage;
use App\Models\EditionPageText;
use App\Support\MagazineViewerUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EditionController extends Controller
{
    /**
     * Listagem unificada (novo + acervo) com filtros opcionais.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $access = (string) $request->input('access', '');
        $year = $request->input('year');
        $source = (string) $request->input('source', '');
        $mode = $request->input('mode', 'title') === 'content' ? 'content' : 'title';

        if ($mode === 'content' && $search !== '') {
            // Aba "No conteúdo das matérias": paginar páginas que casam com o termo.
            $textResults = $this->searchPageTexts($request, $search, $access, $year, $source);
            $editions = $this->emptyEditionsPaginator($request);
        } else {
            $textResults = null;
            $editions = $this->searchEditionsByTitle($request, $search, $access, $year, $source);
        }

        $editionYears = Edition::query()
            ->where('published', true)
            ->get(['release_date', 'published_at'])
            ->map(fn ($e) => $e->release_date?->year ?? $e->published_at?->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        return view('editions.index', compact('editions', 'search', 'editionYears', 'mode', 'textResults'));
    }

    /**
     * Busca tradicional por título/slug/descrição (modo padrão).
     */
    protected function searchEditionsByTitle(Request $request, string $search, string $access, $year, string $source)
    {
        return Edition::query()
            ->where('published', true)
            ->when($search !== '', function ($query) use ($search) {
                $like = $this->searchLikePattern($search);
                $matchingEditionIds = $this->editionIdsMatchingPageText($search, $query);

                $query->where(function ($q) use ($like, $matchingEditionIds) {
                    $q->where('title', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhere('description', 'like', $like);

                    if ($matchingEditionIds !== []) {
                        $q->orWhereIn('id', $matchingEditionIds);
                    }
                });
            })
            ->when($access === 'free', fn ($q) => $q->accessibleByNonSubscribers())
            ->when($access === 'subscribers', fn ($q) => $q->exclusiveForSubscribers())
            ->when($source === 'nova', fn ($q) => $q->nonLegacy())
            ->when($source === 'acervo', fn ($q) => $q->legacy())
            ->when(
                filled($year) && ctype_digit((string) $year) && (int) $year > 1900 && (int) $year <= (int) now()->format('Y') + 1,
                function ($query) use ($year) {
                    $y = (int) $year;
                    $query->where(function ($q) use ($y) {
                        $q->whereYear('release_date', $y)
                            ->orWhere(function ($q2) use ($y) {
                                $q2->whereNull('release_date')
                                    ->whereYear('published_at', $y);
                            });
                    });
                }
            )
            ->orderBy('release_date', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * Busca por conteúdo das matérias: retorna paginação de EditionPageText
     * com snippet em destaque e URL pronta pro visualizador na página exata.
     *
     * Aplica os mesmos filtros (access/source/year) via subquery em editions,
     * reusando os scopes do model Edition em vez de duplicar regras.
     */
    protected function searchPageTexts(Request $request, string $search, string $access, $year, string $source)
    {
        $isMysql = \DB::connection()->getDriverName() === 'mysql';
        $fullTextQuery = $this->buildFullTextQuery($search);
        $like = $this->searchLikePattern($search);

        // IDs das edições que satisfazem os filtros públicos de listagem.
        $editionIdsQuery = Edition::query()
            ->where('published', true)
            ->when($access === 'free', fn ($q) => $q->accessibleByNonSubscribers())
            ->when($access === 'subscribers', fn ($q) => $q->exclusiveForSubscribers())
            ->when($source === 'nova', fn ($q) => $q->nonLegacy())
            ->when($source === 'acervo', fn ($q) => $q->legacy())
            ->when(
                filled($year) && ctype_digit((string) $year) && (int) $year > 1900 && (int) $year <= (int) now()->format('Y') + 1,
                function ($q) use ($year) {
                    $y = (int) $year;
                    $q->where(function ($qq) use ($y) {
                        $qq->whereYear('release_date', $y)
                            ->orWhere(function ($q2) use ($y) {
                                $q2->whereNull('release_date')
                                    ->whereYear('published_at', $y);
                            });
                    });
                }
            )
            ->select('id');

        $query = EditionPageText::query()
            ->select('edition_page_texts.*')
            ->join('editions', 'editions.id', '=', 'edition_page_texts.edition_id')
            ->whereIn('edition_page_texts.edition_id', $editionIdsQuery)
            ->with([
                'edition' => fn ($q) => $q->select('id', 'title', 'slug', 'cover_image', 'release_date', 'published_at', 'is_legacy', 'pdf_file', 'description'),
            ]);

        if ($isMysql && $fullTextQuery !== '') {
            $query->whereRaw(
                'MATCH(edition_page_texts.body_html) AGAINST (? IN BOOLEAN MODE)',
                [$fullTextQuery]
            );
        } else {
            $query->where('edition_page_texts.body_html', 'like', $like);
        }

        $query->orderByRaw('COALESCE(editions.release_date, editions.published_at) desc')
            ->orderByRaw('COALESCE(edition_page_texts.page_number, 9999) asc')
            ->orderBy('edition_page_texts.page_label', 'asc');

        $paginator = $query->paginate(12)->withQueryString();

        $paginator->getCollection()->transform(function (EditionPageText $pt) use ($search) {
            $pt->snippet_html = $this->buildSnippet((string) $pt->body_html, $search);
            $pt->open_url = $this->buildOpenAtPageUrl($pt, $search);

            return $pt;
        });

        return $paginator;
    }

    /**
     * Paginator vazio para que a view não quebre quando estiver no modo "content".
     */
    protected function emptyEditionsPaginator(Request $request)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            12,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Galeria por década/ano (replicação do antigo Index1951..2025 dentro do layout novo).
     */
    public function gallery(Request $request)
    {
        $editions = Edition::query()
            ->where('published', true)
            ->where('is_legacy', true)
            ->orderBy('release_date', 'asc')
            ->orderBy('legacy_issue_number', 'asc')
            ->get();

        $byYear = [];
        foreach ($editions as $edition) {
            $year = $edition->release_date?->year ?? $edition->published_at?->year;
            if (! $year) {
                continue;
            }
            $month = (int) ($edition->release_date?->month ?? $edition->published_at?->month ?? 0);
            $byYear[$year][$month] = $edition;
        }

        ksort($byYear);

        $byDecade = [];
        foreach ($byYear as $year => $months) {
            $decade = (int) (floor($year / 10) * 10);
            $byDecade[$decade][$year] = $months;
        }
        ksort($byDecade);

        $availableDecades = array_keys($byDecade);
        $decadeParam = $request->input('decade');
        $selectedDecade = null;
        if ($decadeParam !== null && ctype_digit((string) $decadeParam) && in_array((int) $decadeParam, $availableDecades, true)) {
            $selectedDecade = (int) $decadeParam;
        } elseif ($availableDecades !== []) {
            $selectedDecade = end($availableDecades);
        }

        return view('editions.gallery', compact('byDecade', 'availableDecades', 'selectedDecade'));
    }

    /**
     * Exibe uma edição individual
     */
    public function show(string $slug)
    {
        $edition = Edition::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        [$hasFullAccess, $canDownload, $requiresLoginOnly] = $this->resolveEditionAccess($edition);
        $canDownload = $canDownload && filled($edition->pdf_file);

        $edition->load(['pages', 'articles']);

        $edition->increment('views');

        $otherEditions = Edition::where('published', true)
            ->where('id', '!=', $edition->id)
            ->where('is_legacy', $edition->is_legacy)
            ->orderBy('release_date', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(6)
            ->get();

        return view('editions.show', compact('edition', 'hasFullAccess', 'canDownload', 'otherEditions', 'requiresLoginOnly'));
    }

    /**
     * Página individual (visualizador de imagem + textos da página, para edições legado).
     */
    public function showPage(string $slug, string $label)
    {
        $edition = Edition::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        if (! $this->userCanViewEditionContent($edition)) {
            return $this->redirectFromBlockedAccess($edition);
        }

        $page = EditionPage::where('edition_id', $edition->id)
            ->where('label', $label)
            ->firstOrFail();

        $prevPage = EditionPage::where('edition_id', $edition->id)
            ->where('sort_order', '<', $page->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        $nextPage = EditionPage::where('edition_id', $edition->id)
            ->where('sort_order', '>', $page->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        $articlesOnPage = EditionArticle::where('edition_id', $edition->id)
            ->where('page_label', $label)
            ->orderBy('sort_order')
            ->get();

        return view('editions.page', compact('edition', 'page', 'prevPage', 'nextPage', 'articlesOnPage'));
    }

    /**
     * Texto integral de uma matéria do acervo.
     */
    public function showArticle(string $slug, string $articleSlug)
    {
        $edition = Edition::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        if (! $this->userCanViewEditionContent($edition)) {
            return $this->redirectFromBlockedAccess($edition);
        }

        $article = EditionArticle::where('edition_id', $edition->id)
            ->where('slug', $articleSlug)
            ->firstOrFail();

        $prevArticle = EditionArticle::where('edition_id', $edition->id)
            ->where('sort_order', '<', $article->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        $nextArticle = EditionArticle::where('edition_id', $edition->id)
            ->where('sort_order', '>', $article->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        return view('editions.article', compact('edition', 'article', 'prevArticle', 'nextArticle'));
    }

    /**
     * Faz o download do PDF da edição
     */
    public function download(string $slug)
    {
        $edition = Edition::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        if ($edition->allowsLegacyPublicAccess()) {
            return $this->respondPdfDownload($edition);
        }

        $user = auth()->user();

        if (! $user) {
            return redirect()->guest(route('login'))
                ->with('error', 'Você precisa estar logado para baixar esta edição.');
        }

        if ($edition->canBeAccessedByNonSubscribers()) {
            // Qualquer usuário autenticado
        } else {
            if (! $user->canAccessEditions()) {
                return redirect()->route('subscriptions.plans')
                    ->with('error', 'Você precisa de uma assinatura ativa para baixar esta edição.');
            }
        }

        return $this->respondPdfDownload($edition);
    }

    /**
     * Visualiza o PDF (novo) ou as páginas (acervo) em formato revista.
     */
    public function viewMagazine(string $slug)
    {
        $edition = Edition::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        if (! $this->userCanViewEditionContent($edition)) {
            return $this->redirectFromBlockedAccess($edition);
        }

        $edition->load(['pages', 'pageTexts']);

        $pageTexts = $edition->pageTexts
            ->mapWithKeys(fn ($pt) => [$pt->page_label => $this->stripInvalidCharRefs((string) ($pt->body_html ?? ''))])
            ->all();

        return view('editions.magazine', compact('edition', 'pageTexts'));
    }

    /**
     * Regra única para acessar conteúdo (páginas, artigos, magazine).
     */
    protected function userCanViewEditionContent(Edition $edition): bool
    {
        if ($edition->allowsLegacyPublicAccess()) {
            return true;
        }

        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($edition->canBeAccessedByNonSubscribers()) {
            return true;
        }

        return $user->canAccessEditions();
    }

    protected function redirectFromBlockedAccess(Edition $edition)
    {
        if (! auth()->check()) {
            return redirect()->guest(route('login'))
                ->with('error', 'Você precisa estar logado para acessar esta edição.');
        }

        return redirect()->route('subscriptions.plans')
            ->with('error', 'Você precisa de uma assinatura ativa para acessar esta edição.');
    }

    /**
     * @return array{0: bool, 1: bool, 2: bool}
     */
    protected function resolveEditionAccess(Edition $edition): array
    {
        if ($edition->allowsLegacyPublicAccess()) {
            return [true, true, false];
        }

        $user = auth()->user();

        if ($edition->canBeAccessedByNonSubscribers()) {
            if ($user) {
                return [true, true, false];
            }

            return [false, false, true];
        }

        if ($user) {
            $hasFullAccess = $user->canAccessEdition($edition);
            $canDownload = $user->canAccessEditions();

            return [$hasFullAccess, $canDownload, false];
        }

        return [false, false, false];
    }

    /**
     * Retorna a lista de edition_ids cujo texto de página casa com o termo
     * informado. Executa apenas UMA consulta (não correlacionada), usando o
     * índice FULLTEXT no MySQL ou caindo para LIKE em outros drivers.
     *
     * Limitado a 5000 ids para proteger a query principal — termos genéricos
     * (ex.: "Deus") podem casar em centenas de edições, e isso é o suficiente
     * para a paginação.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Edition>  $query
     * @return array<int, int>
     */
    protected function editionIdsMatchingPageText(string $search, $query): array
    {
        $isMysql = $query->getConnection()->getDriverName() === 'mysql';
        $fullTextQuery = $this->buildFullTextQuery($search);

        $base = \App\Models\EditionPageText::query()->select('edition_id');

        if ($isMysql && $fullTextQuery !== '') {
            $base->whereRaw(
                'MATCH(body_html) AGAINST (? IN BOOLEAN MODE)',
                [$fullTextQuery]
            );
        } else {
            $base->where('body_html', 'like', $this->searchLikePattern($search));
        }

        return $base
            ->distinct()
            ->limit(5000)
            ->pluck('edition_id')
            ->all();
    }

    /**
     * Gera um trecho ("snippet") do texto da página em torno do termo buscado,
     * marcando o termo com <mark> para o front-end renderizar como destaque.
     * Tudo é escapado contra XSS exceto o próprio <mark>.
     *
     * Funciona de forma case- e accent-insensitive: a posição é encontrada em
     * um índice "normalizado" (sem acentos, em lowercase), mas o snippet
     * retornado preserva o texto original (com acentos e capitalização).
     */
    protected function buildSnippet(string $html, string $term, int $contextChars = 140): string
    {
        $plain = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plain = $this->stripInvalidCharRefs($plain);
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? $plain;
        if ($plain === '') {
            return '';
        }

        $haystackNorm = $this->normalizeForSearch($plain);
        // Mantém só palavras significativas (>= 3 chars), igual à regra do FULLTEXT
        // do MySQL (innodb_ft_min_token_size = 3). Palavras como "a", "o", "de" são
        // descartadas para não destacar todo o texto.
        $words = array_values(array_filter(
            preg_split('/\s+/u', trim($term)) ?: [],
            fn ($w) => mb_strlen($this->normalizeForSearch($w)) >= 3
        ));

        $pos = false;
        $matchLen = 0;
        foreach ($words as $w) {
            $needle = $this->normalizeForSearch($w);
            $found = $this->findWordStart($haystackNorm, $needle, 0);
            if ($found !== false) {
                $pos = $found;
                // Estende até o fim da palavra para o snippet ficar centrado nela.
                $matchLen = $this->wordRunLength($haystackNorm, $found, mb_strlen($needle));
                break;
            }
        }

        if ($pos === false) {
            // Termo não localizado (raro, mas pode acontecer com FULLTEXT-only matches
            // que casam por prefixo em outra palavra). Devolve o começo do texto.
            $head = mb_substr($plain, 0, $contextChars * 2);
            $head = e($head);
            if (mb_strlen($plain) > $contextChars * 2) {
                $head .= '…';
            }

            return $head;
        }

        $start = max(0, $pos - $contextChars);
        $length = $matchLen + ($contextChars * 2);
        $snippet = mb_substr($plain, $start, $length);

        // Recorta nas bordas das palavras para não cortar pelo meio.
        if ($start > 0) {
            $firstSpace = mb_strpos($snippet, ' ');
            if ($firstSpace !== false && $firstSpace < 30) {
                $snippet = mb_substr($snippet, $firstSpace + 1);
            }
        }
        $endsAtText = ($start + $length) >= mb_strlen($plain);
        if (! $endsAtText) {
            $lastSpace = mb_strrpos($snippet, ' ');
            if ($lastSpace !== false && (mb_strlen($snippet) - $lastSpace) < 30) {
                $snippet = mb_substr($snippet, 0, $lastSpace);
            }
        }

        // Destaca cada palavra do termo (case/accent-insensitive) no snippet
        // preservando o conteúdo original. Para isso, percorremos o snippet
        // e o snippet normalizado em paralelo.
        $highlighted = $this->highlightTermsInText($snippet, $words);

        $prefix = $start > 0 ? '…' : '';
        $suffix = $endsAtText ? '' : '…';

        return $prefix.$highlighted.$suffix;
    }

    /**
     * Destaca cada palavra do termo no texto (case/accent-insensitive),
     * envolvendo cada ocorrência em <mark>. Casa só em borda de palavra
     * (não destaca "a" dentro de "casa") e faz prefix matching estendendo
     * o match até o fim da palavra ("perfei" → destaca "perfeição" inteiro).
     *
     * @param  array<int, string>  $words
     */
    protected function highlightTermsInText(string $text, array $words): string
    {
        $textNorm = $this->normalizeForSearch($text);
        $ranges = [];

        foreach ($words as $w) {
            $needle = $this->normalizeForSearch($w);
            if (mb_strlen($needle) < 3) {
                continue;
            }
            $needleLen = mb_strlen($needle);
            $offset = 0;
            while (($p = $this->findWordStart($textNorm, $needle, $offset)) !== false) {
                $runLen = $this->wordRunLength($textNorm, $p, $needleLen);
                $ranges[] = [$p, $runLen];
                $offset = $p + $runLen;
            }
        }

        if ($ranges === []) {
            return e($text);
        }

        // Ordena e funde intervalos sobrepostos.
        usort($ranges, fn ($a, $b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($ranges as $r) {
            if ($merged === []) {
                $merged[] = $r;

                continue;
            }
            $last = &$merged[count($merged) - 1];
            if ($r[0] <= $last[0] + $last[1]) {
                $last[1] = max($last[1], ($r[0] + $r[1]) - $last[0]);
            } else {
                $merged[] = $r;
            }
            unset($last);
        }

        // Reconstrói o snippet escapando o texto e inserindo <mark>...</mark>.
        $out = '';
        $cursor = 0;
        foreach ($merged as [$p, $len]) {
            $out .= e(mb_substr($text, $cursor, $p - $cursor));
            $out .= '<mark>'.e(mb_substr($text, $p, $len)).'</mark>';
            $cursor = $p + $len;
        }
        $out .= e(mb_substr($text, $cursor));

        return $out;
    }

    /**
     * Remove referências numéricas de caracteres inválidas (fora do intervalo
     * Unicode 0..0x10FFFF) que o smalot/pdfparser emite para ligaduras de
     * fontes embutidas no PDF (ex.: "f&#6684777;m" → "fm" no lugar de "fim").
     *
     * Isso evita aparecer "&#6684780;" como lixo visível nos snippets/painel
     * de texto. A limpeza definitiva (ao extrair) pode ser feita depois;
     * aqui só sanitizamos para exibição.
     */
    protected function stripInvalidCharRefs(string $text): string
    {
        return preg_replace_callback(
            '/&#(\d+);/',
            fn ($m) => ((int) $m[1]) > 0x10FFFF ? '' : $m[0],
            $text
        ) ?? $text;
    }

    /**
     * Encontra a próxima ocorrência de $needle em $haystack que comece em borda
     * de palavra (não-letra/dígito antes, ou no início da string). Retorna o
     * char-index UTF-8 ou false.
     *
     * Premissa: $haystack e $needle já estão normalizados (lowercase + sem
     * acentos), então "letra" pode ser detectada com a classe ASCII a-z0-9.
     */
    protected function findWordStart(string $haystack, string $needle, int $offset)
    {
        if ($needle === '') {
            return false;
        }
        while (($p = mb_strpos($haystack, $needle, $offset)) !== false) {
            if ($p === 0) {
                return $p;
            }
            $prev = mb_substr($haystack, $p - 1, 1);
            // Borda de palavra: char anterior não é letra/dígito.
            if (! preg_match('/[a-z0-9]/i', $prev)) {
                return $p;
            }
            $offset = $p + 1;
        }

        return false;
    }

    /**
     * A partir de uma posição que começa em borda de palavra, retorna o tamanho
     * "run" (em chars) da palavra inteira para fazer prefix matching — i.e.
     * busca "perfei" passa a destacar "perfeicao" inteiro.
     */
    protected function wordRunLength(string $haystack, int $start, int $minLen): int
    {
        $len = mb_strlen($haystack);
        $end = $start + $minLen;
        while ($end < $len) {
            $ch = mb_substr($haystack, $end, 1);
            if (! preg_match('/[a-z0-9]/i', $ch)) {
                break;
            }
            $end++;
        }

        return $end - $start;
    }

    /**
     * Normaliza string para busca insensível a caixa e acentos.
     */
    protected function normalizeForSearch(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ]);

        return $text;
    }

    /**
     * URL do visualizador da revista posicionado na página onde o termo aparece,
     * preservando o termo de busca para destaque no painel de texto.
     */
    protected function buildOpenAtPageUrl(EditionPageText $pt, string $term): string
    {
        if (! $pt->relationLoaded('edition') || ! $pt->edition) {
            return '#';
        }

        return MagazineViewerUrl::build(
            $pt->edition,
            $term,
            $pt->page_number,
            $pt->page_label,
        );
    }

    /**
     * Sanitiza o termo informado pelo usuário e o transforma em uma expressão
     * para FULLTEXT MATCH...AGAINST em BOOLEAN MODE, com prefix-wildcard.
     *
     * Exemplo: "Thomas Morus" -> "+Thomas* +Morus*"
     *          "Halloween"    -> "+Halloween*"
     *
     * Retorna string vazia quando não há termo utilizável (ex.: só caracteres especiais
     * ou palavras menores que innodb_ft_min_token_size, padrão 3 chars).
     */
    protected function buildFullTextQuery(string $search): string
    {
        // Remove caracteres especiais reservados pelo BOOLEAN MODE do MySQL.
        $sanitized = preg_replace('/[+\-><()~*"@]/u', ' ', $search);
        $sanitized = trim(preg_replace('/\s+/u', ' ', (string) $sanitized));
        if ($sanitized === '') {
            return '';
        }

        $words = array_filter(
            explode(' ', $sanitized),
            // FULLTEXT do InnoDB ignora tokens com menos de 3 caracteres por padrão.
            fn ($w) => mb_strlen($w) >= 3
        );

        if ($words === []) {
            return '';
        }

        return implode(' ', array_map(fn ($w) => '+'.$w.'*', $words));
    }

    protected function respondPdfDownload(Edition $edition): BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        if (! $edition->pdf_file) {
            abort(404, 'Arquivo PDF não disponível para esta edição.');
        }

        if (Edition::isAbsoluteUrl($edition->pdf_file)) {
            return redirect()->away($edition->pdf_file);
        }

        if (! Storage::disk('public')->exists($edition->pdf_file)) {
            abort(404, 'Arquivo PDF não encontrado.');
        }

        return Storage::disk('public')->download($edition->pdf_file, $edition->slug.'.pdf');
    }
}

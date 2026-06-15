<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\EditionArticle;
use App\Models\EditionPage;
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

        $editions = Edition::query()
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

        $editionYears = Edition::query()
            ->where('published', true)
            ->get(['release_date', 'published_at'])
            ->map(fn ($e) => $e->release_date?->year ?? $e->published_at?->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        return view('editions.index', compact('editions', 'search', 'editionYears'));
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
            return redirect()->route('login')
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
            ->mapWithKeys(fn ($pt) => [$pt->page_label => $pt->body_html ?? ''])
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
            return redirect()->route('login')
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

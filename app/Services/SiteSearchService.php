<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Edition;
use App\Models\EditionArticle;
use App\Models\EditionPageText;
use App\Support\MagazineViewerUrl;
use App\Support\SearchHighlighter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SiteSearchService
{
    /** @var array<string, array<int, int>> */
    protected array $editionIdsByTermCache = [];

    public function __construct(
        protected SearchHighlighter $highlighter
    ) {}

    /**
     * Busca unificada em todo o site público.
     *
     * Edições incluem título, descrição e conteúdo das páginas — uma entrada
     * por edição (estilo Google), com link direto à página da revista quando
     * o termo aparece no texto.
     *
     * @return array{
     *   articles: Collection|LengthAwarePaginator,
     *   editions: Collection|LengthAwarePaginator,
     *   archive: Collection|LengthAwarePaginator,
     *   counts: array{articles: int, editions: int, archive: int, total: int}
     * }
     */
    public function search(string $term, string $type, Request $request, int $previewLimit = 6): array
    {
        $term = trim($term);
        $paginate = $type !== 'all';
        $perPage = 12;

        $articles = $this->searchArticles($term, $type, $paginate, $perPage, $previewLimit);
        $editions = $this->searchEditions($term, $type, $paginate, $perPage, $previewLimit);
        $archive = $this->searchArchiveArticles($term, $type, $paginate, $perPage, $previewLimit);

        $counts = [
            'articles' => $this->countArticles($term),
            'editions' => $this->countEditions($term),
            'archive' => $this->countArchiveArticles($term),
        ];
        $counts['total'] = array_sum($counts);

        return compact('articles', 'editions', 'archive', 'counts');
    }

    protected function searchArticles(string $term, string $type, bool $paginate, int $perPage, int $previewLimit)
    {
        if ($type !== 'all' && $type !== 'articles') {
            return collect();
        }

        $query = Article::query()
            ->with('categoryRelation')
            ->where('published', true)
            ->when($term !== '', fn ($q) => $this->applyArticleSearch($q, $term))
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc');

        if ($paginate && $type === 'articles') {
            $paginator = $query->paginate($perPage)->withQueryString();
            $paginator->getCollection()->transform(fn ($a) => $this->formatArticle($a, $term));

            return $paginator;
        }

        return $query->limit($previewLimit)->get()->map(fn ($a) => $this->formatArticle($a, $term));
    }

    /**
     * Uma entrada por edição: busca título/slug/descrição + conteúdo das páginas.
     */
    protected function searchEditions(string $term, string $type, bool $paginate, int $perPage, int $previewLimit)
    {
        if ($type !== 'all' && $type !== 'editions') {
            return collect();
        }

        $query = $this->editionsMatchingQuery($term);

        if ($paginate && $type === 'editions') {
            $paginator = $query->paginate($perPage)->withQueryString();
            $this->formatEditionsCollection($paginator->getCollection(), $term);

            return $paginator;
        }

        $collection = $query->limit($previewLimit)->get();
        $this->formatEditionsCollection($collection, $term);

        return $collection;
    }

    protected function searchArchiveArticles(string $term, string $type, bool $paginate, int $perPage, int $previewLimit)
    {
        if ($type !== 'all' && $type !== 'archive') {
            return collect();
        }

        $query = $this->archiveArticlesQuery($term);

        if ($paginate && $type === 'archive') {
            $paginator = $query->paginate($perPage)->withQueryString();
            $paginator->getCollection()->transform(fn ($a) => $this->formatArchiveHit($a, $term));

            return $paginator;
        }

        return $query->limit($previewLimit)->get()->map(fn ($a) => $this->formatArchiveHit($a, $term));
    }

    protected function editionsMatchingQuery(string $term)
    {
        return Edition::query()
            ->where('published', true)
            ->when($term !== '', fn ($q) => $this->applyEditionSearchFilter($q, $term))
            ->orderBy('release_date', 'desc')
            ->orderBy('published_at', 'desc');
    }

    protected function applyEditionSearchFilter($query, string $term): void
    {
        $like = $this->highlighter->likePattern($term);
        $matchingIds = $this->editionIdsMatchingPageText($term);

        $query->where(function ($qq) use ($like, $matchingIds, $term) {
            $qq->where('title', 'like', $like)
                ->orWhere('slug', 'like', $like);

            if ($matchingIds !== []) {
                $qq->orWhereIn('id', $matchingIds);
            }

            $qq->orWhere(function ($descQ) use ($term) {
                $this->highlighter->applyWholeWordContentWhere($descQ, 'description', $term);
            });
        });
    }

    /**
     * Anexa snippet e URL unificados: prioriza trecho da página quando o termo
     * aparece no conteúdo; senão usa a descrição e link para a ficha da edição.
     *
     * @param  Collection<int, Edition>  $editions
     */
    protected function formatEditionsCollection(Collection $editions, string $term): void
    {
        $bestPages = $this->bestPageTextByEdition($term, $editions->pluck('id')->all());

        $editions->transform(function (Edition $edition) use ($term, $bestPages) {
            return $this->formatUnifiedEdition($edition, $term, $bestPages[$edition->id] ?? null);
        });
    }

    /**
     * Primeira página (por ordem) de cada edição onde o termo aparece.
     *
     * @param  array<int, int>  $editionIds
     * @return array<int, EditionPageText>
     */
    protected function bestPageTextByEdition(string $term, array $editionIds): array
    {
        if ($term === '' || $editionIds === []) {
            return [];
        }

        $map = [];
        foreach ($this->pageTextsMatchingQuery($term)->whereIn('edition_page_texts.edition_id', $editionIds)->get() as $pt) {
            if (! isset($map[$pt->edition_id])) {
                $map[$pt->edition_id] = $pt;
            }
        }

        return $map;
    }

    protected function applyArticleSearch($query, string $term): void
    {
        $like = $this->highlighter->likePattern($term);
        $query->where(function ($q) use ($like, $term) {
            $q->where('title', 'like', $like)
                ->orWhere('slug', 'like', $like)
                ->orWhere('author', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhereHas('categoryRelation', function ($cq) use ($like) {
                    $cq->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                })
                ->orWhere(function ($qq) use ($term) {
                    $this->highlighter->applyWholeWordContentWhere($qq, 'description', $term);
                })
                ->orWhere(function ($qq) use ($term) {
                    $this->highlighter->applyWholeWordContentWhere($qq, 'content', $term);
                });
        });
    }

    protected function pageTextsMatchingQuery(string $term)
    {
        $editionIds = Edition::query()->where('published', true)->select('id');

        $query = EditionPageText::query()
            ->select('edition_page_texts.*')
            ->join('editions', 'editions.id', '=', 'edition_page_texts.edition_id')
            ->whereIn('edition_page_texts.edition_id', $editionIds);

        if ($term !== '') {
            $this->highlighter->applyBodyHtmlSearchWhere($query, 'edition_page_texts.body_html', $term);
        }

        return $query
            ->orderByRaw('COALESCE(edition_page_texts.page_number, 9999) asc')
            ->orderBy('edition_page_texts.page_label', 'asc');
    }

    protected function archiveArticlesQuery(string $term)
    {
        $like = $this->highlighter->likePattern($term);

        return EditionArticle::query()
            ->select('edition_articles.*')
            ->join('editions', 'editions.id', '=', 'edition_articles.edition_id')
            ->where('editions.published', true)
            ->when($term !== '', function ($q) use ($like, $term) {
                $q->where(function ($qq) use ($like, $term) {
                    $qq->where('edition_articles.title', 'like', $like)
                        ->orWhere(function ($bodyQ) use ($term) {
                            $this->highlighter->applyBodyHtmlSearchWhere($bodyQ, 'edition_articles.body_html', $term);
                        });
                });
            })
            ->with(['edition' => fn ($q) => $q->select('id', 'title', 'slug', 'cover_image', 'release_date', 'published_at', 'is_legacy')])
            ->orderByRaw('COALESCE(editions.release_date, editions.published_at) desc')
            ->orderBy('edition_articles.sort_order', 'asc');
    }

    protected function countArticles(string $term): int
    {
        return Article::query()
            ->where('published', true)
            ->when($term !== '', fn ($q) => $this->applyArticleSearch($q, $term))
            ->count();
    }

    protected function countEditions(string $term): int
    {
        return $this->editionsMatchingQuery($term)->count();
    }

    protected function countArchiveArticles(string $term): int
    {
        if ($term === '') {
            return 0;
        }

        return $this->archiveArticlesQuery($term)->count();
    }

    /**
     * @return array<int, int>
     */
    protected function editionIdsMatchingPageText(string $term): array
    {
        if ($term === '') {
            return [];
        }

        if (isset($this->editionIdsByTermCache[$term])) {
            return $this->editionIdsByTermCache[$term];
        }

        return $this->editionIdsByTermCache[$term] = $this->pageTextsMatchingQuery($term)
            ->distinct()
            ->limit(5000)
            ->pluck('edition_page_texts.edition_id')
            ->all();
    }

    protected function formatArticle(Article $article, string $term): array
    {
        $categorySlug = $article->categoryRelation
            ? $article->categoryRelation->slug
            : Str::slug($article->category ?? '');

        $snippetSource = $article->description ?: $article->content;

        return [
            'type' => 'article',
            'title' => $article->title,
            'url' => route('articles.show', [$categorySlug, $article->slug]),
            'image' => $article->image_url,
            'meta' => collect([
                $article->category_name,
                $article->published_at?->format('d/m/Y'),
                $article->author,
            ])->filter()->implode(' · '),
            'snippet_html' => $this->highlighter->buildSnippet((string) $snippetSource, $term),
        ];
    }

    protected function formatUnifiedEdition(Edition $edition, string $term, ?EditionPageText $pageText): array
    {
        if ($pageText) {
            $snippet = $this->highlighter->buildSnippet((string) $pageText->body_html, $term);
            $pageLabel = $pageText->page_number
                ? ('Página '.$pageText->page_number)
                : ('Página '.$pageText->page_label);
            $url = MagazineViewerUrl::build(
                $edition,
                $term,
                $pageText->page_number,
                $pageText->page_label,
            );
        } else {
            $snippet = $this->highlighter->buildSnippet((string) ($edition->description ?? ''), $term);
            $pageLabel = null;
            $url = route('editions.show', $edition->slug);
        }

        return [
            'type' => 'edition',
            'title' => $edition->title,
            'url' => $url,
            'image' => $edition->cover_image_url,
            'meta' => collect([
                $edition->is_legacy ? 'Acervo' : 'Edição',
                $pageLabel,
                $edition->release_date?->format('m/Y') ?? $edition->published_at?->format('d/m/Y'),
            ])->filter()->implode(' · '),
            'snippet_html' => $snippet,
            'is_legacy' => $edition->is_legacy,
        ];
    }

    protected function formatArchiveHit(EditionArticle $article, string $term): array
    {
        $edition = $article->edition;
        $pageNumber = $edition
            ? MagazineViewerUrl::resolvePageNumber($edition->id, null, $article->page_label)
            : null;

        return [
            'type' => 'archive',
            'title' => $article->title,
            'url' => $edition
                ? MagazineViewerUrl::build($edition, $term, $pageNumber, $article->page_label)
                : '#',
            'image' => $edition?->cover_image_url,
            'meta' => collect([
                'Acervo',
                $edition?->title,
                $pageNumber ? ('Página '.$pageNumber) : null,
                $edition?->release_date?->format('m/Y') ?? $edition?->published_at?->format('d/m/Y'),
            ])->filter()->implode(' · '),
            'snippet_html' => $this->highlighter->buildSnippet((string) $article->body_html, $term),
            'is_legacy' => true,
        ];
    }
}

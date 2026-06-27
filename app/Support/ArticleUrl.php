<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Support\Str;

class ArticleUrl
{
    /**
     * Slug de categoria para rotas articles.show, ou null se não resolvível.
     */
    public static function resolveCategorySlug(Article $article): ?string
    {
        if ($article->categoryRelation?->slug) {
            return $article->categoryRelation->slug;
        }

        $legacy = Str::slug((string) ($article->category ?? ''));

        return $legacy !== '' ? $legacy : null;
    }

    /**
     * URL pública do artigo, ou null se faltar categoria/slug.
     */
    public static function showUrl(Article $article): ?string
    {
        $categorySlug = self::resolveCategorySlug($article);
        $slug = trim((string) ($article->slug ?? ''));

        if ($categorySlug === null || $slug === '') {
            return null;
        }

        return route('articles.show', [$categorySlug, $slug]);
    }
}

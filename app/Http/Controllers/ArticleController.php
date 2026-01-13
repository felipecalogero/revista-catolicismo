<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;

class ArticleController extends Controller
{
    /**
     * Exibe um artigo público
     */
    public function show(string $category, string $slug)
    {
        // Buscar a categoria pelo slug
        $categoryModel = Category::where('slug', $category)->first();

        // Buscar o artigo pela categoria e slug
        $article = Article::where('slug', $slug)
            ->where('published', true)
            ->where(function ($query) use ($category, $categoryModel) {
                if ($categoryModel) {
                    $query->where('category_id', $categoryModel->id)
                        ->orWhere('category', $category);
                } else {
                    $query->where('category', $category);
                }
            })
            ->firstOrFail();

        $article->increment('views');

        return view('articles.show', compact('article'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Article;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Exibe todos os artigos de uma categoria com paginação
     */
    public function show(string $slug)
    {
        // Buscar a categoria pelo slug
        $category = Category::where('slug', $slug)->firstOrFail();

        // Buscar artigos publicados da categoria com paginação
        $articles = Article::where('published', true)
            ->where(function ($query) use ($category) {
                $query->where('category_id', $category->id)
                    ->orWhere('category', $category->name);
            })
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('categories.show', compact('category', 'articles'));
    }
}


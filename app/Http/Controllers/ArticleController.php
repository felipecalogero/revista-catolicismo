<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Exibe um artigo público
     */
    public function show(string $slug)
    {
        $article = Article::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        return view('articles.show', compact('article'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    /**
     * Exibe todas as notícias com busca e filtro por categoria
     */
    public function index(Request $request)
    {
        $query = Article::where('published', true);

        // Busca por texto
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        // Filtro por categoria
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where(function ($q) use ($category) {
                    $q->where('category_id', $category->id)
                      ->orWhere('category', $category->name);
                });
            }
        }

        // Ordenação
        $articles = $query->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Formatar artigos
        $articles->getCollection()->transform(function ($article) {
            $categorySlug = $article->categoryRelation ? $article->categoryRelation->slug : Str::slug($article->category);
            return [
                'title' => $article->title,
                'excerpt' => $article->description,
                'image' => $article->image_url ?? $article->image,
                'category' => $article->category_name,
                'category_slug' => $categorySlug,
                'author' => $article->author,
                'date' => $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y'),
                'slug' => $article->slug,
            ];
        });

        // Buscar todas as categorias para o select
        $categories = Category::orderBy('name')->get();

        return view('articles.index', compact('articles', 'categories'));
    }
}

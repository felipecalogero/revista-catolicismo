<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Edition;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        // Buscar as últimas 10 edições publicadas
        $editions = Edition::where('published', true)
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Transformar em formato compatível com o componente
        $revistas = $editions->map(function ($edition) {
            return [
                'id' => $edition->id,
                'titulo' => $edition->title,
                'edicao' => $edition->title,
                'data' => $edition->published_at ? $edition->published_at->format('d/m/Y') : $edition->created_at->format('d/m/Y'),
                'capa' => $edition->cover_image ? \Illuminate\Support\Facades\Storage::url($edition->cover_image) : '',
                'destaque' => $edition->published_at && $edition->published_at->isToday(),
                'slug' => $edition->slug,
            ];
        })->toArray();

        // Buscar artigos em destaque: artigos recentes (últimos 30 dias) com mais visualizações
        // Se não houver artigos recentes suficientes, complementa com os mais visualizados
        $destaques = Article::where('published', true)
            ->where('published_at', '>=', now()->subDays(30))
            ->orderBy('views', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get();

        // Se não houver 4 artigos dos últimos 30 dias, complementa com os mais visualizados de todos os tempos
        if ($destaques->count() < 4) {
            $destaquesAntigos = Article::where('published', true)
                ->where(function ($query) {
                    $query->where('published_at', '<', now()->subDays(30))
                        ->orWhereNull('published_at');
                })
                ->whereNotIn('id', $destaques->pluck('id'))
                ->orderBy('views', 'desc')
                ->orderBy('published_at', 'desc')
                ->limit(4 - $destaques->count())
                ->get();
            
            $destaques = $destaques->merge($destaquesAntigos);
        }
        
        $destaques = $destaques->take(4)
            ->map(function ($article) {
                $categorySlug = $article->categoryRelation ? $article->categoryRelation->slug : Str::slug($article->category);
                return [
                    'title' => $article->title,
                    'excerpt' => $article->description,
                    'image' => $article->image,
                    'category' => $article->category_name,
                    'category_slug' => $categorySlug,
                    'author' => $article->author,
                    'date' => $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y'),
                    'slug' => $article->slug,
                ];
            })->toArray();

        $noticias = Article::where('published', true)
            ->orderBy('published_at', 'desc')
            ->limit(16)
            ->get()
            ->map(function ($article) {
                $categorySlug = $article->categoryRelation ? $article->categoryRelation->slug : Str::slug($article->category);
                return [
                    'title' => $article->title,
                    'excerpt' => $article->description,
                    'image' => $article->image,
                    'category' => $article->category_name,
                    'category_slug' => $categorySlug,
                    'author' => $article->author,
                    'date' => $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y'),
                    'slug' => $article->slug,
                ];
            })->toArray();

        $maisLidas = Article::where('published', true)
            ->orderBy('views', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($article) {
                $categorySlug = $article->categoryRelation ? $article->categoryRelation->slug : Str::slug($article->category);
                return [
                    'title' => $article->title,
                    'image' => $article->image,
                    'date' => $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y'),
                    'slug' => $article->slug,
                    'category_slug' => $categorySlug,
                ];
            })->toArray();

        // Buscar as 3 categorias mais visitadas (baseado na soma de views dos artigos)
        $topCategories = Category::withCount(['articles' => function ($query) {
                $query->where('published', true);
            }])
            ->withSum(['articles' => function ($query) {
                $query->where('published', true);
            }], 'views')
            ->having('articles_count', '>', 0)
            ->orderByRaw('COALESCE(articles_sum_views, 0) DESC')
            ->limit(3)
            ->get();

        // Buscar artigos para cada uma das 3 categorias mais visitadas
        $categoriasMaisVisitadas = [];
        foreach ($topCategories as $category) {
            $artigos = Article::where('published', true)
                ->where(function ($query) use ($category) {
                    $query->where('category_id', $category->id)
                        ->orWhere('category', $category->name);
                })
                ->orderBy('published_at', 'desc')
                ->limit(4)
                ->get()
                ->map(function ($article) {
                    $categorySlug = $article->categoryRelation ? $article->categoryRelation->slug : Str::slug($article->category);
                    return [
                        'title' => $article->title,
                        'excerpt' => $article->description,
                        'image' => $article->image,
                        'category' => $article->category_name,
                        'category_slug' => $categorySlug,
                        'author' => $article->author,
                        'date' => $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y'),
                        'slug' => $article->slug,
                    ];
                })->toArray();

            if (count($artigos) > 0) {
                $categoriasMaisVisitadas[] = [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'articles' => $artigos,
                ];
            }
        }

        // Buscar categorias mais populares (com mais artigos) para a sidebar - limitado a 8
        $categorias = Category::withCount(['articles' => function ($query) {
                $query->where('published', true);
            }])
            ->having('articles_count', '>', 0)
            ->orderBy('articles_count', 'desc')
            ->orderBy('name', 'asc')
            ->limit(6)
            ->get();

        return view('home', compact(
            'revistas',
            'destaques',
            'noticias',
            'maisLidas',
            'categoriasMaisVisitadas',
            'categorias'
        ));
    }
}

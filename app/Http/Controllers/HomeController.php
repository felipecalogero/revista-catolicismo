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

        // Buscar artigos publicados do banco de dados
        $destaques = Article::where('published', true)
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

        // Artigos por categoria
        $artigosPolitica = Article::where('published', true)
            ->where(function ($query) {
                $query->where('category', 'Política')
                    ->orWhereHas('categoryRelation', function ($q) {
                        $q->where('name', 'Política');
                    });
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
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

        $artigosIgreja = Article::where('published', true)
            ->where(function ($query) {
                $query->where('category', 'Igreja')
                    ->orWhereHas('categoryRelation', function ($q) {
                        $q->where('name', 'Igreja');
                    });
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
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

        $artigosCultura = Article::where('published', true)
            ->where(function ($query) {
                $query->where('category', 'Cultura')
                    ->orWhereHas('categoryRelation', function ($q) {
                        $q->where('name', 'Cultura');
                    });
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
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

        return view('home', compact(
            'revistas',
            'destaques',
            'noticias',
            'maisLidas',
            'artigosPolitica',
            'artigosIgreja',
            'artigosCultura'
        ));
    }
}

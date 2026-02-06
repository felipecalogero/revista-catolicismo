<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Article;
use App\Models\Edition;
use App\Models\Category;
use App\Models\Subscription;

class DashboardController extends Controller
{
    /**
     * Mostra o dashboard do administrador
     */
    public function index()
    {
        // Contagem de usuários (todos os usuários do banco)
        $totalUsers = User::count();

        // Contagem de artigos publicados
        $totalArticles = Article::where('published', true)->count();

        // Contagem de assinaturas ativas
        $totalSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '>=', now())
            ->count();

        // Contagem de edições publicadas
        $totalEditions = Edition::where('published', true)->count();

        // Atividades Recentes - Combinar todas as atividades
        $activities = collect();

        // Artigos criados recentemente
        $recentArticles = Article::with('user', 'categoryRelation')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($article) {
                return [
                    'type' => 'article_created',
                    'title' => $article->title,
                    'user' => $article->user ? $article->user->name : 'Sistema',
                    'date' => $article->created_at,
                    'url' => route('admin.articles.edit', $article->id),
                    'icon' => '📝',
                ];
            });

        // Artigos publicados recentemente
        $publishedArticles = Article::with('user', 'categoryRelation')
            ->where('published', true)
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($article) {
                return [
                    'type' => 'article_published',
                    'title' => $article->title,
                    'user' => $article->user ? $article->user->name : 'Sistema',
                    'date' => $article->published_at,
                    'url' => route('admin.articles.edit', $article->id),
                    'icon' => '✅',
                ];
            });

        // Edições criadas recentemente
        $recentEditions = Edition::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($edition) {
                return [
                    'type' => 'edition_created',
                    'title' => $edition->title,
                    'user' => $edition->user ? $edition->user->name : 'Sistema',
                    'date' => $edition->created_at,
                    'url' => route('admin.editions.edit', $edition->id),
                    'icon' => '📚',
                ];
            });

        // Edições publicadas recentemente
        $publishedEditions = Edition::with('user')
            ->where('published', true)
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($edition) {
                return [
                    'type' => 'edition_published',
                    'title' => $edition->title,
                    'user' => $edition->user ? $edition->user->name : 'Sistema',
                    'date' => $edition->published_at,
                    'url' => route('admin.editions.edit', $edition->id),
                    'icon' => '📖',
                ];
            });

        // Usuários criados recentemente
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user_created',
                    'title' => $user->name,
                    'user' => 'Sistema',
                    'date' => $user->created_at,
                    'url' => route('admin.users.show', $user->id),
                    'icon' => '👤',
                ];
            });

        // Categorias criadas recentemente
        $recentCategories = Category::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($category) {
                return [
                    'type' => 'category_created',
                    'title' => $category->name,
                    'user' => 'Sistema',
                    'date' => $category->created_at,
                    'url' => route('admin.categories.edit', $category->id),
                    'icon' => '🏷️',
                ];
            });

        // Combinar todas as atividades e ordenar por data
        $activities = $recentArticles
            ->merge($publishedArticles)
            ->merge($recentEditions)
            ->merge($publishedEditions)
            ->merge($recentUsers)
            ->merge($recentCategories)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalSubscriptions',
            'totalEditions',
            'totalArticles',
            'activities'
        ));
    }
}

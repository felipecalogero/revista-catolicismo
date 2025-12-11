<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Article;
use App\Models\Edition;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        $totalSubscriptions = 0;
        if (Schema::hasTable('subscriptions')) {
            $totalSubscriptions = DB::table('subscriptions')
                ->where(function($query) {
                    $query->where('status', 'active')
                          ->orWhere('status', 'ativa')
                          ->orWhere('active', true)
                          ->orWhereNull('status'); // Se não houver campo status, conta todos
                })
                ->count();
        }

        // Contagem de edições publicadas
        $totalEditions = Edition::where('published', true)->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalSubscriptions',
            'totalEditions',
            'totalArticles'
        ));
    }
}

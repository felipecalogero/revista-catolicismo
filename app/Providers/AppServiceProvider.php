<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar locale para português
        \Carbon\Carbon::setLocale('pt_BR');
        app()->setLocale('pt_BR');
        
        // Compartilhar categorias com todas as views
        View::composer('partials.header', function ($view) {
            $categories = Category::orderBy('name')->get();
            $view->with('categories', $categories);
        });

        View::composer('partials.footer', function ($view) {
            $categories = Category::orderBy('name')->get();
            $view->with('categories', $categories);
        });
    }
}

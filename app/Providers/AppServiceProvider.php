<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Configurar regras de senha padrão
        \Illuminate\Validation\Rules\Password::defaults(function () {
            return \Illuminate\Validation\Rules\Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        // Configurar locale para português
        \Carbon\Carbon::setLocale('pt_BR');
        app()->setLocale('pt_BR');

        // Compartilhar categorias com todas as views
        View::composer('partials.header', function ($view) {
            $allCategories = Category::orderBy('name')->get();
            $mainCategories = $allCategories->take(5);
            $moreCategories = $allCategories->skip(5);
            $view->with([
                'categories' => $allCategories,
                'mainCategories' => $mainCategories,
                'moreCategories' => $moreCategories,
            ]);
        });

        View::composer('partials.footer', function ($view) {
            $allCategories = Category::orderBy('name')->get();
            $view->with([
                'categories' => $allCategories,
                'mainCategories' => $allCategories->take(5),
                'moreCategories' => $allCategories->skip(5),
            ]);
        });
    }
}

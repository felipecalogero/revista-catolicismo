<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\EditionController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;

// Rotas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/edicoes/{slug}', [EditionController::class, 'show'])->name('editions.show');
Route::get('/edicoes/{slug}/download', [EditionController::class, 'download'])->name('editions.download');

// Rotas de autenticação
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Rotas autenticadas
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Rotas de administrador
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/csrf-token', [\App\Http\Controllers\Admin\CsrfTokenController::class, 'getToken'])->name('csrf-token');
    Route::resource('articles', AdminArticleController::class);
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('editions', \App\Http\Controllers\Admin\EditionController::class);
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('/editions/{id}/publish', [\App\Http\Controllers\Admin\EditionController::class, 'publish'])->name('editions.publish');
    Route::post('/editions/{id}/unpublish', [\App\Http\Controllers\Admin\EditionController::class, 'unpublish'])->name('editions.unpublish');
});

// Rota genérica de artigos (deve ser a última para evitar conflitos)
Route::get('/{category}/{slug}', [ArticleController::class, 'show'])->name('articles.show');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EditionController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PagBankWebhookController;

// Rotas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/noticias', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/edicoes/{slug}', [EditionController::class, 'show'])->name('editions.show');
Route::get('/edicoes/{slug}/revista', [EditionController::class, 'viewMagazine'])->name('editions.magazine');
Route::get('/edicoes/{slug}/download', [EditionController::class, 'download'])->name('editions.download')
    ->middleware('subscription');

// Rotas de assinatura (públicas para visualização de planos)
Route::get('/assinaturas/planos', [SubscriptionController::class, 'plans'])->name('subscriptions.plans');

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
    
    // Configurações do usuário
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    
    // Rotas de assinatura
    Route::prefix('assinaturas')->name('subscriptions.')->group(function () {
        Route::post('/criar', [SubscriptionController::class, 'create'])->name('create');
        Route::get('/minha-assinatura', [SubscriptionController::class, 'show'])->name('show');
        Route::post('/ativar', [SubscriptionController::class, 'activate'])->name('activate');
        Route::post('/suspender', [SubscriptionController::class, 'suspend'])->name('suspend');
        Route::post('/cancelar', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::get('/retorno-pagbank', [SubscriptionController::class, 'returnFromPagBank'])->name('return');
    });
});

// Webhook do PagBank (sem autenticação)
Route::post('/webhook/pagbank', [PagBankWebhookController::class, 'handle'])->name('webhook.pagbank');

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

// Rota de categorias (deve vir antes da rota de artigos)
Route::get('/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// Rota genérica de artigos (deve ser a última para evitar conflitos)
Route::get('/{category}/{slug}', [ArticleController::class, 'show'])->name('articles.show');

<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EditionController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PagBankWebhookController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SearchController;

// Rotas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/busca', [SearchController::class, 'index'])->name('search.index');
Route::get('/sobre-nos', [PageController::class, 'about'])->name('pages.about');
Route::get('/nossa-missao', [PageController::class, 'mission'])->name('pages.mission');
Route::get('/politica-de-privacidade', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/termos-de-uso', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/fale-conosco', [ContactController::class, 'index'])->name('contact.index');
Route::post('/fale-conosco', [ContactController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('contact.store');
Route::get('/noticias', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/edicoes', [EditionController::class, 'index'])->name('editions.index');
Route::get('/edicoes/galeria', [EditionController::class, 'gallery'])->name('editions.gallery');
Route::get('/edicoes/{slug}', [EditionController::class, 'show'])->name('editions.show');
Route::get('/edicoes/{slug}/revista', [EditionController::class, 'viewMagazine'])->name('editions.magazine');
Route::get('/edicoes/{slug}/download', [EditionController::class, 'download'])->name('editions.download');
Route::get('/edicoes/{slug}/pagina/{label}', [EditionController::class, 'showPage'])->name('editions.page');
Route::get('/edicoes/{slug}/artigos/{textSlug}', [EditionController::class, 'showArticle'])->name('editions.article');

// Redirecionamentos para preservar links antigos do acervo
Route::redirect('/arquivo/edicoes', '/edicoes')->name('archive.editions.index');
Route::redirect('/arquivo/edicoes/{slug}', '/edicoes/{slug}')->name('archive.editions.show');
Route::redirect('/arquivo/edicoes/{slug}/revista', '/edicoes/{slug}/revista')->name('archive.editions.magazine');
Route::redirect('/arquivo/edicoes/{slug}/download', '/edicoes/{slug}/download')->name('archive.editions.download');

// Rotas de assinatura (públicas para visualização de planos)
Route::get('/assinaturas/planos', [SubscriptionController::class, 'plans'])->name('subscriptions.plans');

// Rotas de autenticação
Route::middleware('guest')->group(function () {
    Route::get('/entrar', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/entrar', [LoginController::class, 'login']);
    Route::get('/cadastro', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/cadastro', [RegisterController::class, 'register'])->middleware('throttle:5,10');

    // Password Reset Routes
    Route::get('/esqueci-minha-senha', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/esqueci-minha-senha', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,15')
        ->name('password.email');
    Route::get('/redefinir-senha/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/redefinir-senha', [ResetPasswordController::class, 'reset'])->name('password.update');

    // First Access Flow
    Route::get('/primeiro-acesso', [\App\Http\Controllers\Auth\FirstAccessController::class, 'showForm'])->name('first-access');
    Route::post('/primeiro-acesso', [\App\Http\Controllers\Auth\FirstAccessController::class, 'sendLink'])
        ->middleware('throttle:5,15')
        ->name('first-access.send');
});

// Rotas autenticadas
Route::middleware(['auth'])->group(function () {
    // Email Verification Routes
    Route::get('/email/verificar', [\App\Http\Controllers\Auth\VerificationController::class, 'show'])
        ->name('verification.notice');

    Route::get('/email/verificar/{id}/{hash}', [\App\Http\Controllers\Auth\VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/notificacao-verificacao', [\App\Http\Controllers\Auth\VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::post('/email/atualizar', [\App\Http\Controllers\Auth\VerificationController::class, 'updateEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.update-email');

    Route::post('/sair', [LogoutController::class, 'logout'])->name('logout');

    // Rotas que exigem e-mail verificado
    Route::middleware('verified')->group(function () {
        Route::get('/painel', [DashboardController::class, 'index'])->name('dashboard');

        // Configurações do usuário
        Route::get('/configuracoes', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/configuracoes', [SettingsController::class, 'update'])->name('settings.update');
        Route::put('/configuracoes/senha', [SettingsController::class, 'updatePassword'])->name('settings.password.update');

        // Rotas de assinatura
        Route::prefix('assinaturas')->name('subscriptions.')->group(function () {
            Route::post('/criar', [SubscriptionController::class, 'create'])->name('create');
            Route::get('/minha-assinatura', [SubscriptionController::class, 'show'])->name('show');
            Route::post('/ativar', [SubscriptionController::class, 'activate'])->name('activate');
            Route::post('/suspender', [SubscriptionController::class, 'suspend'])->name('suspend');
            Route::post('/cancelar', [SubscriptionController::class, 'cancel'])->name('cancel');
        });
    });

    // Rota de retorno do PagBank (pode ser acessada logo após pagamento, mas preferencialmente logado)
    Route::get('/assinaturas/retorno-pagbank', [SubscriptionController::class, 'returnFromPagBank'])->name('subscriptions.return');
});

// Webhook do PagBank (sem autenticação)
Route::post('/webhook/pagbank', [PagBankWebhookController::class, 'handle'])->name('webhook.pagbank');

// Endpoint HTTP para processar a fila (chamado por cron externo).
// Hospedagem compartilhada não roda processo longo de queue:work com confiabilidade,
// então delegamos para um pinger externo (ex.: cron-job.org) batendo aqui a cada minuto.
// Protegido por token em config('app.queue_tick_token').
Route::get('/internal/queue-tick/{token}', \App\Http\Controllers\Internal\QueueTickController::class)
    ->middleware('throttle:120,1')
    ->name('internal.queue-tick');

// Rotas de administrador
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/painel', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/csrf-token', [\App\Http\Controllers\Admin\CsrfTokenController::class, 'getToken'])->name('csrf-token');
    Route::resource('artigos', AdminArticleController::class)->names('articles');
    Route::resource('categorias', \App\Http\Controllers\Admin\CategoryController::class)->names('categories');
    Route::resource('edicoes', \App\Http\Controllers\Admin\EditionController::class)->names('editions');
    Route::get('/usuarios/importar', [\App\Http\Controllers\Admin\UserController::class, 'import'])->name('users.import');
    Route::post('/usuarios/importar', [\App\Http\Controllers\Admin\UserController::class, 'storeImport'])->name('users.storeImport');
    Route::get('/usuarios/importar/progresso', [\App\Http\Controllers\Admin\UserController::class, 'importProgress'])->name('users.importProgress');
    Route::resource('usuarios', \App\Http\Controllers\Admin\UserController::class)->names('users');
    Route::post('/edicoes/{id}/publicar', [\App\Http\Controllers\Admin\EditionController::class, 'publish'])->name('editions.publish');
    Route::post('/edicoes/{id}/despublicar', [\App\Http\Controllers\Admin\EditionController::class, 'unpublish'])->name('editions.unpublish');

    // Texto por página (extração / edição manual)
    Route::get('/edicoes/{id}/texto', [\App\Http\Controllers\Admin\EditionController::class, 'pageTexts'])->name('editions.page-texts');
    Route::post('/edicoes/{id}/texto/extrair', [\App\Http\Controllers\Admin\EditionController::class, 'extractText'])->name('editions.extract-text');
    Route::post('/edicoes/{id}/texto/paginas', [\App\Http\Controllers\Admin\EditionController::class, 'storePageText'])->name('editions.page-texts.create');
    Route::put('/edicoes/{id}/texto/paginas/{label}', [\App\Http\Controllers\Admin\EditionController::class, 'updatePageText'])
        ->where('label', '[A-Za-z0-9_\-]+')
        ->name('editions.page-texts.update');
    Route::put('/edicoes/{id}/texto/paginas/{label}/reset', [\App\Http\Controllers\Admin\EditionController::class, 'resetPageText'])
        ->where('label', '[A-Za-z0-9_\-]+')
        ->name('editions.page-texts.reset');
});

// Rota de categorias (deve vir antes da rota de artigos)
Route::get('/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// Rota genérica de artigos (deve ser a última para evitar conflitos)
Route::get('/{category}/{slug}', [ArticleController::class, 'show'])->name('articles.show');

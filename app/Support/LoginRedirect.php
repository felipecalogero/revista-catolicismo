<?php

namespace App\Support;

use Illuminate\Http\Request;

class LoginRedirect
{
    /**
     * Rotas de autenticação que não devem ser destino após o login.
     *
     * @var list<string>
     */
    protected static array $authPaths = [
        '/entrar',
        '/cadastro',
        '/esqueci-minha-senha',
        '/redefinir-senha',
        '/primeiro-acesso',
        '/sair',
    ];

    /**
     * URL segura para retorno após login (mesmo site, fora das rotas de auth).
     */
    public static function sanitize(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return self::isAllowedPath($url) ? $url : null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $host = parse_url($url, PHP_URL_HOST);

        if (! $appHost || $host !== $appHost) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?? '/';

        return self::isAllowedPath($path) ? $url : null;
    }

    /**
     * Grava na sessão a URL para onde o usuário deve voltar após autenticar.
     */
    public static function remember(?string $url = null): void
    {
        $url ??= request()->fullUrl();
        $safe = self::sanitize($url);

        if ($safe) {
            session(['url.intended' => $safe]);
        }
    }

    /**
     * Link para a página de login preservando o retorno à URL informada (ou atual).
     */
    public static function loginUrl(?string $redirect = null): string
    {
        $redirect ??= request()->fullUrl();
        $safe = self::sanitize($redirect);

        if (! $safe) {
            return route('login');
        }

        return route('login', ['redirect' => $safe]);
    }

    /**
     * Processa o parâmetro ?redirect= na tela de login.
     */
    public static function rememberFromRequest(Request $request): void
    {
        if ($request->filled('redirect')) {
            self::remember($request->input('redirect'));
        }
    }

    protected static function isAllowedPath(string $path): bool
    {
        foreach (self::$authPaths as $authPath) {
            if ($path === $authPath || str_starts_with($path, $authPath.'/')) {
                return false;
            }
        }

        return true;
    }
}

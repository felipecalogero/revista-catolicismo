<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Administradores sempre têm acesso
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // Verifica se o usuário está autenticado
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Você precisa estar logado para acessar este conteúdo.');
        }

        // Verifica se o usuário tem assinatura ativa
        if (!$user->canAccessEditions()) {
            return redirect()->route('subscriptions.plans')
                ->with('error', 'Você precisa de uma assinatura ativa para acessar este conteúdo.');
        }

        return $next($request);
    }
}

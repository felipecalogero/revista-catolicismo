<?php

use App\Support\LoginRedirect;

if (! function_exists('login_url')) {
    /**
     * URL da página de login com retorno à página atual (ou à informada).
     */
    function login_url(?string $redirect = null): string
    {
        return LoginRedirect::loginUrl($redirect);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Exibe a página de Política de Privacidade
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Exibe a página de Termos de Uso
     */
    public function terms()
    {
        return view('pages.terms');
    }
}

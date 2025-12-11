<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CsrfTokenController extends Controller
{
    /**
     * Retorna um novo token CSRF
     */
    public function getToken(Request $request)
    {
        // Renovar a sessão para garantir que não expire durante o upload
        Session::regenerate();
        
        return response()->json([
            'token' => csrf_token(),
        ]);
    }
}

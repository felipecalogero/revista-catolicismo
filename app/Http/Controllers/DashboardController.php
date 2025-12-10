<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Mostra o dashboard do usuário
     */
    public function index()
    {
        return view('dashboard');
    }
}

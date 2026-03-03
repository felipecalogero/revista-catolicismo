<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Exibe a página de Fale Conosco
     */
    public function index()
    {
        return view('pages.contact');
    }

    /**
     * Processa o formulário de contato
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Envia o e-mail para o endereço de contato
            Mail::to('contato@catolicismo.com.br')->send(new ContactMail($validated));

            return back()->with('success', 'Sua mensagem foi enviada com sucesso! Em breve entraremos em contato.');
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail de contato: ' . $e->getMessage());
            return back()->with('error', 'Houve um erro ao enviar sua mensagem. Por favor, tente novamente mais tarde.');
        }
    }
}

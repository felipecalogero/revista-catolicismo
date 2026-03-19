<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Password;

class FirstAccessController extends Controller
{
    /**
     * Show the first access form.
     */
    public function showForm()
    {
        return view('auth.first-access');
    }

    /**
     * Send the first access link (password reset link).
     */
    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'E-mail não encontrado em nossa base de dados.']);
        }

        if ($user->hasPassword()) {
            return back()->withErrors(['email' => 'Este usuário já possui uma senha definida. Por favor, use a tela de login ou a recuperação de senha padrão.']);
        }

        // We use the standard password reset broker to send the link
        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Enviamos um link para o seu e-mail para você configurar sua primeira senha.');
        }

        return back()->withErrors(['email' => 'Não foi possível enviar o link de acesso. Tente novamente mais tarde.']);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use App\Mail\NewUserNotificationMail;

class RegisterController extends Controller
{
    /**
     * Mostra o formulário de registro
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Processa o registro
     */
    public function register(Request $request)
    {
        if (filled($request->input('website'))) {
            Log::warning('Cadastro bloqueado por honeypot', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);

            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user', // Todos os registros são usuários comuns
        ]);

        // O e-mail de boas-vindas é enviado pelo listener SendWelcomeEmailAfterVerification
        // apenas após o usuário confirmar a posse do e-mail (evento Verified), evitando
        // que o domínio seja usado para spam de listbombing em e-mails de terceiros.
        try {
            $adminEmail = config('mail.from.address', 'contato@catolicismo.com.br');
            Mail::to($adminEmail)->send(new NewUserNotificationMail($user));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de novo usuário ao admin: ' . $e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}

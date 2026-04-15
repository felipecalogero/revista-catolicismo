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
use App\Mail\WelcomeUserMail;
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

        // Envia email de boas-vindas
        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user));

            // Envia notificacao para o admin
            $adminEmail = config('mail.from.address', 'admin@revistacatolicismo.com.br');
            Mail::to($adminEmail)->send(new NewUserNotificationMail($user));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar e-mail no cadastro: ' . $e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}

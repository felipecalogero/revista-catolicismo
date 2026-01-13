<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Mostra a página de configurações do usuário
     */
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    /**
     * Atualiza as informações pessoais do usuário
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        // Apenas administradores podem alterar o email
        if ($user->isAdmin()) {
            $validated['email'] = $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            ])['email'];
        }

        $user->update($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Suas informações foram atualizadas com sucesso!');
    }

    /**
     * Atualiza a senha do usuário
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('A senha atual está incorreta.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Sua senha foi alterada com sucesso!');
    }
}

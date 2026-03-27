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

    public function update(Request $request)
    {
        $user = Auth::user();

        // Normalizar CPF, Telefone e CEP (remover máscara) antes da validação
        if ($request->has('cpf')) {
            $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
            $request->merge(['cpf' => $cpf ?: null]);
        }
        if ($request->has('zip_code')) {
            $zip = preg_replace('/[^0-9]/', '', $request->zip_code);
            $request->merge(['zip_code' => $zip ?: null]);
        }
        if ($request->has('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            $request->merge(['phone' => $phone ?: null]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'min:11', 'max:14', 'unique:users,cpf,' . $user->id],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip_code' => ['nullable', 'string', 'size:8'],
            'phone' => ['nullable', 'string', 'min:10', 'max:11'],
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('A senha atual está incorreta.');
                }
            }],
        ];

        // Se estiver tentando mudar a senha
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

        // Apenas administradores podem alterar o email (embora no settings.index esteja desabilitado para o usuário)
        if ($request->has('email') && $user->isAdmin()) {
            $validated['email'] = $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            ])['email'];
        }

        // Remover current_password do array de update
        unset($validated['current_password']);

        // Se a nova senha foi validada, criptografá-la
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
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
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Sua senha foi alterada com sucesso!');
    }
}

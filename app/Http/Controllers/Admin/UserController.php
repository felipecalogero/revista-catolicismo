<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('subscriptions')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Normalizar CPF, Telefone e CEP (remover máscara) antes da validação
        if ($request->has('cpf')) {
            $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
            $request->merge(['cpf' => $cpf ?: null]);
        }
        if ($request->has('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            $request->merge(['phone' => $phone ?: null]);
        }
        if ($request->has('zip_code')) {
            $zip = preg_replace('/[^0-9]/', '', $request->zip_code);
            $request->merge(['zip_code' => $zip ?: null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => 'required|in:user,admin',
            'cpf' => 'nullable|string|min:11|max:14|unique:users,cpf',
            'address' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:9',
            'phone' => 'nullable|string|min:10|max:11',
            'plan_type' => 'nullable|in:physical,virtual,none',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'cpf' => $validated['cpf'],
            'address' => $validated['address'],
            'neighborhood' => $validated['neighborhood'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip_code' => $validated['zip_code'],
            'phone' => $validated['phone'],
        ]);

        // Gerenciar Assinatura (Apenas para usuários comuns)
        if ($validated['role'] === 'user' && isset($validated['plan_type']) && $validated['plan_type'] !== 'none') {
            $user->subscriptions()->create([
                'plan_type' => $validated['plan_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'active',
                'purchase_date' => now(),
                'amount' => $validated['plan_type'] === 'physical' ? 100.00 : 50.00,
            ]);
        }

        $user->sendEmailVerificationNotification();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('subscriptions')->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::with(['subscriptions' => function($query) {
            $query->latest();
        }])->findOrFail($id);
        
        $latestSubscription = $user->subscriptions->first();
        
        return view('admin.users.edit', compact('user', 'latestSubscription'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        // Normalizar CPF, Telefone e CEP (remover máscara) antes da validação
        if ($request->has('cpf')) {
            $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
            $request->merge(['cpf' => $cpf ?: null]);
        }
        if ($request->has('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            $request->merge(['phone' => $phone ?: null]);
        }
        if ($request->has('zip_code')) {
            $zip = preg_replace('/[^0-9]/', '', $request->zip_code);
            $request->merge(['zip_code' => $zip ?: null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => ['nullable', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => 'required|in:user,admin',
            'cpf' => 'nullable|string|min:11|max:14|unique:users,cpf,' . $id,
            'address' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:9',
            'phone' => 'nullable|string|min:10|max:11',
            'plan_type' => 'nullable|in:physical,virtual,none',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'cpf' => $validated['cpf'],
            'address' => $validated['address'],
            'neighborhood' => $validated['neighborhood'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip_code' => $validated['zip_code'],
            'phone' => $validated['phone'],
        ];

        // Atualizar senha apenas se fornecida
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Gerenciar Assinatura (Apenas para usuários comuns)
        if ($validated['role'] === 'user' && isset($validated['plan_type'])) {
            if ($validated['plan_type'] === 'none') {
                // Se o admin escolher "Nenhuma", podemos expirar a assinatura atual se existir
                $user->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);
            } else {
                // Atualizar ou criar assinatura
                $latest = $user->subscriptions()->latest()->first();

                $subData = [
                    'plan_type' => $validated['plan_type'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'status' => 'active',
                    'purchase_date' => now(),
                    'amount' => $validated['plan_type'] === 'physical' ? 100.00 : 50.00, // Valores padrão fictícios se manual
                ];

                if ($latest) {
                    $latest->update($subData);
                } else {
                    $user->subscriptions()->create($subData);
                }
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Prevenir deletar o próprio usuário admin logado
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Você não pode deletar seu próprio usuário!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário deletado com sucesso!');
    }

    /**
     * Show the import form.
     */
    public function import()
    {
        return view('admin.users.import');
    }

    /**
     * Process the CSV import.
     */
    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header
        fgetcsv($handle);

        $count = 0;
        $errors = [];

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Expecting: Nome, Email, CPF (opcional), Telefone (opcional), Endereço (opcional)
            $name = $data[0] ?? null;
            $email = $data[1] ?? null;
            $cpf = !empty($data[2]) ? preg_replace('/[^0-9]/', '', $data[2]) : null;
            $phone = !empty($data[3]) ? preg_replace('/[^0-9]/', '', $data[3]) : null;
            $address = $data[4] ?? null;

            if (!$name || !$email) {
                continue;
            }

            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $errors[] = "E-mail {$email} já cadastrado.";
                continue;
            }

            // Check if CPF already exists if provided
            if ($cpf && User::where('cpf', $cpf)->exists()) {
                $errors[] = "CPF {$cpf} já cadastrado para o e-mail {$email}.";
                continue;
            }

            try {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'cpf' => $cpf,
                    'phone' => $phone,
                    'address' => $address,
                    'password' => null, // No password initially
                    'role' => 'user',
                ]);

                $user->sendEmailVerificationNotification();

                $count++;
            } catch (\Exception $e) {
                $errors[] = "Erro ao importar {$email}: " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "{$count} usuários importados com sucesso!";
        if (!empty($errors)) {
            $message .= " Erros: " . implode(' ', $errors);
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }
}

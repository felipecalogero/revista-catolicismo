<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $role = $request->input('role');
        $planType = $request->input('plan_type');
        $subscriptionStatus = $request->input('subscription_status');
        $state = strtoupper(trim((string) $request->input('state', '')));

        $users = User::query()
            ->with('subscriptions')
            ->orderBy('created_at', 'desc')
            ->when($search !== '', function ($query) use ($search) {
                $like = $this->searchLikePattern($search);

                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('cpf', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('address', 'like', $like)
                        ->orWhere('address_number', 'like', $like)
                        ->orWhere('complement', 'like', $like)
                        ->orWhere('neighborhood', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('state', 'like', $like)
                        ->orWhere('zip_code', 'like', $like)
                        ->orWhere('profession', 'like', $like)
                        ->orWhere('role', 'like', $like)
                        ->orWhereHas('subscriptions', function ($sub) use ($like) {
                            $sub->where('plan_type', 'like', $like)
                                ->orWhere('plan_name', 'like', $like)
                                ->orWhere('product_name', 'like', $like)
                                ->orWhere('status', 'like', $like)
                                ->orWhere('cancel_reason', 'like', $like)
                                ->orWhere('notes', 'like', $like)
                                ->orWhere('payment_method', 'like', $like)
                                ->orWhere('pagbank_transaction_id', 'like', $like)
                                ->orWhere('pagbank_subscription_id', 'like', $like);
                        });
                });
            })
            ->when(in_array($role, ['user', 'admin'], true), fn ($q) => $q->where('role', $role))
            ->when(in_array($planType, ['physical', 'virtual'], true), function ($q) use ($planType) {
                $q->whereHas('subscriptions', fn ($sub) => $sub->where('plan_type', $planType));
            })
            ->when(in_array($subscriptionStatus, ['pending', 'active', 'expired', 'cancelled', 'suspended'], true), function ($q) use ($subscriptionStatus) {
                $q->whereHas('subscriptions', fn ($sub) => $sub->where('status', $subscriptionStatus));
            })
            ->when(strlen($state) === 2, fn ($q) => $q->where('state', $state))
            ->paginate(15)
            ->withQueryString();

        $subscriptionStatuses = Subscription::query()
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->values();

        $ufOptions = User::query()
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->orderBy('state')
            ->pluck('state')
            ->values();

        return view('admin.users.index', compact('users', 'search', 'subscriptionStatuses', 'ufOptions'));
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
            'profession' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'address_number' => 'nullable|string|max:255',
            'complement' => 'nullable|string|max:255',
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
            'profession' => $validated['profession'],
            'address' => $validated['address'],
            'address_number' => $validated['address_number'],
            'complement' => $validated['complement'],
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

        try {
            Password::sendResetLink($user->only('email'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Falha ao enviar e-mail de redefinição para {$user->email}: ".$e->getMessage());

            return redirect()->route('admin.users.index')
                ->with('warning', 'Usuário criado com sucesso, mas o e-mail de boas-vindas não pôde ser enviado. Verifique a configuração SMTP.');
        }

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
        $user = User::with(['subscriptions' => function ($query) {
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
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => ['nullable', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => 'required|in:user,admin',
            'cpf' => 'nullable|string|min:11|max:14|unique:users,cpf,'.$id,
            'profession' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'address_number' => 'nullable|string|max:255',
            'complement' => 'nullable|string|max:255',
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
            'profession' => $validated['profession'],
            'address' => $validated['address'],
            'address_number' => $validated['address_number'],
            'complement' => $validated['complement'],
            'neighborhood' => $validated['neighborhood'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip_code' => $validated['zip_code'],
            'phone' => $validated['phone'],
        ];

        // Atualizar senha apenas se fornecida
        if (! empty($validated['password'])) {
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
     * Process the user import (CSV, XLSX, XLS).
     */
    public function storeImport(Request $request)
    {
        $mode = strtolower(trim((string) $request->input('import_mode', '')));
        if ($mode === 'digital') {
            $mode = 'virtual';
        }
        if ($mode !== '') {
            $request->merge(['import_mode' => $mode]);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
            'job_id' => 'required|string',
            'import_mode' => 'required|in:physical,virtual',
        ]);

        $jobId = $request->input('job_id');
        $import = new \App\Imports\UsersImport(
            $jobId,
            (string) $request->input('import_mode'),
            $request->boolean('extend_expired_vigence'),
        );

        // Inicializar progresso caso demore
        Cache::put('import_progress_'.$jobId, ['current' => 0, 'total' => 0, 'status' => 'starting'], 300);

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            $message = "{$import->count} usuários importados com sucesso!";

            if (! empty($import->errors)) {
                $errorMsg = count($import->errors) > 5
                    ? implode(' ', array_slice($import->errors, 0, 5)).' ... e mais '.(count($import->errors) - 5).' erros.'
                    : implode(' ', $import->errors);

                return redirect()->route('admin.users.index')->with('success', $message)->with('warning', 'Alguns registros falharam: '.$errorMsg);
            }

            return redirect()->route('admin.users.index')->with('success', $message);
        } catch (\Exception $e) {
            if (isset($jobId)) {
                Cache::put('import_progress_'.$jobId, ['status' => 'error', 'message' => $e->getMessage()], 300);
            }

            return redirect()->route('admin.users.index')->with('error', 'Erro fatal na importação: '.$e->getMessage());
        }
    }

    /**
     * Endpoint for AJAX progress tracking
     */
    public function importProgress(Request $request)
    {
        $jobId = $request->query('job_id');
        if (! $jobId) {
            return response()->json(['status' => 'error', 'message' => 'Job ID não fornecido.'], 400);
        }

        $progress = Cache::get('import_progress_'.$jobId, [
            'current' => 0,
            'total' => 0,
            'status' => 'pending',
        ]);

        return response()->json($progress);
    }

}

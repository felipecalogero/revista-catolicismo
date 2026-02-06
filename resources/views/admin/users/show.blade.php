@extends('layouts.app')

@section('title', 'Detalhes do Usuário - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                        Detalhes do Usuário
                    </h1>
                    <p class="text-gray-600">Informações completas do usuário</p>
                </div>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium">
                    Editar Usuário
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Informações Básicas --}}
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 font-serif">Informações Básicas</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nome</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Função</dt>
                            <dd class="mt-1">
                                @if($user->role === 'admin')
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800">Administrador</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">Usuário</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Data de Cadastro</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Última Atualização</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Informações de Assinatura --}}
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 font-serif">Assinatura</h2>
                    @php
                        $activeSubscription = $user->activeSubscription();
                        $latestSubscription = $user->subscriptions()->latest()->first();
                    @endphp
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if($activeSubscription)
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Ativa</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">Inativa</span>
                                @endif
                            </dd>
                        </div>
                        @if($latestSubscription)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipo de Plano</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $latestSubscription->plan_type === 'physical' ? 'Física' : 'Virtual' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Data de Início</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($latestSubscription->start_date)
                                        {{ \Carbon\Carbon::parse($latestSubscription->start_date)->format('d/m/Y') }}
                                    @else
                                        <span class="text-gray-400">Não definida</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Data de Término</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($latestSubscription->end_date)
                                        {{ \Carbon\Carbon::parse($latestSubscription->end_date)->format('d/m/Y') }}
                                        @if(\Carbon\Carbon::parse($latestSubscription->end_date)->isPast())
                                            <span class="ml-2 text-xs text-red-600">(Expirada)</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Não definida</span>
                                    @endif
                                </dd>
                            </div>
                        @else
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nenhuma assinatura encontrada</dt>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <a href="{{ route('admin.users.index') }}" class="text-red-800 hover:text-red-900 font-medium">
                    ← Voltar para Lista
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


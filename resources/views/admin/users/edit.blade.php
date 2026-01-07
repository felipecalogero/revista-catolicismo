@extends('layouts.app')

@section('title', 'Editar Usuário - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Editar Usuário
                </h1>
                <p class="text-gray-600">Atualize as informações do usuário</p>
            </div>

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nome --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome <span class="text-red-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Nome completo"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-600">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="email@exemplo.com"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nova Senha --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Nova Senha
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Deixe em branco para manter a senha atual"
                        >
                        <p class="mt-1 text-sm text-gray-500">Deixe em branco para manter a senha atual</p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirmar Nova Senha --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Nova Senha
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Confirme a nova senha"
                        >
                    </div>
                </div>

                {{-- Função --}}
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Função <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="role"
                        name="role"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                    >
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Usuário</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Seção de Assinatura --}}
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Informações de Assinatura</h3>
                    
                    <div class="flex items-center mb-4">
                        <input
                            type="checkbox"
                            id="subscription_active"
                            name="subscription_active"
                            value="1"
                            {{ old('subscription_active', $user->subscription_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-red-800 border-gray-300 rounded focus:ring-red-800"
                        >
                        <label for="subscription_active" class="ml-2 text-sm font-medium text-gray-700">
                            Assinatura Ativa
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Data de Início --}}
                        <div>
                            <label for="subscription_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Início
                            </label>
                            <input
                                type="date"
                                id="subscription_start_date"
                                name="subscription_start_date"
                                value="{{ old('subscription_start_date', $user->subscription_start_date ? \Carbon\Carbon::parse($user->subscription_start_date)->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            >
                            @error('subscription_start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Data de Término --}}
                        <div>
                            <label for="subscription_end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Término
                            </label>
                            <input
                                type="date"
                                id="subscription_end_date"
                                name="subscription_end_date"
                                value="{{ old('subscription_end_date', $user->subscription_end_date ? \Carbon\Carbon::parse($user->subscription_end_date)->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            >
                            @error('subscription_end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Plano --}}
                        <div>
                            <label for="subscription_plan" class="block text-sm font-medium text-gray-700 mb-2">
                                Plano
                            </label>
                            <select
                                id="subscription_plan"
                                name="subscription_plan"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            >
                                <option value="">Selecione um plano</option>
                                <option value="monthly" {{ old('subscription_plan', $user->subscription_plan) === 'monthly' ? 'selected' : '' }}>Mensal</option>
                                <option value="yearly" {{ old('subscription_plan', $user->subscription_plan) === 'yearly' ? 'selected' : '' }}>Anual</option>
                            </select>
                            @error('subscription_plan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Botões --}}
                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button
                        type="submit"
                        class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium"
                    >
                        Atualizar Usuário
                    </button>
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


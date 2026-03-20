@extends('layouts.app')

@section('title', 'Criar Usuário - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Criar Novo Usuário
                </h1>
                <p class="text-gray-600">Preencha os campos abaixo para criar um novo usuário</p>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
                @csrf

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
                            value="{{ old('name') }}"
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
                            value="{{ old('email') }}"
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
                    {{-- Senha --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Senha <span class="text-red-600">*</span>
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Mínimo 8 caracteres"
                            oninput="validatePassword(this.value); validatePasswordMatch();"
                        >
                        {{-- Requisitos de Senha Forte --}}
                        <ul class="mt-3 space-y-2" id="password-requirements">
                            <li class="flex items-center text-sm" id="req-length">
                                <span class="requirement-icon mr-2">✗</span>
                                <span>Mínimo de 8 caracteres</span>
                            </li>
                            <li class="flex items-center text-sm" id="req-uppercase">
                                <span class="requirement-icon mr-2">✗</span>
                                <span>Pelo menos uma letra maiúscula (A-Z)</span>
                            </li>
                            <li class="flex items-center text-sm" id="req-lowercase">
                                <span class="requirement-icon mr-2">✗</span>
                                <span>Pelo menos uma letra minúscula (a-z)</span>
                            </li>
                            <li class="flex items-center text-sm" id="req-number">
                                <span class="requirement-icon mr-2">✗</span>
                                <span>Pelo menos um número (0-9)</span>
                            </li>
                            <li class="flex items-center text-sm" id="req-symbol">
                                <span class="requirement-icon mr-2">✗</span>
                                <span>Pelo menos um caractere especial (!@#$%^&*()_+-=[]{}|;:,.<>?)</span>
                            </li>
                        </ul>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirmar Senha --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Senha <span class="text-red-600">*</span>
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Confirme a senha"
                            oninput="validatePasswordMatch()"
                        >
                        <div id="password-match-message" class="mt-2 text-sm hidden"></div>
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
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Usuário</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                {{-- Botões --}}
                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button
                        type="submit"
                        id="submit-button"
                        class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Criar Usuário
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
@push('scripts')
<script src="{{ asset('js/password-validation.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    PasswordValidation.init();
});
</script>
@endpush
@endsection


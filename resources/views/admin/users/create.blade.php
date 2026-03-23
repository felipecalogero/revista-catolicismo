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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- CPF --}}
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                            CPF/CNPJ
                        </label>
                        <input
                            type="text"
                            id="cpf"
                            name="cpf"
                            value="{{ old('cpf') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="000.000.000-00 ou 00.000.000/0000-00"
                            maxlength="18"
                        >
                        @error('cpf')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CEP --}}
                    <div>
                        <label for="zip_code" class="block text-sm font-medium text-gray-700 mb-2">
                            CEP
                        </label>
                        <input
                            type="text"
                            id="zip_code"
                            name="zip_code"
                            value="{{ old('zip_code') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="00000-000"
                            maxlength="9"
                        >
                        @error('zip_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Endereço --}}
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Endereço (Rua e Número)
                        </label>
                        <input
                            type="text"
                            id="address"
                            name="address"
                            value="{{ old('address') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Ex: Rua das Flores, 123"
                        >
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Bairro --}}
                    <div>
                        <label for="neighborhood" class="block text-sm font-medium text-gray-700 mb-2">
                            Bairro
                        </label>
                        <input
                            type="text"
                            id="neighborhood"
                            name="neighborhood"
                            value="{{ old('neighborhood') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Bairro"
                        >
                        @error('neighborhood')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cidade --}}
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                            Cidade
                        </label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Cidade"
                        >
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Estado --}}
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado
                        </label>
                        <input
                            type="text"
                            id="state"
                            name="state"
                            value="{{ old('state') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="UF"
                            maxlength="2"
                        >
                        @error('state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Telefone --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone
                        </label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="(XX) XXXXX-XXXX"
                            maxlength="15"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                </div>

                {{-- Seção de Assinatura --}}
                <div id="subscription-section" class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 font-serif mb-4 pb-2 border-b border-gray-300">
                        Gerenciar Assinatura (já em andamento)
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Tipo de Assinatura --}}
                        <div>
                            <label for="plan_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Plano
                            </label>
                            <select
                                id="plan_type"
                                name="plan_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            >
                                <option value="none" {{ old('plan_type') === 'none' ? 'selected' : '' }}>Sem assinatura</option>
                                <option value="virtual" {{ old('plan_type') === 'virtual' ? 'selected' : '' }}>Assinatura Virtual</option>
                                <option value="physical" {{ old('plan_type') === 'physical' ? 'selected' : '' }}>Assinatura Física</option>
                            </select>
                            @error('plan_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Data de Início --}}
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Início
                            </label>
                            <input
                                type="date"
                                id="start_date"
                                name="start_date"
                                value="{{ old('start_date') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            >
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Data de Término --}}
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Término
                            </label>
                            <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                value="{{ old('end_date') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            >
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Use estes campos para registrar assinaturas que já estão em andamento ou foram adquiridas por outros meios.
                    </p>
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

    const roleSelect = document.getElementById('role');
    const subscriptionSection = document.getElementById('subscription-section');

    function toggleSubscriptionSection() {
        if (roleSelect && subscriptionSection) {
            if (roleSelect.value === 'admin') {
                subscriptionSection.style.display = 'none';
            } else {
                subscriptionSection.style.display = 'block';
            }
        }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', toggleSubscriptionSection);
        toggleSubscriptionSection(); // Run on load
    }

    const cpfInput = document.getElementById('cpf');
    const phoneInput = document.getElementById('phone');
    const zipCodeInput = document.getElementById('zip_code');
    const addressInput = document.getElementById('address');
    const neighborhoodInput = document.getElementById('neighborhood');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');

    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 14) value = value.slice(0, 14);
            
            let masked = '';
            if (value.length <= 11) {
                // CPF: 000.000.000-00
                if (value.length > 0) masked = value.slice(0, 3);
                if (value.length > 3) masked += '.' + value.slice(3, 6);
                if (value.length > 6) masked += '.' + value.slice(6, 9);
                if (value.length > 9) masked += '-' + value.slice(9, 11);
            } else {
                // CNPJ: 00.000.000/0000-00
                if (value.length > 0) masked = value.slice(0, 2);
                if (value.length > 2) masked += '.' + value.slice(2, 5);
                if (value.length > 5) masked += '.' + value.slice(5, 8);
                if (value.length > 8) masked += '/' + value.slice(8, 12);
                if (value.length > 12) masked += '-' + value.slice(12, 14);
            }
            e.target.value = masked;
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            let masked = '';
            if (value.length > 0) masked = '(' + value;
            if (value.length > 2) masked = '(' + value.slice(0, 2) + ') ' + value.slice(2);
            if (value.length > 7) {
                if (value.length === 11) {
                    masked = '(' + value.slice(0, 2) + ') ' + value.slice(2, 7) + '-' + value.slice(7);
                } else {
                    masked = '(' + value.slice(0, 2) + ') ' + value.slice(2, 6) + '-' + value.slice(6);
                }
            }
            e.target.value = masked;
        });
    }

    if (zipCodeInput) {
        zipCodeInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) value = value.slice(0, 8);
            
            let masked = value;
            if (value.length > 5) masked = value.slice(0, 5) + '-' + value.slice(5);
            e.target.value = masked;

            if (value.length === 8) {
                fetch(`https://viacep.com.br/ws/${value}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            if (addressInput) addressInput.value = data.logradouro;
                            if (neighborhoodInput) neighborhoodInput.value = data.bairro;
                            if (cityInput) cityInput.value = data.localidade;
                            if (stateInput) stateInput.value = data.uf;
                            
                            if (addressInput) {
                                addressInput.focus();
                                if (data.logradouro) addressInput.value += ', ';
                            }
                        }
                    })
                    .catch(error => console.error('Erro ao buscar CEP:', error));
            }
        });
    }
});
</script>
@endpush
@endsection


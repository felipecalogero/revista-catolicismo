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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Deixe em branco para manter a senha atual"
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Confirme a nova senha"
                            oninput="validatePasswordMatch()"
                        >
                        <div id="password-match-message" class="mt-2 text-sm hidden"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- CPF --}}
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                            CPF
                        </label>
                        <input
                            type="text"
                            id="cpf"
                            name="cpf"
                            value="{{ old('cpf', $user->formatted_cpf) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="000.000.000-00"
                            maxlength="14"
                        >
                        @error('cpf')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Endereço --}}
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Endereço
                        </label>
                        <input
                            type="text"
                            id="address"
                            name="address"
                            value="{{ old('address', $user->address) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Endereço completo"
                        >
                        @error('address')
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
                            value="{{ old('phone', $user->formatted_phone) }}"
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
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Usuário</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Seção de Assinatura --}}
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
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
                                <option value="none" {{ !$latestSubscription ? 'selected' : '' }}>Sem assinatura</option>
                                <option value="virtual" {{ (old('plan_type') ?? ($latestSubscription->plan_type ?? '')) === 'virtual' ? 'selected' : '' }}>Assinatura Virtual</option>
                                <option value="physical" {{ (old('plan_type') ?? ($latestSubscription->plan_type ?? '')) === 'physical' ? 'selected' : '' }}>Assinatura Física</option>
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
                                value="{{ old('start_date', $latestSubscription && $latestSubscription->start_date ? $latestSubscription->start_date->format('Y-m-d') : '') }}"
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
                                value="{{ old('end_date', $latestSubscription && $latestSubscription->end_date ? $latestSubscription->end_date->format('Y-m-d') : '') }}"
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
@push('scripts')
<script src="{{ asset('js/password-validation.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        PasswordValidation.init({
            onValidityChange: (isValid, data) => {
                // No edit, se a senha estiver em branco, é válido (mantém atual)
                if (!data.password && !data.passwordConfirmation) return true;
                return isValid;
            }
        });

        const cpfInput = document.getElementById('cpf');
        const phoneInput = document.getElementById('phone');

        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);
                
                let masked = value;
                if (value.length > 3) masked = value.slice(0, 3) + '.' + value.slice(3);
                if (value.length > 6) masked = masked.slice(0, 7) + '.' + masked.slice(7);
                if (value.length > 9) masked = masked.slice(0, 11) + '-' + masked.slice(11);
                
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
    });
</script>
@endpush
@endsection


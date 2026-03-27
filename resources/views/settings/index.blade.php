@extends('layouts.app')

@section('title', 'Configurações - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 font-serif mb-8 border-b-2 border-red-800 pb-2">
                Configurações da Conta
            </h1>

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                {{-- Informações Pessoais --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 font-serif mb-6 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-red-800"></i>
                        Informações Pessoais
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                            <input type="email" id="email" value="{{ $user->email }}" disabled
                                class="w-full px-4 py-2 border border-gray-100 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500 italic">O e-mail não pode ser alterado.</p>
                        </div>

                        <div>
                            <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">CPF/CNPJ</label>
                            <input type="text" id="cpf" name="cpf" value="{{ old('cpf', $user->formatted_cpf) }}" 
                                maxlength="18"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                placeholder="000.000.000-00 ou 00.000.000/0000-00">
                            @error('cpf') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->formatted_phone) }}" 
                                maxlength="15"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                placeholder="(00) 00000-0000">
                            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- CEP --}}
                        <div>
                            <label for="zip_code" class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                            <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code', $user->zip_code) }}" 
                                maxlength="9"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                placeholder="00000-000">
                            @error('zip_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Endereço --}}
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Endereço (Rua e Número)</label>
                            <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                placeholder="Ex: Rua das Flores, 123">
                            @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Bairro --}}
                        <div>
                            <label for="neighborhood" class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                            <input type="text" id="neighborhood" name="neighborhood" value="{{ old('neighborhood', $user->neighborhood) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                placeholder="Bairro">
                            @error('neighborhood') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Cidade --}}
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                            <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                placeholder="Cidade">
                            @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Estado --}}
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <input type="text" id="state" name="state" value="{{ old('state', $user->state) }}" 
                                maxlength="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                placeholder="UF">
                            @error('state') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Alterar Senha --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 font-serif mb-6 flex items-center border-b border-gray-100 pb-4">
                        <i class="fas fa-lock mr-2 text-red-800"></i>
                        Alterar Senha
                    </h2>

                    <div class="space-y-6">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2 text-red-800 italic">
                                Senha Atual (necessária para qualquer alteração)
                            </label>
                            <input type="password" id="current_password" name="current_password" 
                                class="w-full px-4 py-2 border-2 border-red-100 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent shadow-sm"
                                placeholder="Digite sua senha atual">
                            @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-50">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Nova Senha</label>
                                <input type="password" id="password" name="password" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                    placeholder="Deixe em branco para não alterar">
                                
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
                                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nova Senha</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                    placeholder="Confirme a nova senha">
                                <div id="password-match-message" class="mt-2 text-sm hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ações --}}
                <div class="flex items-center justify-between bg-white rounded-lg shadow-md p-6 border-t-4 border-red-800">
                    <p class="text-sm text-gray-500 italic max-w-md">
                        <i class="fas fa-info-circle mr-1"></i>
                        Para sua segurança, pedimos que confirme sua senha atual antes de salvar qualquer alteração nos seus dados.
                    </p>
                    <div class="flex gap-4">
                        <a href="{{ route('home') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Voltar
                        </a>
                        <button type="submit" id="save-all-button"
                            class="px-8 py-2 bg-red-800 text-white rounded-lg hover:bg-red-900 transition-colors font-bold shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/password-validation.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuração da validação de senha
    const currentPasswordInput = document.getElementById('current_password');
    const passwordInput = document.getElementById('password');
    const confirmationInput = document.getElementById('password_confirmation');

    const getFormValidity = (isValid, data) => {
        // Se a senha atual não estiver preenchida, o formulário é inválido
        if (!currentPasswordInput.value || currentPasswordInput.value.length === 0) {
            return false;
        }

        // Se estiver tentando mudar a senha (campos de nova senha não estão vazios)
        const isChangingPassword = data.password.length > 0 || (confirmationInput && confirmationInput.value.length > 0);
        
        if (isChangingPassword) {
            // Se estiver mudando a senha, exige que a nova senha seja válida (força e correspondência)
            return isValid;
        }

        // Se NÃO estiver mudando a senha, mas preencheu a senha atual, permite salvar (para outros dados)
        return true;
    };

    PasswordValidation.init({
        submitButtonId: 'save-all-button',
        onValidityChange: getFormValidity
    });
    
    // Monitorar a senha atual especificamente pois ela não faz parte do objeto padrão PasswordValidation
    if (currentPasswordInput) {
        currentPasswordInput.addEventListener('input', () => {
            PasswordValidation.updateSubmitButton({
                ...PasswordValidation.defaults,
                submitButtonId: 'save-all-button',
                onValidityChange: getFormValidity
            });
        });
    }

    // Máscaras para CPF e Telefone
    const cpfInput = document.getElementById('cpf');
    const phoneInput = document.getElementById('phone');

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

    // Máscaras e ViaCEP para Endereço
    const zipCodeInput = document.getElementById('zip_code');
    const addressInput = document.getElementById('address');
    const neighborhoodInput = document.getElementById('neighborhood');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');

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

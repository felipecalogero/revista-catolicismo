@extends('layouts.app')

@section('title', 'Registro - Revista Catolicismo')

@section('content')
<div class="flex-grow flex items-center justify-center bg-gray-50 py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 mt-[-5rem]">
        <div>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 font-serif">
                Criar nova conta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Já tem uma conta?
                <a href="{{ route('login') }}" class="font-medium text-red-800 hover:text-red-900">
                    Faça login
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-md border border-gray-200" action="{{ route('register') }}" method="POST">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nome completo
                    </label>
                    <input 
                        id="name" 
                        name="name" 
                        type="text" 
                        autocomplete="name" 
                        required 
                        value="{{ old('name') }}"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="Seu nome"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        E-mail
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        value="{{ old('email') }}"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="seu@email.com"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Senha
                    </label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="new-password" 
                        required 
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent @error('password') border-red-500 @enderror"
                        placeholder="••••••••"
                        oninput="validatePassword(this.value); validatePasswordMatch();"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Requisitos de Senha Forte --}}
                    <ul class="mt-3 space-y-2" id="password-requirements">
                        <li class="flex items-center text-sm" id="req-length">
                            <span class="requirement-icon mr-2 text-red-600">✗</span>
                            <span class="text-gray-700">Mínimo de 8 caracteres</span>
                        </li>
                        <li class="flex items-center text-sm" id="req-uppercase">
                            <span class="requirement-icon mr-2 text-red-600">✗</span>
                            <span class="text-gray-700">Pelo menos uma letra maiúscula (A-Z)</span>
                        </li>
                        <li class="flex items-center text-sm" id="req-lowercase">
                            <span class="requirement-icon mr-2 text-red-600">✗</span>
                            <span class="text-gray-700">Pelo menos uma letra minúscula (a-z)</span>
                        </li>
                        <li class="flex items-center text-sm" id="req-number">
                            <span class="requirement-icon mr-2 text-red-600">✗</span>
                            <span class="text-gray-700">Pelo menos um número (0-9)</span>
                        </li>
                        <li class="flex items-center text-sm" id="req-symbol">
                            <span class="requirement-icon mr-2 text-red-600">✗</span>
                            <span class="text-gray-700">Pelo menos um caractere especial (!@#$%^&*()_+-=[]{}|;:,.<>?)</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar senha
                    </label>
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        autocomplete="new-password" 
                        required 
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        placeholder="••••••••"
                        oninput="validatePasswordMatch()"
                    >
                    <div id="password-match-message" class="mt-2 text-sm hidden"></div>
                </div>
            </div>

            <div>
                <button 
                    id="submit-button"
                    type="submit" 
                    disabled
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-800 hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Criar conta
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
function validatePassword(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        symbol: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password)
    };

    // Atualizar cada requisito
    updateRequirement('req-length', requirements.length);
    updateRequirement('req-uppercase', requirements.uppercase);
    updateRequirement('req-lowercase', requirements.lowercase);
    updateRequirement('req-number', requirements.number);
    updateRequirement('req-symbol', requirements.symbol);

    checkFormValidity();
}

function updateRequirement(id, isValid) {
    const element = document.getElementById(id);
    const icon = element.querySelector('.requirement-icon');
    const text = element.querySelector('span:last-child');
    
    if (isValid) {
        icon.textContent = '✓';
        icon.className = 'requirement-icon mr-2 text-green-600 font-bold';
        text.className = 'text-green-700';
    } else {
        icon.textContent = '✗';
        icon.className = 'requirement-icon mr-2 text-red-600';
        text.className = 'text-gray-700';
    }
}

function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const messageDiv = document.getElementById('password-match-message');
    const confirmationInput = document.getElementById('password_confirmation');
    
    if (passwordConfirmation.length === 0) {
        messageDiv.classList.add('hidden');
        confirmationInput.classList.remove('border-green-500', 'border-red-500');
        confirmationInput.classList.add('border-gray-300');
        checkFormValidity();
        return;
    }
    
    messageDiv.classList.remove('hidden');
    
    if (password === passwordConfirmation) {
        messageDiv.textContent = '✓ As senhas coincidem';
        messageDiv.className = 'mt-2 text-sm text-green-600 font-medium';
        confirmationInput.classList.remove('border-red-500', 'border-gray-300');
        confirmationInput.classList.add('border-green-500');
    } else {
        messageDiv.textContent = '✗ As senhas não coincidem';
        messageDiv.className = 'mt-2 text-sm text-red-600 font-medium';
        confirmationInput.classList.remove('border-green-500', 'border-gray-300');
        confirmationInput.classList.add('border-red-500');
    }

    checkFormValidity();
}

function checkFormValidity() {
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const submitButton = document.getElementById('submit-button');
    
    const isStrengthValid = (
        password.length >= 8 &&
        /[A-Z]/.test(password) &&
        /[a-z]/.test(password) &&
        /[0-9]/.test(password) &&
        /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password)
    );
    
    const isMatchValid = (password.length > 0 && password === passwordConfirmation);
    
    if (submitButton) {
        submitButton.disabled = !(isStrengthValid && isMatchValid);
    }
}

// Validar senha ao carregar a página se já houver valor
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    if (passwordInput && passwordInput.value) {
        validatePassword(passwordInput.value);
        validatePasswordMatch();
    }
    checkFormValidity();
});
</script>
@endpush
@endsection


@extends('layouts.app')

@section('title', 'Configurações - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                Configurações
            </h1>
            <p class="text-gray-600">Gerencie suas informações pessoais e preferências</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="mb-6 pb-4 border-b-2 border-red-800">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif">Informações Pessoais</h2>
                    <p class="text-gray-600 text-sm mt-1">Atualize seu nome{{ Auth::user()->isAdmin() ? ' e endereço de e-mail' : '' }}</p>
                </div>

                <form id="profile-form" action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Completo
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name', $user->name) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                required
                            >
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                E-mail
                            </label>
                            @if(Auth::user()->isAdmin())
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email', $user->email) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                    required
                                >
                            @else
                                <input 
                                    type="email" 
                                    id="email" 
                                    value="{{ $user->email }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                    readonly
                                    disabled
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    O e-mail não pode ser alterado por questões de segurança. Entre em contato com o administrador se precisar alterar seu e-mail.
                                </p>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="mb-6 pb-4 border-b-2 border-red-800">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif">Alterar Senha</h2>
                    <p class="text-gray-600 text-sm mt-1">Para sua segurança, use uma senha forte</p>
                </div>

                <form id="password-form" action="{{ route('settings.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Senha Atual
                            </label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            >
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Nova Senha
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
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
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirmar Nova Senha
                            </label>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                oninput="validatePasswordMatch()"
                            >
                            <div id="password-match-message" class="mt-2 text-sm hidden"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-6 flex justify-between items-center">
            <a 
                href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" 
                class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors font-medium"
            >
                ← Voltar ao Dashboard
            </a>
            <button 
                type="button"
                id="save-all-button"
                class="bg-red-800 text-white px-6 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium"
            >
                Salvar Alterações
            </button>
        </div>
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
}

// Validar senha ao carregar a página se já houver valor
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    if (passwordInput && passwordInput.value) {
        validatePassword(passwordInput.value);
        validatePasswordMatch();
    }

    // Botão único para salvar tudo
    const saveAllButton = document.getElementById('save-all-button');
    const profileForm = document.getElementById('profile-form');
    const passwordForm = document.getElementById('password-form');
    
    saveAllButton.addEventListener('click', function(e) {
        e.preventDefault();
        const button = this;
        const originalText = button.textContent;
        
        // Verificar se os campos de senha foram preenchidos
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;
        
        const hasPasswordFields = currentPassword || newPassword || passwordConfirmation;
        const allPasswordFieldsFilled = currentPassword && newPassword && passwordConfirmation;
        
        // Se algum campo de senha foi preenchido, todos devem estar preenchidos
        if (hasPasswordFields && !allPasswordFieldsFilled) {
            alert('Por favor, preencha todos os campos de senha para alterar a senha, ou deixe todos vazios para atualizar apenas as informações pessoais.');
            return;
        }
        
        // Validar senha se os campos foram preenchidos
        if (allPasswordFieldsFilled) {
            const password = document.getElementById('password').value;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                symbol: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password)
            };
            
            const allRequirementsMet = Object.values(requirements).every(req => req === true);
            
            if (!allRequirementsMet) {
                alert('A senha não atende a todos os requisitos de segurança. Por favor, verifique os requisitos listados.');
                return;
            }
            
            if (password !== passwordConfirmation) {
                alert('As senhas não coincidem. Por favor, verifique.');
                return;
            }
        }
        
        // Desabilitar botão
        button.disabled = true;
        button.textContent = 'Salvando...';
        
        // Se os campos de senha foram preenchidos, vamos submeter ambos sequencialmente
        if (allPasswordFieldsFilled) {
            // Primeiro submeter o formulário de perfil
            // Usar um iframe oculto ou AJAX para não recarregar a página
            const profileFormData = new FormData(profileForm);
            
            fetch(profileForm.action, {
                method: 'POST',
                body: profileFormData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                redirect: 'follow'
            })
            .then(response => {
                // Laravel retorna redirects mesmo em AJAX, então vamos considerar sucesso se status for 200 ou redirect
                if (response.ok || response.status === 302 || response.redirected) {
                    // Agora submeter o formulário de senha
                    const passwordFormData = new FormData(passwordForm);
                    return fetch(passwordForm.action, {
                        method: 'POST',
                        body: passwordFormData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        redirect: 'follow'
                    });
                } else {
                    return response.text().then(text => {
                        throw new Error('Erro ao atualizar perfil: ' + text);
                    });
                }
            })
            .then(response => {
                // Recarregar a página para mostrar mensagens de sucesso
                window.location.href = '{{ route("settings.index") }}';
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao salvar. Por favor, tente novamente.');
                button.disabled = false;
                button.textContent = originalText;
            });
        } else {
            // Apenas submeter o formulário de perfil normalmente
            profileForm.submit();
        }
    });
});
</script>
@endpush
@endsection

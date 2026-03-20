@extends('layouts.app')

@section('title', 'Registro - Revista Catolicismo')

@section('content')
<div class="relative flex-grow flex items-center justify-center bg-[#f5f0e6] py-20 px-4 sm:px-6 lg:px-8">
    <img
        src="{{ asset('img/textura.jpeg') }}"
        alt=""
        class="absolute inset-0 w-full h-full object-cover"
    >
    <div class="relative z-10 max-w-md w-full space-y-8 mt-[-5rem]">
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
<script src="{{ asset('js/password-validation.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        PasswordValidation.init();
    });
</script>
@endpush
@endsection

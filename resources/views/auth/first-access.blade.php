@extends('layouts.app')

@section('title', 'Primeiro Acesso - Revista Catolicismo')

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
                Primeiro Acesso
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Informe seu e-mail para configurar sua primeira senha de acesso.
            </p>
        </div>
        
        <div class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-md border border-gray-200">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    <span class="block sm:inline text-sm font-medium">{{ session('success') }}</span>
                </div>
                <div class="text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-red-800 hover:bg-red-900 transition-colors">
                        Voltar para o Login
                    </a>
                </div>
            @else
                <form class="space-y-6" action="{{ route('first-access.send') }}" method="POST">
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

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Endereço de E-mail
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
                        <button 
                            type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-800 hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-800 transition-colors"
                        >
                            Enviar Link de Acesso
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-100 flex flex-col items-center space-y-4">
                    <p class="text-sm text-gray-500">Já possui uma senha definida?</p>
                    <a href="{{ route('login') }}" class="font-medium text-red-800 hover:text-red-900">
                        Entrar no Sistema
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

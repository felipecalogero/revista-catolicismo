@extends('layouts.app')

@section('title', 'Verifique seu e-mail - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border border-gray-100">
        <div>
            <div class="flex justify-center">
                <div class="bg-red-50 p-3 rounded-full">
                    <svg class="h-12 w-12 text-red-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 font-serif">
                Verifique seu e-mail
            </h2>
            <p class="mt-4 text-center text-sm text-gray-600 leading-relaxed">
                Obrigado por se associar à <strong>Revista Catolicismo</strong>! Antes de começar, por favor verifique seu endereço de e-mail clicando no link que acabamos de enviar para você.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-md bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            Um novo link de verificação foi enviado para o endereço de e-mail fornecido.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-8 space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-800 hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-800 transition-all shadow-md">
                    Reenviar e-mail de verificação
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="flex justify-center">
                @csrf
                <button type="submit" class="text-sm font-medium text-gray-600 hover:text-red-800 transition-colors">
                    Sair da conta
                </button>
            </form>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400 italic">
                Verifique também sua pasta de spam caso não encontre o e-mail na caixa de entrada.
            </p>
        </div>
    </div>
</div>
@endsection

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
                Obrigado por se associar à <strong>Revista Catolicismo</strong>! Enviamos um e-mail de verificação para sua conta. Clique no link recebido para liberar seu acesso.
            </p>
            <p class="mt-2 text-center text-sm font-medium text-gray-800">
                E-mail de destino: <span class="text-red-800">{{ $maskedEmail }}</span>
            </p>
            <p class="mt-2 text-center text-sm text-gray-600 leading-relaxed">
                Se não encontrar o e-mail em instantes, use o botão abaixo para reenviar.
            </p>
        </div>

        @if (session('email-updated'))
            <div class="rounded-md bg-blue-50 p-4 border border-blue-200">
                <p class="text-sm font-medium text-blue-900">
                    E-mail atualizado com sucesso. Enviamos um novo link de verificação.
                </p>
            </div>
        @endif

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
            <form method="POST" action="{{ route('verification.send') }}" id="resend-verification-form">
                @csrf
                <button
                    type="submit"
                    id="resend-verification-button"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-800 hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-800 transition-all shadow-md disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span id="resend-verification-button-text">Reenviar e-mail de verificação</span>
                </button>
            </form>

            <form method="POST" action="{{ route('verification.update-email') }}" class="space-y-2 rounded-lg border border-gray-200 p-4 bg-gray-50">
                @csrf
                <label for="verification_email" class="block text-sm font-medium text-gray-700">
                    Digitou o e-mail errado? Atualize abaixo
                </label>
                <input
                    type="email"
                    id="verification_email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-800 focus:border-transparent"
                    placeholder="seuemail@dominio.com"
                >
                @error('email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button
                    type="submit"
                    class="w-full rounded-lg border border-gray-300 bg-white py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                >
                    Atualizar e reenviar verificação
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="flex justify-center">
                @csrf
                <button type="submit" class="text-sm font-medium text-gray-600 hover:text-red-800 transition-colors">
                    Sair da conta
                </button>
            </form>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center rounded-lg bg-amber-50 border border-amber-200 p-4">
            <p class="text-sm text-amber-900 font-medium">
                Não encontrou? Verifique também as pastas Spam, Promoções e Lixeira.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('resend-verification-form');
        const button = document.getElementById('resend-verification-button');
        const text = document.getElementById('resend-verification-button-text');
        const cooldownSeconds = 30;

        if (!form || !button || !text) return;

        const runCooldown = (seconds) => {
            let remaining = seconds;
            button.disabled = true;

            const tick = () => {
                if (remaining <= 0) {
                    button.disabled = false;
                    text.textContent = 'Reenviar e-mail de verificação';
                    return;
                }

                text.textContent = `Reenviar em ${remaining}s`;
                remaining -= 1;
                setTimeout(tick, 1000);
            };

            tick();
        };

        form.addEventListener('submit', function () {
            button.disabled = true;
            text.textContent = 'Enviando...';
        });

        @if (session('status') === 'verification-link-sent')
            runCooldown(cooldownSeconds);
        @endif
    });
</script>
@endpush

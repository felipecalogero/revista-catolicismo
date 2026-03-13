@extends('layouts.app')

@section('title', 'Fale Conosco - Revista Catolicismo')

@section('content')
<div class="relative min-h-screen bg-[#f8f1e4] py-12">
    <img
        src="{{ asset('img/textura.jpeg') }}"
        alt=""
        class="absolute inset-0 w-full h-full object-cover"
    >
    <div class="relative z-10 max-w-6xl mx-auto px-4 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 font-serif mb-4">
                Fale Conosco
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Estamos à disposição para ouvir suas sugestões, tirar dúvidas ou receber seu feedback.
            </p>
            <div class="w-24 h-1 bg-red-800 mx-auto mt-6"></div>
        </div>

        <div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Informações de Contato --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 font-serif mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        E-mail
                    </h3>
                    <p class="text-gray-600 break-all">
                        contato@catolicismo.com.br
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 font-serif mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Horário de Atendimento
                    </h3>
                    <p class="text-gray-600">
                        Segunda a Sexta<br>
                        09:00 às 18:00
                    </p>
                </div>
            </div>

            {{-- Formulário --}}
            <div class="lg:col-span-2">
                <div class="bg-white p-8 rounded-lg shadow-lg border border-gray-100">
                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent outline-none transition-all @error('name') border-red-500 @enderror">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent outline-none transition-all @error('email') border-red-500 @enderror">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Assunto</label>
                            <input type="text" name="subject" id="subject" required value="{{ old('subject') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent outline-none transition-all @error('subject') border-red-500 @enderror">
                            @error('subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Mensagem</label>
                            <textarea name="message" id="message" rows="5" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent outline-none transition-all @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                            @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                            class="w-full bg-red-800 text-white px-6 py-3 rounded-lg font-bold hover:bg-red-900 transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Enviar Mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

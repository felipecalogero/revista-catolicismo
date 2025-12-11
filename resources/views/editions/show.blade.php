@extends('layouts.app')

@section('title', $edition->title . ' - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-white">
    <div class="container mx-auto px-4 lg:px-8 py-12">
        {{-- Cabeçalho da Edição --}}
        <div class="mb-8 pb-6 border-b-2 border-red-800">
            <div class="flex flex-col md:flex-row gap-8">
                {{-- Imagem da Capa --}}
                <div class="flex-shrink-0">
                    @if($edition->cover_image)
                        <img
                            src="{{ Storage::url($edition->cover_image) }}"
                            alt="{{ $edition->title }}"
                            class="w-full md:w-64 h-auto rounded-lg shadow-lg"
                        >
                    @endif
                </div>

                {{-- Informações --}}
                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-gray-900 font-serif mb-4">
                        {{ $edition->title }}
                    </h1>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                        @if($edition->published_at)
                            <span>Publicada em {{ $edition->published_at->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    <p class="text-lg text-gray-700 leading-relaxed mb-6">
                        {{ $edition->description }}
                    </p>

                    {{-- Botão de Download --}}
                    @auth
                        <a
                            href="{{ route('editions.download', $edition->slug) }}"
                            class="inline-block bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium"
                        >
                            Baixar PDF
                        </a>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-red-800 font-medium mb-2">Assine para ter acesso completo</p>
                            <p class="text-sm text-gray-700 mb-4">Faça login ou assine a revista para baixar esta edição em PDF.</p>
                            <div class="flex gap-3">
                                <a href="{{ route('login') }}" class="bg-red-800 text-white px-6 py-2 rounded hover:bg-red-900 transition-colors font-medium text-sm">
                                    Entrar
                                </a>
                                <a href="#" class="bg-white text-red-800 px-6 py-2 rounded border border-red-800 hover:bg-red-50 transition-colors font-medium text-sm">
                                    Assinar Agora
                                </a>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Conteúdo Adicional (pode ser expandido no futuro) --}}
        <div class="prose prose-lg max-w-none">
            <p class="text-gray-600">
                Esta edição está disponível para download em formato PDF. Assine a revista para ter acesso completo a todas as edições.
            </p>
        </div>
    </div>
</div>
@endsection

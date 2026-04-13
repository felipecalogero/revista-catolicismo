@extends('layouts.app')

@section('title', 'Todas as Notícias - Revista Catolicismo')

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6]">
    <img
        src="{{ asset('img/textura.jpeg') }}"
        alt=""
        class="absolute inset-0 w-full h-full object-cover"
    >
    <div class="relative z-10">
        <section class="py-12">
            <div class="container mx-auto px-4 lg:px-8">
                <div class="bg-white rounded-lg p-6 md:p-8">
                    <div class="mb-8 pb-6 border-b-2 border-red-800">
                        <nav class="text-sm text-gray-600 mb-4">
                            <a href="{{ route('home') }}" class="hover:text-red-800 transition-colors">Início</a>
                            <span class="mx-2">/</span>
                            <span class="text-gray-900 font-medium">Todas as Notícias</span>
                        </nav>
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 font-serif mb-4">
                            Todas as Notícias
                        </h1>
                    </div>

                    {{-- Buscador e Filtro --}}
                    <div class="bg-white rounded-lg p-6 mb-10 border border-gray-200">
                        <p class="mb-3 text-sm font-medium text-gray-700">Buscar notícias</p>
                        <x-admin.filter-bar
                            :formAction="route('articles.index')"
                            modalId="publicArticlesFilterModal"
                            searchPlaceholder="Título, autor, texto, slug, categoria ou link de vídeo…"
                            :clearUrl="route('articles.index')"
                        >
                            <x-slot name="modal">
                                <div>
                                    <label for="public_filter_category" class="mb-1 block text-sm font-medium text-gray-700">Categoria</label>
                                    <select id="public_filter_category" name="category" class="w-full rounded-lg border border-gray-300 py-2 text-sm">
                                        <option value="">Todas as categorias</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="public_filter_free" class="mb-1 block text-sm font-medium text-gray-700">Acesso livre</label>
                                    <select id="public_filter_free" name="free_access" class="w-full rounded-lg border border-gray-300 py-2 text-sm">
                                        <option value="">Todos</option>
                                        <option value="1" @selected(request('free_access') === '1')>Sim (leitura sem assinatura)</option>
                                        <option value="0" @selected(request('free_access') === '0')>Não</option>
                                    </select>
                                </div>
                            </x-slot>
                        </x-admin.filter-bar>
                    </div>

                    @if($articles->count() > 0)
                        <div class="mb-8 pb-4 border-b-2 border-red-800">
                            <h2 class="text-2xl font-bold text-gray-900 font-serif">
                                @if(request()->filled('search') || request()->filled('category') || request()->filled('free_access'))
                                    Resultados da Busca
                                @else
                                    Todas as Notícias
                                @endif
                                <span class="text-lg font-normal text-gray-600">({{ $articles->total() }})</span>
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
                            @foreach($articles as $article)
                                <x-article-card :article="$article" />
                            @endforeach
                        </div>

                        {{-- Paginação --}}
                        <div class="mt-8">
                            {{ $articles->links() }}
                        </div>
                    @else
                        <div class="text-center py-16">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-xl font-bold text-gray-900 mb-2 font-serif">Nenhuma notícia encontrada</h3>
                            <p class="text-gray-600 mb-6">
                                @if(request()->filled('search') || request()->filled('category') || request()->filled('free_access'))
                                    Não encontramos notícias com os filtros selecionados. Tente alterar sua busca.
                                @else
                                    Ainda não há notícias publicadas.
                                @endif
                            </p>
                            @if(request()->filled('search') || request()->filled('category') || request()->filled('free_access'))
                                <a href="{{ route('articles.index') }}" class="inline-block bg-red-800 text-white px-6 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                                    Ver Todas as Notícias
                                </a>
                            @else
                                <a href="{{ route('home') }}" class="inline-block bg-red-800 text-white px-6 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                                    Voltar para a página inicial
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
@endsection


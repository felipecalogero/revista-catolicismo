@extends('layouts.app')

@section('title', 'Todas as Notícias - Revista Catolicismo')

@section('content')
<section class="bg-white py-12 border-b border-gray-200">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="mb-8">
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
        <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <form action="{{ route('articles.index') }}" method="GET" class="space-y-4 md:space-y-0 md:flex md:gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        Buscar Notícia
                    </label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Digite o título ou conteúdo da notícia..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
                    >
                </div>
                <div class="md:w-64">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Filtrar por Categoria
                    </label>
                    <select 
                        id="category" 
                        name="category"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
                    >
                        <option value="">Todas as categorias</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button 
                        type="submit"
                        class="bg-red-800 text-white px-6 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium whitespace-nowrap"
                    >
                        Buscar
                    </button>
                    @if(request('search') || request('category'))
                        <a 
                            href="{{ route('articles.index') }}"
                            class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors font-medium whitespace-nowrap"
                        >
                            Limpar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</section>

<section class="bg-gray-50 py-16">
    <div class="container mx-auto px-4 lg:px-8">
        @if($articles->count() > 0)
            <div class="mb-8 pb-4 border-b-2 border-red-800">
                <h2 class="text-2xl font-bold text-gray-900 font-serif">
                    @if(request('search') || request('category'))
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
                    @if(request('search') || request('category'))
                        Não encontramos notícias com os filtros selecionados. Tente alterar sua busca.
                    @else
                        Ainda não há notícias publicadas.
                    @endif
                </p>
                @if(request('search') || request('category'))
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
</section>
@endsection


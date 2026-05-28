@extends('layouts.app')

@section('title', 'Página ' . $page->label . ' — ' . $edition->title . ' - Revista Catolicismo')

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6] py-8">
    <div class="absolute inset-0 bg-textura" aria-hidden="true"></div>
    <div class="relative z-10 container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
            {{-- Breadcrumb / Header --}}
            <div class="mb-6 pb-4 border-b border-gray-200 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">{{ $edition->title }}</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 font-serif">Página {{ $page->label }}</h1>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('editions.show', $edition->slug) }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium text-sm">
                        ← Voltar à edição
                    </a>
                    <a href="{{ route('editions.magazine', $edition->slug) }}" class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium text-sm">
                        Modo revista
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Imagem da Página --}}
                <div class="lg:col-span-2">
                    <div class="bg-gray-50 rounded p-4 flex items-center justify-center">
                        @if($page->image_url)
                            <a href="{{ $page->image_url }}" target="_blank" rel="noopener" class="block w-full">
                                <img
                                    src="{{ $page->image_url }}"
                                    alt="Página {{ $page->label }} de {{ $edition->title }}"
                                    class="w-full h-auto rounded shadow-lg border border-gray-300"
                                >
                            </a>
                            <p class="sr-only">Clique para abrir a imagem em tamanho original.</p>
                        @else
                            <p class="text-gray-500 py-8">Imagem indisponível.</p>
                        @endif
                    </div>
                    <p class="text-center text-xs text-gray-500 mt-2">Clique na imagem para ampliá-la em nova aba.</p>

                    {{-- Navegação anterior/próxima --}}
                    <div class="mt-6 flex items-center justify-between">
                        @if($prevPage)
                            <a href="{{ route('editions.page', [$edition->slug, $prevPage->label]) }}" class="text-red-800 hover:text-red-900 font-medium text-sm flex items-center gap-1">
                                ← {{ $prevPage->label }}
                            </a>
                        @else
                            <span></span>
                        @endif
                        @if($nextPage)
                            <a href="{{ route('editions.page', [$edition->slug, $nextPage->label]) }}" class="text-red-800 hover:text-red-900 font-medium text-sm flex items-center gap-1">
                                {{ $nextPage->label }} →
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Sidebar com artigos e sumário --}}
                <aside class="space-y-6">
                    @if($articlesOnPage->isNotEmpty())
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h2 class="text-lg font-bold text-gray-900 font-serif mb-3 pb-2 border-b-2 border-red-800">Nesta página</h2>
                            <ul class="space-y-2">
                                @foreach($articlesOnPage as $article)
                                    <li>
                                        <a href="{{ route('editions.article', [$edition->slug, $article->slug]) }}" class="text-sm text-gray-800 hover:text-red-800 transition-colors leading-snug block">
                                            {{ $article->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($edition->table_of_contents)
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h2 class="text-lg font-bold text-gray-900 font-serif mb-3 pb-2 border-b-2 border-red-800">Sumário da edição</h2>
                            <div class="prose prose-sm max-w-none text-gray-800 sumario-legado">
                                {!! $edition->table_of_contents !!}
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</div>
@endsection

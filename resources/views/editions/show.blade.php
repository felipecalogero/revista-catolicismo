@extends('layouts.app')

@section('title', $edition->title . ' - Revista Catolicismo')

@php
    $hasPages = $edition->pages->isNotEmpty();
    $hasArticles = $edition->articles->isNotEmpty();
    $articlesByPage = $hasArticles ? $edition->articles->groupBy('page_label') : collect();
    $canViewContent = $hasFullAccess;
@endphp

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6] py-12">
    <div class="absolute inset-0 bg-textura" aria-hidden="true"></div>
    <div class="relative z-10 container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8 md:p-12">
        {{-- Cabeçalho da Edição --}}
        <div class="mb-8 pb-6 border-b-2 border-red-800">
            <div class="flex flex-col md:flex-row gap-8">
                {{-- Imagem da Capa --}}
                <div class="flex-shrink-0">
                    @if($edition->cover_image_url)
                        <img
                            src="{{ $edition->cover_image_url }}"
                            alt="{{ $edition->title }}"
                            class="w-full md:w-64 h-auto rounded-lg shadow-lg"
                        >
                    @endif
                </div>

                {{-- Informações --}}
                <div class="flex-1">
                    @if($edition->is_legacy)
                        <span class="inline-block bg-amber-700 text-white text-xs font-bold uppercase tracking-wide px-2 py-1 rounded mb-3">Acervo histórico</span>
                    @endif
                    <h1 class="text-4xl font-bold text-gray-900 font-serif mb-4">
                        {{ $edition->title }}
                    </h1>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                        @if($edition->release_date)
                            <span>Edição de {{ $edition->release_date->format('m/Y') }}</span>
                        @elseif($edition->published_at)
                            <span>Publicada em {{ $edition->published_at->format('d/m/Y') }}</span>
                        @endif
                        @if($hasPages)
                            <span>•</span>
                            <span>{{ $edition->pages->count() }} páginas</span>
                        @endif
                        @if($hasArticles)
                            <span>•</span>
                            <span>{{ $edition->articles->count() }} matérias</span>
                        @endif
                    </div>
                    <div class="text-lg text-gray-700 leading-relaxed mb-6">
                        {!! $edition->description !!}
                    </div>

                    {{-- Botões de Ação --}}
                    @if($canViewContent)
                        <div class="flex flex-col sm:flex-row gap-3">
                            @if($hasPages || $edition->pdf_file)
                                <a
                                    href="{{ route('editions.magazine', $edition->slug) }}"
                                    class="inline-block bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium text-center"
                                >
                                    Visualizar Revista
                                </a>
                            @endif
                            @if($canDownload)
                                <a
                                    href="{{ route('editions.download', $edition->slug) }}"
                                    class="inline-block bg-white text-red-800 border-2 border-red-800 px-8 py-3 rounded-lg hover:bg-red-50 transition-colors font-medium text-center"
                                >
                                    Baixar PDF
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bloqueio de acesso (visitante ou não-assinante) --}}
        @if(!$canViewContent)
            <div class="mb-10">
                @if($requiresLoginOnly)
                    <div class="bg-gradient-to-b from-white via-white to-gray-50 rounded-lg p-8 text-center border border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3 font-serif">Acesso Gratuito Disponível</h3>
                        <p class="text-gray-600 mb-6">Esta edição está disponível gratuitamente para todos os usuários cadastrados. Faça login para visualizar e baixar o conteúdo.</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('login') }}" class="bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                                Fazer Login
                            </a>
                            <a href="{{ route('register') }}" class="bg-white text-red-800 border border-red-800 px-8 py-3 rounded-lg hover:bg-red-50 transition-colors font-medium">
                                Criar Conta Grátis
                            </a>
                        </div>
                    </div>
                @else
                    <div class="bg-gradient-to-b from-white via-white to-gray-50 rounded-lg p-8 text-center border border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3 font-serif">Quer ler esta edição completa?</h3>
                        <p class="text-gray-600 mb-6">Assine a Revista Catolicismo e tenha acesso a todas as edições, além de artigos exclusivos e conteúdo premium.</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            @auth
                                <a href="{{ route('subscriptions.plans') }}" class="bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                                    Assinar Agora
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                                    Fazer Login
                                </a>
                                <a href="{{ route('subscriptions.plans') }}" class="bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                                    Assinar Agora
                                </a>
                            @endauth
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Sumário --}}
        @if($edition->table_of_contents)
            <div class="mb-10 border-t border-gray-200 pt-8">
                <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">Sumário</h2>
                <div class="prose prose-lg max-w-none text-gray-800 sumario-legado">
                    {!! $edition->table_of_contents !!}
                </div>
            </div>
        @endif

        {{-- Páginas (somente quando há acesso) --}}
        @if($canViewContent && $hasPages)
            <div class="mb-10 border-t border-gray-200 pt-8">
                <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">Páginas</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($edition->pages as $page)
                        <a href="{{ route('editions.page', [$edition->slug, $page->label]) }}" class="group block border border-gray-200 rounded overflow-hidden hover:shadow-md transition-shadow">
                            <div class="bg-gray-100 overflow-hidden">
                                @if($page->image_url)
                                    <img
                                        src="{{ $page->image_url }}"
                                        alt="Página {{ $page->label }}"
                                        loading="lazy"
                                        class="w-full h-auto group-hover:scale-105 transition-transform duration-300"
                                    >
                                @endif
                            </div>
                            <div class="text-center py-2">
                                <p class="text-xs font-bold text-gray-700">{{ $page->label }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Matérias --}}
        @if($canViewContent && $hasArticles)
            <div class="mb-10 border-t border-gray-200 pt-8">
                <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">Matérias desta edição</h2>
                <div class="space-y-4">
                    @foreach($articlesByPage as $pageLabel => $articles)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="text-sm font-bold uppercase text-red-800 mb-3">Página {{ $pageLabel ?: '—' }}</p>
                            <ul class="space-y-2">
                                @foreach($articles as $article)
                                    <li>
                                        <a href="{{ route('editions.article', [$edition->slug, $article->slug]) }}" class="text-gray-800 hover:text-red-800 transition-colors font-serif">
                                            {{ $article->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Outras Edições --}}
    @if(isset($otherEditions) && count($otherEditions) > 0)
        <section class="py-16 border-t border-gray-200">
            <div class="bg-white rounded-lg shadow-md p-8 md:p-12">
                <div class="mb-10 pb-4 border-b-2 border-red-800">
                    <h2 class="text-3xl font-bold text-gray-900 font-serif mb-2">Outras Edições</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    @foreach($otherEditions as $otherEdition)
                        <div class="group">
                            <a href="{{ route('editions.show', $otherEdition->slug) }}" class="block">
                                <div class="relative overflow-hidden rounded shadow-sm hover:shadow-lg transition-shadow aspect-[3/4]">
                                    @if($otherEdition->cover_image_url)
                                        <img
                                            src="{{ $otherEdition->cover_image_url }}"
                                            alt="{{ $otherEdition->title }}"
                                            loading="lazy"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        >
                                    @else
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Sem Capa</span>
                                        </div>
                                    @endif

                                    {{-- Tag de Acesso --}}
                                    <span class="absolute top-2 left-2 bg-red-800 text-white px-1.5 py-0.5 text-xs font-medium rounded shadow-sm z-50 uppercase">
                                        {{ $otherEdition->canBeAccessedByNonSubscribers() ? 'Grátis' : 'Assinantes' }}
                                    </span>

                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                                        <div class="text-white text-sm font-medium line-clamp-1">{{ $otherEdition->title }}</div>
                                        @if($otherEdition->release_date)
                                            <div class="text-white/80 text-xs">{{ $otherEdition->release_date->format('m/Y') }}</div>
                                        @elseif($otherEdition->published_at)
                                            <div class="text-white/80 text-xs">{{ $otherEdition->published_at->format('d/m/Y') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-10">
                    <a href="{{ route('editions.index') }}" class="inline-flex items-center gap-2 text-red-800 hover:text-red-900 font-medium text-sm border-b border-red-800 hover:border-red-900 transition-colors">
                        Ver todas as edições
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', $article->title . ' — ' . $edition->title . ' - Revista Catolicismo')

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6] py-12">
    <div class="absolute inset-0 bg-textura" aria-hidden="true"></div>
    <div class="relative z-10 container mx-auto px-4 lg:px-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md p-6 md:p-10">
            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-500 mb-4">
                <a href="{{ route('editions.index') }}" class="hover:text-red-800">Edições</a>
                <span class="mx-2">/</span>
                <a href="{{ route('editions.show', $edition->slug) }}" class="hover:text-red-800">{{ $edition->title }}</a>
                @if($article->page_label)
                    <span class="mx-2">/</span>
                    <a href="{{ route('editions.page', [$edition->slug, $article->page_label]) }}" class="hover:text-red-800">Página {{ $article->page_label }}</a>
                @endif
            </nav>

            <header class="mb-8 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 font-serif">{{ $article->title }}</h1>
                <p class="mt-2 text-sm text-gray-500">
                    {{ $edition->title }}
                    @if($edition->release_date)
                        — {{ $edition->release_date->format('m/Y') }}
                    @endif
                </p>
            </header>

            <article class="prose prose-lg max-w-none text-gray-800 sumario-legado">
                {!! $article->body_html !!}
            </article>

            <div class="mt-10 pt-6 border-t border-gray-200 flex items-center justify-between gap-4 flex-wrap">
                @if($prevArticle)
                    <a href="{{ route('editions.article', [$edition->slug, $prevArticle->slug]) }}" class="text-sm text-red-800 hover:text-red-900 font-medium">
                        ← {{ $prevArticle->title }}
                    </a>
                @else
                    <span></span>
                @endif

                <a href="{{ route('editions.show', $edition->slug) }}" class="text-sm text-gray-600 hover:text-red-800 transition-colors">
                    Voltar à edição
                </a>

                @if($nextArticle)
                    <a href="{{ route('editions.article', [$edition->slug, $nextArticle->slug]) }}" class="text-sm text-red-800 hover:text-red-900 font-medium">
                        {{ $nextArticle->title }} →
                    </a>
                @else
                    <span></span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

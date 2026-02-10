@extends('layouts.app')

@section('title', ($category->name ?? 'Categoria') . ' - Revista Catolicismo')

@section('content')
<section class="bg-white py-6 border-b border-gray-200">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="mb-4">
            <nav class="text-sm text-gray-600 mb-3">
                <a href="{{ route('home') }}" class="hover:text-red-800 transition-colors">Início</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">{{ $category->name }}</span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 font-serif mb-3">
                {{ $category->name }}
            </h1>
            @if($category->description)
                <p class="text-base text-gray-600 max-w-3xl">
                    {{ $category->description }}
                </p>
            @endif
        </div>
    </div>
</section>

<section class="bg-gray-50 py-16">
    <div class="container mx-auto px-4 lg:px-8">
        @if($articles->count() > 0)
            <div class="mb-8 pb-4 border-b-2 border-red-800">
                <h2 class="text-2xl font-bold text-gray-900 font-serif">
                    Artigos de {{ $category->name }}
                    <span class="text-lg font-normal text-gray-600">({{ $articles->total() }})</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
                @foreach($articles as $article)
                    @php
                        $categorySlug = $article->categoryRelation ? $article->categoryRelation->slug : \Illuminate\Support\Str::slug($article->category);
                        $articleData = [
                            'title' => $article->title,
                            'excerpt' => $article->description,
                            'image' => $article->image_url ?? $article->image,
                            'category' => $article->category_name,
                            'category_slug' => $categorySlug,
                            'author' => $article->author,
                            'date' => $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y'),
                            'slug' => $article->slug,
                        ];
                    @endphp
                    <x-article-card :article="$articleData" />
                @endforeach
            </div>

            {{-- Paginação --}}
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2 font-serif">Nenhum artigo encontrado</h3>
                <p class="text-gray-600 mb-6">
                    Ainda não há artigos publicados nesta categoria.
                </p>
                <a href="{{ route('home') }}" class="inline-block bg-red-800 text-white px-6 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                    Voltar para a página inicial
                </a>
            </div>
        @endif
    </div>
</section>
@endsection

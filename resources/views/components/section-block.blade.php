@props(['title', 'articles', 'columns' => 3, 'slug' => null])

@php
    $gridClasses = [
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    ];
    $gridClass = $gridClasses[$columns] ?? $gridClasses[3];
@endphp

<div class="container mx-auto px-4 lg:px-8">
    <div class="mb-10 pb-4 border-b-2 border-red-800">
        <h2 class="text-3xl font-bold text-gray-900 font-serif mb-2">{{ $title }}</h2>
        <p class="text-gray-600 text-sm">Últimas publicações sobre {{ strtolower($title) }}</p>
    </div>
    
    <div class="grid {{ $gridClass }} gap-8">
        @foreach($articles as $article)
            <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-100">
                <x-article-card :article="$article" />
            </div>
        @endforeach
    </div>
    
    @if($slug)
        <div class="text-center mt-10">
            <a href="{{ route('categories.show', $slug) }}" class="inline-block text-red-800 hover:text-red-900 font-medium text-sm border-b border-red-800 hover:border-red-900 transition-colors">
                Ver todas as notícias de {{ $title }} →
            </a>
        </div>
    @endif
</div>


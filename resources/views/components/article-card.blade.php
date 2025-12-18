@props(['article', 'size' => 'default'])

@php
    $sizeClasses = [
        'default' => 'flex flex-col h-full',
        'large' => 'flex flex-col h-full',
        'horizontal' => 'flex flex-row gap-3',
    ];
    
    $imageClasses = [
        'default' => 'w-full h-32 object-cover',
        'large' => 'w-full h-48 object-cover',
        'horizontal' => 'w-24 h-24 object-cover flex-shrink-0 rounded',
    ];
    
    $titleClasses = [
        'default' => 'text-sm font-bold text-gray-900 mt-2 mb-1 line-clamp-2 leading-tight',
        'large' => 'text-xl font-bold text-gray-900 mt-3 mb-2',
        'horizontal' => 'text-sm font-bold text-gray-900 mb-1 line-clamp-2 leading-snug',
    ];
    
    $cardClasses = [
        'default' => 'bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all',
        'large' => 'bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all',
        'horizontal' => 'bg-white border border-gray-200 rounded-lg p-2.5 shadow-sm hover:shadow-md transition-all',
    ];
@endphp

<article class="{{ $sizeClasses[$size] }} {{ $cardClasses[$size] }} group cursor-pointer">
    <a href="{{ isset($article['slug']) && isset($article['category_slug']) ? route('articles.show', [$article['category_slug'], $article['slug']]) : '#' }}" class="block h-full flex flex-col">
        <div class="relative overflow-hidden {{ $size === 'horizontal' ? '' : 'rounded-t-lg' }}">
            <img 
                src="{{ $article['image'] ?? 'https://via.placeholder.com/800x600?text=Revista+Catolicismo' }}" 
                alt="{{ $article['title'] ?? '' }}"
                class="{{ $imageClasses[$size] }} transition-transform duration-300 group-hover:scale-105"
            >
            @if(isset($article['category']))
                <span class="absolute top-1.5 left-1.5 bg-red-800 text-white px-1.5 py-0.5 text-xs font-medium rounded">
                    {{ $article['category'] }}
                </span>
            @endif
        </div>
        <div class="{{ $size === 'horizontal' ? 'flex-1 min-w-0 flex flex-col justify-between py-0.5' : 'flex-1 flex flex-col p-2.5' }}">
            <div class="min-w-0 flex-1">
                <h3 class="{{ $titleClasses[$size] }} group-hover:text-red-800 transition-colors font-serif">
                    {{ $article['title'] ?? 'Título do Artigo' }}
                </h3>
                @if(isset($article['excerpt']) && $size !== 'horizontal')
                    <p class="text-gray-600 text-xs line-clamp-2 mt-1 leading-relaxed">
                        {{ $article['excerpt'] }}
                    </p>
                @endif
            </div>
            @if(isset($article['author']) || isset($article['date']))
                <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-1.5 flex-shrink-0">
                    @if(isset($article['author']))
                        <span class="truncate max-w-[120px]">{{ $article['author'] }}</span>
                    @endif
                    @if(isset($article['date']))
                        <span class="text-gray-400 flex-shrink-0">•</span>
                        <time class="whitespace-nowrap flex-shrink-0">{{ $article['date'] }}</time>
                    @endif
                </div>
            @endif
        </div>
    </a>
</article>


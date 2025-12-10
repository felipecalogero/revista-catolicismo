@props(['article', 'size' => 'default'])

@php
    $sizeClasses = [
        'default' => 'flex flex-col',
        'large' => 'flex flex-col',
        'horizontal' => 'flex flex-row gap-4',
    ];
    
    $imageClasses = [
        'default' => 'w-full h-48 object-cover',
        'large' => 'w-full h-96 object-cover',
        'horizontal' => 'w-32 h-32 object-cover flex-shrink-0',
    ];
    
    $titleClasses = [
        'default' => 'text-lg font-bold text-gray-900 mt-3 mb-2 line-clamp-2',
        'large' => 'text-3xl font-bold text-gray-900 mt-4 mb-3',
        'horizontal' => 'text-lg font-bold text-gray-900 mb-2 line-clamp-2',
    ];
@endphp

<article class="{{ $sizeClasses[$size] }} group cursor-pointer">
    <a href="#" class="block">
        <div class="relative overflow-hidden {{ $size === 'horizontal' ? '' : 'rounded' }}">
            <img 
                src="{{ $article['image'] ?? 'https://via.placeholder.com/800x600?text=Revista+Catolicismo' }}" 
                alt="{{ $article['title'] ?? '' }}"
                class="{{ $imageClasses[$size] }} transition-transform duration-300 group-hover:scale-105"
            >
            @if(isset($article['category']))
                <span class="absolute top-3 left-3 bg-red-800 text-white px-3 py-1 text-xs font-medium rounded">
                    {{ $article['category'] }}
                </span>
            @endif
        </div>
        <div class="{{ $size === 'horizontal' ? 'flex-1' : '' }}">
            <h3 class="{{ $titleClasses[$size] }} group-hover:text-red-800 transition-colors font-serif">
                {{ $article['title'] ?? 'Título do Artigo' }}
            </h3>
            @if(isset($article['excerpt']) && $size !== 'horizontal')
                <p class="text-gray-600 text-sm line-clamp-3 mb-2">
                    {{ $article['excerpt'] }}
                </p>
            @endif
            @if(isset($article['author']) || isset($article['date']))
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    @if(isset($article['author']))
                        <span>{{ $article['author'] }}</span>
                    @endif
                    @if(isset($article['date']))
                        <span>•</span>
                        <time>{{ $article['date'] }}</time>
                    @endif
                </div>
            @endif
        </div>
    </a>
</article>


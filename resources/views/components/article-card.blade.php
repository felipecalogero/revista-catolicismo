@props(['article', 'size' => 'default'])

@php
    $title = data_get($article, 'title');
    $excerpt = data_get($article, 'excerpt') ?? data_get($article, 'description');
    $categoryLabel = data_get($article, 'category') ?? data_get($article, 'category_name') ?? data_get($article, 'categoryRelation.name');

    $rawImage = data_get($article, 'image_url') ?? data_get($article, 'image');
    if ($rawImage && !preg_match('/^https?:\/\//i', $rawImage)) {
        if (str_starts_with($rawImage, '/')) {
            $rawImage = url($rawImage);
        } elseif (str_starts_with($rawImage, 'storage/')) {
            $rawImage = url('/' . $rawImage);
        } else {
            $rawImage = \Illuminate\Support\Facades\Storage::url($rawImage);
        }
    }
    $imageUrl = $rawImage ?? 'https://via.placeholder.com/800x600?text=Revista+Catolicismo';

    $slug = data_get($article, 'slug');
    $categorySlug = data_get($article, 'category_slug')
        ?? data_get($article, 'categoryRelation.slug')
        ?? ($categoryLabel ? \Illuminate\Support\Str::slug($categoryLabel) : null);
    $href = ($slug && $categorySlug) ? route('articles.show', [$categorySlug, $slug]) : '#';

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
    <a href="{{ $href }}" class="block h-full flex flex-col">
        <div class="relative overflow-hidden {{ $size === 'horizontal' ? '' : 'rounded-t-lg' }}">
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $title ?? '' }}"
                class="{{ $imageClasses[$size] }} transition-transform duration-300 group-hover:scale-105"
            >
            @if($categoryLabel)
                <span class="absolute top-1.5 left-1.5 bg-red-800 text-white px-1.5 py-0.5 text-xs font-medium rounded">
                    {{ $categoryLabel }}
                </span>
            @endif
        </div>
        <div class="{{ $size === 'horizontal' ? 'flex-1 min-w-0 flex flex-col justify-between py-0.5' : 'flex-1 flex flex-col p-2.5' }}">
            <div class="min-w-0 flex-1">
                <h3 class="{{ $titleClasses[$size] }} group-hover:text-red-800 transition-colors font-serif">
                    {{ $title ?? 'Título do Artigo' }}
                </h3>
                @if($excerpt && $size !== 'horizontal')
                    <p class="text-gray-600 text-xs line-clamp-2 mt-1 leading-relaxed">
                        {{ $excerpt }}
                    </p>
                @endif
            </div>
            @if(data_get($article, 'author') || data_get($article, 'date'))
                <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-1.5 flex-shrink-0">
                    @if(data_get($article, 'author'))
                        <span class="truncate max-w-[120px]">{{ data_get($article, 'author') }}</span>
                    @endif
                    @if(data_get($article, 'date'))
                        <span class="text-gray-400 flex-shrink-0">•</span>
                        <time class="whitespace-nowrap flex-shrink-0">{{ data_get($article, 'date') }}</time>
                    @endif
                </div>
            @endif
        </div>
    </a>
</article>


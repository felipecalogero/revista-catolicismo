@extends('layouts.app')

@section('title', ($article->title ?? 'Artigo') . ' - Revista Catolicismo')

@section('content')
<article class="bg-white">
    {{-- Imagem Principal --}}
    @if($article->image)
        <div class="w-full h-[500px] md:h-[600px] relative overflow-hidden">
            <img
                src="{{ Storage::url($article->image) }}"
                alt="{{ $article->title }}"
                class="w-full h-full object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
        </div>
    @endif

    <div class="container mx-auto px-4 lg:px-8 py-12">
        <div class="max-w-4xl mx-auto">
            {{-- Cabeçalho do Artigo --}}
            <header class="mb-8">
                @if($article->categoryRelation || $article->category)
                    <div class="mb-4">
                        <span class="inline-block bg-red-800 text-white px-3 py-1 text-sm font-medium rounded">
                            {{ $article->categoryRelation ? $article->categoryRelation->name : $article->category }}
                        </span>
                    </div>
                @endif

                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 font-serif mb-4 leading-tight">
                    {{ $article->title }}
                </h1>

                @if($article->description)
                    <div class="text-xl text-gray-600 font-serif mb-6 leading-relaxed">
                        {!! $article->description !!}
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 border-b border-gray-200 pb-6">
                    @if($article->author)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ $article->author }}</span>
                        </div>
                    @endif
                    @if($article->published_at)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <time datetime="{{ $article->published_at->toIso8601String() }}">
                                {{ $article->published_at->format('d/m/Y') }}
                            </time>
                        </div>
                    @endif
                </div>
            </header>

            {{-- Conteúdo do Artigo --}}
            <div class="prose prose-lg max-w-none font-serif">
                @php
                    $content = $article->content;
                    $videoUrl = $article->video_url;

                    // Se houver vídeo, converter URL para embed
                    $embedUrl = null;
                    if ($videoUrl) {
                        if (strpos($videoUrl, 'youtube.com/watch') !== false) {
                            parse_str(parse_url($videoUrl, PHP_URL_QUERY), $params);
                            if (isset($params['v'])) {
                                $embedUrl = 'https://www.youtube.com/embed/' . $params['v'];
                            }
                        } elseif (strpos($videoUrl, 'youtu.be/') !== false) {
                            $videoId = substr(parse_url($videoUrl, PHP_URL_PATH), 1);
                            $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
                        } else {
                            $embedUrl = $videoUrl;
                        }
                    }

                    // Inserir vídeo no meio do conteúdo HTML
                    if ($embedUrl) {
                        // Encontrar o primeiro parágrafo com conteúdo significativo
                        preg_match_all('/<p[^>]*>.*?<\/p>/is', $content, $paragraphMatches);
                        $paragraphs = $paragraphMatches[0];
                        
                        if (count($paragraphs) > 1) {
                            // Inserir vídeo após a metade dos parágrafos
                            $splitIndex = ceil(count($paragraphs) / 2);
                            $firstPart = implode('', array_slice($paragraphs, 0, $splitIndex));
                            $secondPart = implode('', array_slice($paragraphs, $splitIndex));
                            
                            // Encontrar a posição do split no conteúdo original
                            $firstPartEnd = strpos($content, $paragraphs[$splitIndex - 1]) + strlen($paragraphs[$splitIndex - 1]);
                            $contentBefore = substr($content, 0, $firstPartEnd);
                            $contentAfter = substr($content, $firstPartEnd);
                        } else {
                            // Se houver apenas um parágrafo ou nenhum, dividir pela metade do conteúdo
                            $splitPosition = strlen($content) / 2;
                            $contentBefore = substr($content, 0, $splitPosition);
                            $contentAfter = substr($content, $splitPosition);
                        }
                    }
                @endphp

                <div class="text-gray-800 leading-relaxed text-lg quill-content">
                    @if($embedUrl && isset($contentBefore) && isset($contentAfter))
                        {!! $contentBefore !!}
                        <div class="my-12">
                            <div class="relative w-full" style="padding-bottom: 56.25%;">
                                <iframe
                                    class="absolute top-0 left-0 w-full h-full rounded-lg"
                                    src="{{ htmlspecialchars($embedUrl) }}"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>
                        {!! $contentAfter !!}
                    @else
                        {!! $content !!}
                    @endif
                </div>
            </div>

            {{-- Compartilhar --}}
            <div class="mt-12 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 font-serif">Compartilhar</h3>
                <div class="flex gap-4">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" class="flex items-center gap-2 text-blue-600 hover:text-blue-800 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Facebook</span>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($article->title) }}" target="_blank" class="flex items-center gap-2 text-blue-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                        <span>Twitter</span>
                    </a>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($article->title . ' ' . request()->fullUrl()) }}" target="_blank" class="flex items-center gap-2 text-green-600 hover:text-green-800 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>

{{-- Artigos Relacionados --}}
<section class="bg-gray-50 py-16 border-t border-gray-200">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="mb-10 pb-4 border-b-2 border-red-800">
            <h2 class="text-3xl font-bold text-gray-900 font-serif mb-2">Artigos Relacionados</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Aqui você pode adicionar artigos relacionados --}}
            <p class="text-gray-600">Em breve: artigos relacionados</p>
        </div>
    </div>
</section>
@endsection

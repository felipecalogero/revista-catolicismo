@props(['article'])

<section class="bg-white">
    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Hero Principal --}}
            <div class="lg:col-span-2">
                <article class="group cursor-pointer">
                    <a href="#" class="block">
                        <div class="relative overflow-hidden rounded-lg mb-4">
                            <img
                                src="{{ $article['image'] ?? 'https://via.placeholder.com/1200x800?text=Revista+Catolicismo' }}"
                                alt="{{ $article['title'] ?? '' }}"
                                class="w-full h-[500px] object-cover transition-transform duration-300 group-hover:scale-105"
                            >
                            @if(isset($article['category']))
                                <span class="absolute top-4 left-4 bg-red-800 text-white px-4 py-2 text-sm font-medium rounded">
                                    {{ $article['category'] }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4 group-hover:text-red-800 transition-colors font-serif leading-tight">
                            {{ $article['title'] ?? 'Título Principal da Matéria' }}
                        </h1>
                        @if(isset($article['excerpt']))
                            <p class="text-lg text-gray-700 mb-4 leading-relaxed">
                                {{ $article['excerpt'] }}
                            </p>
                        @endif
                        @if(isset($article['author']) || isset($article['date']))
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                @if(isset($article['author']))
                                    <span class="font-medium">{{ $article['author'] }}</span>
                                @endif
                                @if(isset($article['date']))
                                    <span>•</span>
                                    <time>{{ $article['date'] }}</time>
                                @endif
                            </div>
                        @endif
                    </a>
                </article>
            </div>

            {{-- Sidebar com Destaques --}}
            <div class="space-y-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 font-serif border-b border-gray-200 pb-2">
                    Destaques
                </h2>
                @php
                    $sidebarDestaques = [
                        ['title' => 'A Doutrina Social da Igreja no Século XXI', 'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=200&h=200&fit=crop&q=80', 'date' => now()->subDays(0)->format('d/m/Y')],
                        ['title' => 'Tradição e Modernidade na Liturgia', 'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=200&h=200&fit=crop&q=80', 'date' => now()->subDays(1)->format('d/m/Y')],
                        ['title' => 'Arte Sacra: Patrimônio da Humanidade', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=200&h=200&fit=crop&q=80', 'date' => now()->subDays(2)->format('d/m/Y')]
                    ];
                @endphp
                @foreach($sidebarDestaques as $destaque)
                    <article class="group cursor-pointer">
                        <a href="#" class="block">
                            <div class="flex gap-4">
                                <div class="w-24 h-24 flex-shrink-0 rounded overflow-hidden">
                                    <img
                                        src="{{ $destaque['image'] }}"
                                        alt="{{ $destaque['title'] }}"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    >
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-sm font-bold text-gray-900 mb-1 line-clamp-2 group-hover:text-red-800 transition-colors font-serif">
                                        {{ $destaque['title'] }}
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        {{ $destaque['date'] }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

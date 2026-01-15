@extends('layouts.app')

@section('title', 'Revista Catolicismo - Assine e Receba em Casa')
@section('description', 'Assine a Revista Catolicismo e receba mensalmente a revista que defende a tradição católica e o jornalismo de qualidade')

@section('content')
    {{-- Faixa de Assinatura Simples --}}
    <section class="bg-red-800 text-white py-4">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-center md:text-left">
                    <span class="text-lg md:text-xl font-medium">Assine a Revista Catolicismo por apenas</span>
                    <span class="text-2xl md:text-3xl font-bold ml-2">R$ 39,90/mês</span>
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="bg-white/10 text-white px-6 py-2 rounded font-medium hover:bg-white/20 transition-colors whitespace-nowrap border border-white/30">
                                Admin
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="bg-white/10 text-white px-6 py-2 rounded font-medium hover:bg-white/20 transition-colors whitespace-nowrap border border-white/30">
                                Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="bg-white/10 text-white px-6 py-2 rounded font-medium hover:bg-white/20 transition-colors whitespace-nowrap border border-white/30">
                            Entrar
                        </a>
                    @endauth
                    <a href="#" class="bg-white text-red-800 px-6 py-2 rounded font-bold hover:bg-gray-100 transition-colors whitespace-nowrap">
                        ASSINE AGORA
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Slider de Revistas Compacto --}}
    <x-revista-slider :revistas="$revistas ?? []" />

    {{-- Grid de Destaques --}}
    <section class="bg-white py-16 border-t border-gray-200">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="mb-10">
                <h2 class="text-3xl font-bold text-gray-900 font-serif mb-3">Destaques</h2>
                <div class="w-20 h-1 bg-red-800"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($destaques ?? [] as $destaque)
                    <x-article-card :article="$destaque" />
                @empty
                    <p class="col-span-full text-center text-gray-500 py-8">Nenhum destaque disponível no momento.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Conteúdo Principal - Duas Colunas --}}
    <section class="bg-gray-50 py-16 border-t border-gray-200">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                {{-- Coluna Esquerda - Artigos Recentes --}}
                <div class="lg:col-span-2">
                    <div class="mb-8 pb-4 border-b-2 border-red-800">
                        <h2 class="text-3xl font-bold text-gray-900 font-serif mb-2">Últimas Notícias</h2>
                        <p class="text-gray-600 text-sm">As principais notícias e análises do momento</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                        @forelse($noticias ?? [] as $index => $noticia)
                            <x-article-card
                                :article="$noticia"
                            />
                        @empty
                            <p class="col-span-full text-center text-gray-500 py-8">Nenhuma notícia disponível no momento.</p>
                        @endforelse
                    </div>

                    {{-- Botão Ver Mais --}}
                    <div class="text-center pt-2">
                        <a href="{{ route('articles.index') }}" class="inline-block bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                            Ver Todas as Notícias
                        </a>
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="space-y-8">
                    {{-- Newsletter --}}
                    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                        <div class="mb-4 pb-3 border-b border-gray-200">
                            <h3 class="text-xl font-bold text-gray-900 mb-1 font-serif">Newsletter</h3>
                            <p class="text-sm text-gray-600">
                                Receba nossos artigos exclusivos e notícias diretamente em seu e-mail.
                            </p>
                        </div>
                        <form class="space-y-3">
                            <input
                                type="email"
                                placeholder="Seu e-mail"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent text-sm"
                            >
                            <button
                                type="submit"
                                class="w-full bg-red-800 text-white px-4 py-3 rounded-lg hover:bg-red-900 transition-colors text-sm font-medium"
                            >
                                Assinar Newsletter
                            </button>
                        </form>
                    </div>

                    {{-- Mais Lidas --}}
                    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                        <div class="mb-6 pb-3 border-b-2 border-red-800">
                            <h3 class="text-xl font-bold text-gray-900 font-serif">
                                Mais Lidas
                            </h3>
                        </div>
                        <div class="space-y-5">
                            @forelse($maisLidas ?? [] as $index => $artigo)
                                <article class="group cursor-pointer pb-5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                    <a href="{{ isset($artigo['slug']) && isset($artigo['category_slug']) ? route('articles.show', [$artigo['category_slug'], $artigo['slug']]) : '#' }}" class="block">
                                        <div class="flex gap-4">
                                            <div class="w-24 h-24 flex-shrink-0 rounded-lg overflow-hidden shadow-sm">
                                                <img
                                                    src="{{ $artigo['image'] ?? 'https://via.placeholder.com/150?text=Revista+Catolicismo' }}"
                                                    alt="{{ $artigo['title'] ?? '' }}"
                                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                >
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-xs font-bold text-red-800 mb-1">{{ $index + 1 }}º</div>
                                                <h4 class="text-sm font-bold text-gray-900 mb-2 line-clamp-3 group-hover:text-red-800 transition-colors font-serif leading-snug">
                                                    {{ $artigo['title'] ?? 'Título do Artigo' }}
                                                </h4>
                                                <p class="text-xs text-gray-500">
                                                    {{ $artigo['date'] ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @empty
                                <p class="text-center text-gray-500 py-4 text-sm">Nenhum artigo disponível no momento.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Espaço para Anúncios --}}
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-8 rounded-lg border-2 border-dashed border-gray-300 text-center">
                        <p class="text-sm text-gray-500 font-medium">Espaço Publicitário</p>
                        <p class="text-xs text-gray-400 mt-2">300x250</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- Seções por Categoria - 3 Categorias Mais Visitadas --}}
    @if(isset($categoriasMaisVisitadas) && count($categoriasMaisVisitadas) > 0)
        @foreach($categoriasMaisVisitadas as $index => $categoria)
        <section class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} py-16 border-t border-gray-200">
            <x-section-block
                :title="$categoria['name']"
                :articles="$categoria['articles']"
                :columns="3"
                :slug="$categoria['slug']"
            />
        </section>
        @endforeach
    @endif
@endsection


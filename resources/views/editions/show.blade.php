@extends('layouts.app')

@section('title', $edition->title . ' - Revista Catolicismo')

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6] py-12">
    <img
        src="{{ asset('img/textura.jpeg') }}"
        alt=""
        class="absolute inset-0 w-full h-full object-cover"
    >
    <div class="relative z-10 container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8 md:p-12">
        {{-- Cabeçalho da Edição --}}
        <div class="mb-8 pb-6 border-b-2 border-red-800">
            <div class="flex flex-col md:flex-row gap-8">
                {{-- Imagem da Capa --}}
                <div class="flex-shrink-0">
                    @if($edition->cover_image)
                        <img
                            src="{{ Storage::url($edition->cover_image) }}"
                            alt="{{ $edition->title }}"
                            class="w-full md:w-64 h-auto rounded-lg shadow-lg"
                        >
                    @endif
                </div>

                {{-- Informações --}}
                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-gray-900 font-serif mb-4">
                        {{ $edition->title }}
                    </h1>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                        @if($edition->published_at)
                            <span>Publicada em {{ $edition->published_at->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    <div class="text-lg text-gray-700 leading-relaxed mb-6">
                        {!! $edition->description !!}
                    </div>

                    {{-- Botões de Ação --}}
                    @if($canDownload)
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a
                                href="{{ route('editions.magazine', $edition->slug) }}"
                                class="inline-block bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium text-center"
                            >
                                Visualizar Revista
                            </a>
                        <a
                            href="{{ route('editions.download', $edition->slug) }}"
                                class="inline-block bg-white text-red-800 border-2 border-red-800 px-8 py-3 rounded-lg hover:bg-red-50 transition-colors font-medium text-center"
                        >
                            Baixar PDF
                        </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Conteúdo Adicional --}}
        <div class="prose prose-lg max-w-none">
            @if($hasFullAccess)
                <p class="text-gray-600">
                    Esta edição está disponível para download em formato PDF. Clique no botão acima para baixar.
                </p>
            @elseif($requiresLoginOnly)
                <div class="bg-gradient-to-b from-white via-white to-gray-50 rounded-lg p-8 text-center border border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3 font-serif">Acesso Gratuito Disponível</h3>
                    <p class="text-gray-600 mb-6">Esta edição está disponível gratuitamente para todos os usuários cadastrados. Faça login para visualizar e baixar o PDF.</p>
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
                    <p class="text-gray-600 mb-6">Assine a Revista Catolicismo e tenha acesso a todas as edições em PDF, além de artigos exclusivos e conteúdo premium.</p>
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
                                    @if($otherEdition->cover_image)
                                        <img
                                            src="{{ Storage::url($otherEdition->cover_image) }}"
                                            alt="{{ $otherEdition->title }}"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        >
                                    @else
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Sem Capa</span>
                                        </div>
                                    @endif
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                                        <div class="text-white text-sm font-medium line-clamp-1">{{ $otherEdition->title }}</div>
                                        @if($otherEdition->release_date)
                                            <div class="text-white/80 text-xs">{{ $otherEdition->release_date->format('M Y') }}</div>
                                        @elseif($otherEdition->published_at)
                                            <div class="text-white/80 text-xs">{{ $otherEdition->published_at->format('m/Y') }}</div>
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

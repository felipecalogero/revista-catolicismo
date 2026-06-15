@extends('layouts.app')

@section('title', 'Todas as Edições - Revista Catolicismo')

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6]">
    <div class="absolute inset-0 bg-textura" aria-hidden="true"></div>
    <div class="relative z-10 container mx-auto px-4 lg:px-8 py-12">
        <div class="bg-white rounded-lg p-6 md:p-8">
            {{-- Header da Página --}}
            <div class="mb-8 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 font-serif mb-2">
                    Todas as Edições
                </h1>
                <p class="text-lg text-gray-600 font-serif">
                    Edições atuais e acervo histórico (1951–presente) reunidos no mesmo lugar.
                </p>
            </div>

            {{-- Informativo de Acesso --}}
            <div class="bg-white/50 border-b border-gray-200 py-4 mb-8">
                <div class="flex items-center gap-3 text-gray-700">
                    <svg class="w-5 h-5 text-red-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm md:text-base italic">
                        O acesso às edições mais recentes é exclusivo para assinantes; as demais edições podem ser acessadas gratuitamente mediante cadastro.
                    </p>
                </div>
            </div>

            <div class="mb-10 rounded-lg border border-gray-200 bg-white p-4 md:p-6">
                <p class="mb-3 text-sm font-medium text-gray-700">Buscar edições</p>
                <x-admin.filter-bar
                    :formAction="route('editions.index')"
                    modalId="publicEditionsFilterModal"
                    searchPlaceholder="Título, descrição ou conteúdo das matérias…"
                    :clearUrl="route('editions.index')"
                >
                    <x-slot name="modal">
                        <div>
                            <label for="edition_filter_access" class="mb-1 block text-sm font-medium text-gray-700">Tipo de acesso</label>
                            <select id="edition_filter_access" name="access" class="w-full rounded-lg border border-gray-300 py-2 text-sm">
                                <option value="">Todas</option>
                                <option value="free" @selected(request('access') === 'free')>Grátis (cadastro)</option>
                                <option value="subscribers" @selected(request('access') === 'subscribers')>Somente assinantes</option>
                            </select>
                        </div>
                        <div>
                            <label for="edition_filter_source" class="mb-1 block text-sm font-medium text-gray-700">Origem da edição</label>
                            <select id="edition_filter_source" name="source" class="w-full rounded-lg border border-gray-300 py-2 text-sm">
                                <option value="">Todas</option>
                                <option value="nova" @selected(request('source') === 'nova')>Edições atuais</option>
                                <option value="acervo" @selected(request('source') === 'acervo')>Acervo histórico</option>
                            </select>
                        </div>
                        <div>
                            <label for="edition_filter_year" class="mb-1 block text-sm font-medium text-gray-700">Ano (lançamento ou publicação)</label>
                            <select id="edition_filter_year" name="year" class="w-full rounded-lg border border-gray-300 py-2 text-sm">
                                <option value="">Todos</option>
                                @foreach($editionYears as $y)
                                    <option value="{{ $y }}" @selected((string) request('year') === (string) $y)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </x-slot>
                </x-admin.filter-bar>
            </div>

            @if(request()->filled('search') || request()->filled('access') || request()->filled('year') || request()->filled('source'))
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-xl font-bold text-gray-900 font-serif">
                        Resultados
                        <span class="text-base font-normal text-gray-600">({{ $editions->total() }})</span>
                    </h2>
                </div>
            @endif

            {{-- Grid de Edições --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6 mb-12">
                @forelse($editions as $edition)
                    <div class="group">
                        <a href="{{ route('editions.show', $edition->slug) }}" class="block">
                            <div class="relative overflow-hidden rounded shadow-md hover:shadow-xl transition-all duration-300 aspect-[3/4]">
                                @if($edition->cover_image_url)
                                    <img
                                        src="{{ $edition->cover_image_url }}"
                                        alt="{{ $edition->title }}"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                        <span class="text-gray-400 text-sm">Sem Capa</span>
                                    </div>
                                @endif

                                {{-- Tag de Acesso --}}
                                <span class="absolute top-2 left-2 bg-red-800 text-white px-1.5 py-0.5 text-xs font-medium rounded shadow-sm z-50 uppercase">
                                    {{ $edition->canBeAccessedByNonSubscribers() ? 'Grátis' : 'Assinantes' }}
                                </span>

                                @if($edition->is_legacy)
                                    <span class="absolute top-2 right-2 bg-amber-700 text-white px-1.5 py-0.5 text-xs font-medium rounded shadow-sm z-50 uppercase">
                                        Acervo
                                    </span>
                                @endif

                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                                    <div class="text-white text-sm font-bold mb-1 line-clamp-1">{{ $edition->title }}</div>
                                    @if($edition->release_date)
                                        <div class="text-white/80 text-xs">{{ $edition->release_date->format('m/Y') }}</div>
                                    @elseif($edition->published_at)
                                        <div class="text-white/80 text-xs">{{ $edition->published_at->format('d/m/Y') }}</div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                        <p class="text-gray-500 font-serif text-lg mb-4">Nenhuma edição encontrada com os filtros atuais.</p>
                        @if(request()->filled('search') || request()->filled('access') || request()->filled('year') || request()->filled('source'))
                            <a href="{{ route('editions.index') }}" class="inline-block rounded-lg bg-red-800 px-6 py-3 text-sm font-medium text-white hover:bg-red-900">Ver todas as edições</a>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- Paginação --}}
            <div class="mb-16">
                {{ $editions->links() }}
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 md:p-10 text-center relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-2xl md:text-3xl font-bold font-serif mb-3 text-gray-900">Navegar pelo acervo histórico</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto mb-6">
                        Veja todas as edições agrupadas por década e ano, desde 1951.
                    </p>
                    <a
                        href="{{ route('editions.gallery') }}"
                        class="inline-flex items-center gap-2 bg-red-800 text-white px-6 py-3 rounded-lg font-bold hover:bg-red-900 transition-colors shadow-md text-sm"
                    >
                        Ir para galeria por década
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

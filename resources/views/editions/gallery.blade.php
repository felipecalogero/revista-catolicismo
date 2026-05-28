@extends('layouts.app')

@section('title', 'Galeria do acervo - Revista Catolicismo')

@php
    $months = [
        1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr',
        5 => 'mai', 6 => 'jun', 7 => 'jul', 8 => 'ago',
        9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez',
    ];
@endphp

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6]">
    <div class="absolute inset-0 bg-textura" aria-hidden="true"></div>
    <div class="relative z-10 container mx-auto px-4 lg:px-8 py-12">
        <div class="bg-white rounded-lg p-6 md:p-8">
            <div class="mb-8 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 font-serif mb-2">Galeria do acervo</h1>
                <p class="text-lg text-gray-600 font-serif">
                    Edições históricas organizadas por década e ano (1951–presente).
                </p>
            </div>

            {{-- Seletor de Décadas --}}
            @if(count($availableDecades) > 0)
                <div class="mb-10">
                    <h2 class="text-sm font-medium text-gray-700 mb-3">Selecione uma década</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableDecades as $decade)
                            @php $isSelected = (int) $decade === (int) $selectedDecade; @endphp
                            <a
                                href="{{ route('editions.gallery', ['decade' => $decade]) }}"
                                class="px-4 py-2 rounded-lg text-sm font-bold border transition-colors {{ $isSelected ? 'bg-red-800 text-white border-red-800 shadow-md' : 'bg-white text-red-800 border-red-800 hover:bg-red-50' }}"
                            >
                                {{ $decade }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($selectedDecade !== null && isset($byDecade[$selectedDecade]))
                @foreach($byDecade[$selectedDecade] as $year => $monthsMap)
                    <section class="mb-12">
                        <div class="mb-4 flex items-center gap-3">
                            <h3 class="text-2xl font-bold text-gray-900 font-serif">{{ $year }}</h3>
                            <span class="text-xs uppercase tracking-wide text-gray-500">{{ count($monthsMap) }} edições</span>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                            @for($m = 1; $m <= 12; $m++)
                                @if(isset($monthsMap[$m]))
                                    @php $edition = $monthsMap[$m]; @endphp
                                    <a
                                        href="{{ route('editions.show', $edition->slug) }}"
                                        class="group block border border-gray-200 rounded overflow-hidden hover:shadow-md transition-shadow bg-white"
                                    >
                                        <div class="aspect-[3/4] bg-gray-100 overflow-hidden">
                                            @if($edition->cover_image_url)
                                                <img
                                                    src="{{ $edition->cover_image_url }}"
                                                    alt="{{ $edition->title }}"
                                                    loading="lazy"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                >
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">Sem Capa</div>
                                            @endif
                                        </div>
                                        <div class="text-center py-2 px-1">
                                            <p class="text-xs font-bold text-gray-800 leading-tight">
                                                Nº {{ $edition->legacy_issue_number ?? '' }}
                                            </p>
                                            <p class="text-[11px] text-gray-500 uppercase">{{ $months[$m] }}/{{ $year }}</p>
                                        </div>
                                    </a>
                                @else
                                    <div class="aspect-[3/4] flex flex-col items-center justify-center border border-dashed border-gray-200 rounded text-gray-300 text-xs">
                                        <span class="uppercase">{{ $months[$m] }}/{{ $year }}</span>
                                        <span class="mt-1 text-[10px]">sem edição</span>
                                    </div>
                                @endif
                            @endfor
                        </div>
                    </section>
                @endforeach
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <p class="text-gray-500 font-serif text-lg">Nenhuma edição do acervo encontrada.</p>
                </div>
            @endif

            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 md:p-10 text-center mt-8">
                <h2 class="text-xl md:text-2xl font-bold font-serif mb-3 text-gray-900">Procurando uma edição específica?</h2>
                <p class="text-gray-600 max-w-2xl mx-auto mb-6 text-sm">
                    Use a busca avançada para filtrar por título, ano ou tipo de acesso.
                </p>
                <a
                    href="{{ route('editions.index') }}"
                    class="inline-flex items-center gap-2 bg-red-800 text-white px-6 py-3 rounded-lg font-bold hover:bg-red-900 transition-colors shadow-md text-sm"
                >
                    Ir para a busca de edições
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

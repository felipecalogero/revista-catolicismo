@extends('layouts.app')

@section('title')
    @if($q !== '')
        {{ $q }} – Busca – Revista Catolicismo
    @else
        Buscar no site – Revista Catolicismo
    @endif
@endsection

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6]">
    <div class="absolute inset-0 bg-textura" aria-hidden="true"></div>
    <div class="relative z-10 container mx-auto px-4 lg:px-8 py-12">
        <div class="bg-white rounded-lg p-6 md:p-8">
            <div class="mb-8 pb-4 border-b-2 border-red-800">
                <nav class="mb-3 text-sm text-gray-600">
                    <a href="{{ route('home') }}" class="hover:text-red-800 transition-colors">Início</a>
                    <span class="mx-2">/</span>
                    <span class="font-medium text-gray-900">Busca no site</span>
                </nav>
                <h1 class="font-serif text-3xl font-bold text-gray-900 md:text-4xl">
                    @if($q !== '')
                        Resultados para "{{ $q }}"
                    @else
                        Buscar no site
                    @endif
                </h1>
                @if($q === '')
                    <p class="mt-2 text-gray-600">Matérias, edições da revista e acervo histórico.</p>
                @endif
            </div>

            <div class="mb-8 rounded-lg border border-gray-200 bg-gray-50/80 p-4 md:p-6">
                <x-site-search-form :q="$q" size="large" :showHint="$q === ''" />
            </div>

            @if($q !== '' && $results)
                @php
                    $counts = $results['counts'];
                    $tabs = [
                        'all' => ['label' => 'Tudo', 'count' => $counts['total']],
                        'articles' => ['label' => 'Matérias', 'count' => $counts['articles']],
                        'editions' => ['label' => 'Edições', 'count' => $counts['editions']],
                        'archive' => ['label' => 'Acervo', 'count' => $counts['archive']],
                    ];
                    $isPaginated = $type !== 'all';
                    $breakdown = collect([
                        $counts['articles'] > 0 ? $counts['articles'].' matérias' : null,
                        $counts['editions'] > 0 ? $counts['editions'].' edições' : null,
                        $counts['archive'] > 0 ? $counts['archive'].' no acervo' : null,
                    ])->filter()->implode(' · ');
                    $searchSections = collect([
                        [
                            'show' => $type === 'all' || $type === 'articles',
                            'title' => 'Matérias',
                            'items' => $results['articles'],
                            'total' => $counts['articles'],
                            'moreUrl' => $counts['articles'] > 6 ? route('search.index', ['q' => $q, 'type' => 'articles']) : null,
                            'showSection' => $type === 'all' ? $counts['articles'] > 0 : true,
                        ],
                        [
                            'show' => $type === 'all' || $type === 'editions',
                            'title' => 'Edições',
                            'items' => $results['editions'],
                            'total' => $counts['editions'],
                            'moreUrl' => $counts['editions'] > 6 ? route('search.index', ['q' => $q, 'type' => 'editions']) : null,
                            'showSection' => $type === 'all' ? $counts['editions'] > 0 : true,
                        ],
                        [
                            'show' => $type === 'all' || $type === 'archive',
                            'title' => 'Acervo histórico',
                            'items' => $results['archive'],
                            'total' => $counts['archive'],
                            'moreUrl' => $counts['archive'] > 6 ? route('search.index', ['q' => $q, 'type' => 'archive']) : null,
                            'showSection' => $type === 'all' ? $counts['archive'] > 0 : true,
                        ],
                    ])->filter(fn ($section) => $section['show'] && $section['showSection'])->values();
                @endphp

                @if($counts['total'] > 0)
                    <div class="mb-6 text-sm text-gray-600">
                        <strong class="text-gray-900">{{ number_format($counts['total'], 0, ',', '.') }}</strong> resultados
                        @if($breakdown !== '')
                            <span class="text-gray-500">— {{ $breakdown }}</span>
                        @endif
                    </div>
                @endif

                <nav class="mb-8 flex flex-wrap gap-2 border-b border-gray-200 pb-4" role="tablist">
                    @foreach($tabs as $tabKey => $tab)
                        @if($tabKey !== 'all' && $tab['count'] === 0)
                            @continue
                        @endif
                        <a
                            href="{{ route('search.index', ['q' => $q, 'type' => $tabKey]) }}"
                            role="tab"
                            aria-selected="{{ $type === $tabKey ? 'true' : 'false' }}"
                            class="rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $type === $tabKey ? 'bg-red-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                        >
                            {{ $tab['label'] }}
                            <span class="{{ $type === $tabKey ? 'text-red-200' : 'text-gray-500' }}">({{ $tab['count'] }})</span>
                        </a>
                    @endforeach
                </nav>

                @if($counts['total'] === 0)
                    <div class="rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 py-12 text-center">
                        <p class="mb-2 font-serif text-xl text-gray-800">Nenhum resultado para "{{ $q }}"</p>
                        <p class="text-sm text-gray-500">Tente outra palavra. A busca procura <strong>palavras inteiras</strong>, não partes dentro de outras palavras.</p>
                    </div>
                @else
                    <div class="space-y-10">
                        @foreach($searchSections as $section)
                            @include('search.partials.section', [
                                'type' => $type,
                                'title' => $section['title'],
                                'items' => $section['items'],
                                'total' => $section['total'],
                                'moreUrl' => $section['moreUrl'],
                                'showSection' => $section['showSection'],
                            ])
                        @endforeach
                    </div>

                    @if($isPaginated)
                        @php
                            $paginator = match($type) {
                                'articles' => $results['articles'],
                                'editions' => $results['editions'],
                                'archive' => $results['archive'],
                                default => null,
                            };
                        @endphp
                        @if($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginator->hasPages())
                            <div class="mt-10 border-t border-gray-200 pt-6">
                                {{ $paginator->links() }}
                            </div>
                        @endif
                    @endif
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

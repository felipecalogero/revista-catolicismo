@props([
    'q' => '',
    'size' => 'default', // default | large | compact
    'showHint' => false,
])

@php
    $value = $q !== '' ? $q : (string) request('q', '');
    $isLarge = $size === 'large';
@endphp

<form action="{{ route('search.index') }}" method="GET" role="search" class="w-full">
    <div
        class="flex w-full overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/30"
    >
        <input
            type="search"
            name="q"
            value="{{ $value }}"
            placeholder="Buscar matérias, edições, revistas e acervo…"
            autocomplete="off"
            @class([
                'min-w-0 flex-1 border-0 bg-transparent text-gray-900 placeholder:text-gray-400 focus:ring-0',
                'px-4 py-3 text-base' => $isLarge,
                'px-4 py-2 text-sm' => ! $isLarge,
            ])
            aria-label="Buscar no site"
        >
        <button
            type="submit"
            class="shrink-0 bg-red-800 px-5 py-2 text-sm font-medium text-white transition-colors hover:bg-red-900 {{ $isLarge ? 'px-6 py-3' : '' }}"
        >
            Buscar
        </button>
    </div>
    @if($showHint)
        <p class="mt-3 text-center text-sm text-gray-500">
            Pesquise em matérias do site, edições da revista e acervo histórico.
        </p>
    @endif
</form>

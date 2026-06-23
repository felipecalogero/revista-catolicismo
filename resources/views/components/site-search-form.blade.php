@props([
    'q' => '',
    'size' => 'default', // default | large | compact
    'showHint' => false,
    'subtle' => false,
])

@php
    $value = $q !== '' ? $q : (string) request('q', '');
    $isLarge = $size === 'large';
@endphp

<form action="{{ route('search.index') }}" method="GET" role="search" class="w-full">
    <div
        @class([
            'flex w-full items-center overflow-hidden rounded-lg border bg-white focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/30',
            'border-gray-200 shadow-none' => $subtle,
            'border-gray-300 shadow-sm' => ! $subtle,
        ])
    >
        <input
            type="search"
            name="q"
            value="{{ $value }}"
            placeholder="Buscar matérias, edições, revistas e acervo…"
            autocomplete="off"
            @class([
                'min-w-0 flex-1 self-center border-0 bg-transparent text-gray-900 placeholder:text-gray-400 focus:ring-0',
                'px-5 py-3.5 text-base' => $isLarge,
                'px-3 py-2 text-sm' => ! $isLarge,
            ])
            aria-label="Buscar no site"
        >
        <button
            type="submit"
            @class([
                'shrink-0 self-stretch bg-red-800 font-medium text-white transition-colors hover:bg-red-900',
                'px-6 text-sm' => $isLarge && ! $subtle,
                'px-5 text-sm' => $isLarge && $subtle,
                'px-4 py-2 text-sm' => ! $isLarge && ! $subtle,
                'px-3 py-2 text-xs' => ! $isLarge && $subtle,
            ])
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

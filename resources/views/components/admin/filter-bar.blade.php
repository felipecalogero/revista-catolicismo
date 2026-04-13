@props([
    'formAction',
    'modalId',
    'searchPlaceholder' => 'Buscar…',
    'clearUrl' => null,
])

@php
    $clearUrl = $clearUrl ?? $formAction;
@endphp

<form method="GET" action="{{ $formAction }}" class="w-full">
    <div
        class="flex w-full flex-col gap-2 sm:flex-row sm:items-stretch"
    >
        <div
            class="flex min-h-[42px] w-full min-w-0 flex-1 overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/30"
        >
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ $searchPlaceholder }}"
                class="min-w-0 flex-1 border-0 bg-transparent px-4 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0"
            >
            <div class="flex shrink-0 divide-x divide-gray-300 border-l border-gray-200">
                <button
                    type="button"
                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    onclick="document.getElementById('{{ $modalId }}').showModal()"
                >
                    Mais opções
                </button>
                <button
                    type="submit"
                    class="bg-red-800 px-4 py-2 text-sm font-medium text-white hover:bg-red-900"
                >
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <dialog id="{{ $modalId }}" class="admin-filter-dialog max-h-[90vh] w-[calc(100vw-2rem)] max-w-lg overflow-y-auto rounded-xl border border-gray-200 bg-white p-6 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
            <h3 class="font-serif text-lg font-bold text-gray-900">Mais filtros</h3>
            <button
                type="button"
                class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100"
                onclick="document.getElementById('{{ $modalId }}').close()"
                aria-label="Fechar"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="space-y-4">
            {{ $modal }}
        </div>
        <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-100 pt-4">
            <button type="submit" class="rounded-lg bg-red-800 px-4 py-2 text-sm font-medium text-white hover:bg-red-900">
                Aplicar filtros
            </button>
            <a href="{{ $clearUrl }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Limpar tudo
            </a>
            <button type="button" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100" onclick="document.getElementById('{{ $modalId }}').close()">
                Fechar
            </button>
        </div>
    </dialog>
</form>

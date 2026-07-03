@if($showSection ?? true)
    @php
        $list = $items instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $items->items()
            : ($items instanceof \Illuminate\Support\Collection ? $items->all() : (array) $items);
    @endphp

    @if(count($list) > 0 || ($total ?? 0) > 0)
        <section>
            @if(($type ?? 'all') === 'all')
                <div class="mb-4 flex items-center justify-between gap-3 border-b border-gray-200 pb-3">
                    <h2 class="font-serif text-xl font-bold text-gray-900">{{ $title }}</h2>
                    @if($moreUrl && ($total ?? 0) > count($list))
                        <a href="{{ $moreUrl }}" class="text-sm font-medium text-red-800 hover:text-red-900">
                            Ver todos ({{ $total }}) →
                        </a>
                    @endif
                </div>
            @elseif(count($list) > 0)
                <h2 class="mb-4 font-serif text-xl font-bold text-gray-900">{{ $title }}</h2>
            @endif

            <div class="space-y-4">
                @foreach($list as $item)
                    <article class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md sm:flex-row">
                        <a href="{{ $item['url'] }}" class="block shrink-0 self-start">
                            @if(!empty($item['image']))
                                <img
                                    src="{{ $item['image'] }}"
                                    alt=""
                                    class="search-result-image shadow"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="search-result-image flex items-center justify-center text-xs text-gray-400">
                                    Sem imagem
                                </div>
                            @endif
                        </a>

                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <a href="{{ $item['url'] }}" class="font-serif text-lg font-bold text-gray-900 hover:text-red-800">
                                    {{ $item['title'] }}
                                </a>
                                @if(!empty($item['is_legacy']))
                                    <span class="rounded bg-amber-700/10 px-2 py-0.5 text-xs font-medium uppercase text-amber-800">Acervo</span>
                                @endif
                            </div>
                            @if(!empty($item['meta']))
                                <p class="mb-2 text-xs uppercase tracking-wide text-gray-500">{{ $item['meta'] }}</p>
                            @endif
                            @if(!empty($item['snippet_html']))
                                <p class="mb-3 text-sm leading-relaxed text-gray-700 [&_mark]:rounded [&_mark]:bg-yellow-200 [&_mark]:px-0.5 [&_mark]:font-semibold [&_mark]:text-gray-900">
                                    {!! $item['snippet_html'] !!}
                                </p>
                            @endif
                            <a
                                href="{{ $item['url'] }}"
                                class="inline-flex items-center gap-1 text-sm font-medium text-red-800 hover:text-red-900"
                            >
                                Abrir →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @elseif(($type ?? 'all') !== 'all')
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-8 text-center text-gray-500">
            Nenhum resultado nesta categoria.
        </div>
    @endif
@endif

@extends('layouts.app')

@section('title', 'Texto por página – ' . $edition->title)

@php
    $pageTexts = $edition->pageTexts;
    $hasPdf = (bool) $edition->pdf_file;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800 flex flex-col md:flex-row justify-between items-start gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">{{ $edition->title }}</p>
                    <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                        Texto por página
                    </h1>
                    <p class="text-gray-600 text-sm max-w-3xl">
                        O texto abaixo é exibido sincronizado com o visualizador da revista (modo revista).
                        Edições manuais ficam marcadas como "manualmente editadas" e <strong>não são sobrescritas</strong> em uma nova extração.
                    </p>
                </div>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('admin.editions.edit', $edition->id) }}" class="text-center bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium text-sm">
                        ← Voltar para a edição
                    </a>
                    @if($edition->is_legacy)
                        <form action="{{ route('admin.editions.extract-text', $edition->id) }}" method="POST" onsubmit="return confirm('Reagrupar o texto a partir das matérias importadas? Páginas marcadas como manualmente editadas serão sobrescritas.');">
                            @csrf
                            <button type="submit" class="w-full bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium text-sm">
                                Reagrupar a partir das matérias
                            </button>
                        </form>
                    @elseif($hasPdf)
                        <form action="{{ route('admin.editions.extract-text', $edition->id) }}" method="POST" onsubmit="return confirm('Re-extrair texto a partir do PDF? Páginas marcadas como manualmente editadas serão sobrescritas.');">
                            @csrf
                            <button type="submit" class="w-full bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium text-sm">
                                Re-extrair do PDF
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if($pageTexts->isEmpty())
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 text-center">
                    <p class="text-amber-900 font-medium mb-2">Nenhum texto extraído para esta edição ainda.</p>
                    @if($edition->is_legacy)
                        <p class="text-sm text-amber-800">
                            Esta edição do acervo legado não possui matérias importadas (provavelmente não foi escaneada ou não tem o arquivo <code>Texto_PXX.html</code>).
                            Use o botão "Adicionar página manualmente" abaixo para preencher o texto.
                        </p>
                    @elseif($hasPdf)
                        <p class="text-sm text-amber-800">
                            Clique em "Re-extrair do PDF" acima para tentar a extração automática, ou adicione o texto manualmente abaixo.
                        </p>
                    @else
                        <p class="text-sm text-amber-800">
                            Esta edição não tem PDF nem matérias importadas. Adicione um PDF ou preencha o texto manualmente.
                        </p>
                    @endif
                </div>
            @endif

            <div class="space-y-6 mt-6">
                @foreach($pageTexts as $pageText)
                    <details class="border border-gray-200 rounded-lg" {{ $loop->first ? 'open' : '' }}>
                        <summary class="cursor-pointer px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-t-lg flex items-center justify-between">
                            <span class="font-bold text-gray-900">
                                Página {{ $pageText->page_label }}
                                @if($pageText->manually_edited)
                                    <span class="ml-2 inline-block bg-amber-200 text-amber-900 text-xs font-medium px-2 py-0.5 rounded uppercase">Editada manualmente</span>
                                @endif
                                @if(empty($pageText->body_html))
                                    <span class="ml-2 inline-block bg-gray-300 text-gray-800 text-xs font-medium px-2 py-0.5 rounded uppercase">Sem texto</span>
                                @endif
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ Str::limit(strip_tags((string) $pageText->body_html), 90) ?: '(vazio)' }}
                            </span>
                        </summary>
                        <div class="p-4 space-y-3">
                            <form action="{{ route('admin.editions.page-texts.update', [$edition->id, $pageText->page_label]) }}" method="POST" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <label class="block text-sm font-medium text-gray-700">HTML do texto da página {{ $pageText->page_label }}</label>
                                <textarea
                                    name="body_html"
                                    rows="14"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent font-mono text-sm"
                                    placeholder="<p>Conteúdo da página…</p>"
                                >{{ $pageText->body_html }}</textarea>
                                <p class="text-xs text-gray-500">
                                    Aceita HTML simples (<code>&lt;p&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>, listas, etc.). Salvar marca esta página como "editada manualmente" — re-extrações futuras a preservarão.
                                </p>
                                <div class="flex gap-2 flex-wrap">
                                    <button type="submit" class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium text-sm">
                                        Salvar texto da página {{ $pageText->page_label }}
                                    </button>
                                    @if($pageText->manually_edited)
                                        <button
                                            type="button"
                                            class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium text-sm"
                                            onclick="document.getElementById('reset-form-{{ $pageText->id }}').submit();"
                                        >
                                            Permitir re-extração automática
                                        </button>
                                    @endif
                                </div>
                            </form>

                            @if($pageText->manually_edited)
                                <form id="reset-form-{{ $pageText->id }}" action="{{ route('admin.editions.page-texts.reset', [$edition->id, $pageText->page_label]) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('PUT')
                                </form>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>

            <details class="mt-8 border border-dashed border-gray-300 rounded-lg">
                <summary class="cursor-pointer px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg font-medium text-gray-800">
                    + Adicionar página manualmente
                </summary>
                <div class="p-4">
                    <form action="{{ route('admin.editions.page-texts.create', $edition->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Label da página</label>
                            <input type="text" name="page_label" required maxlength="32"
                                placeholder="Ex: 1 (PDF), P01 ou P02-03 (acervo legado)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">HTML do texto</label>
                            <textarea name="body_html" rows="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent font-mono text-sm" placeholder="<p>…</p>"></textarea>
                        </div>
                        <button type="submit" class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium text-sm">
                            Adicionar página
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</div>
@endsection

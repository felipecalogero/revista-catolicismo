@extends('layouts.app')

@section('title', 'Visualizar Revista - ' . $edition->title . ' - Revista Catolicismo')

@php
    $fileUrl = $edition->pdf_file_url;
    $hasPages = $edition->pages->isNotEmpty();
    $pages = $edition->pages;
    $pageTexts = collect($pageTexts ?? [])
        ->filter(fn ($html) => trim(strip_tags((string) $html)) !== '')
        ->all();
    $hasAnyPageText = ! empty($pageTexts);
@endphp

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6]">
    <div class="absolute inset-0 bg-textura" aria-hidden="true"></div>
    <div class="relative z-10">
    {{-- Header --}}
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 lg:px-8 py-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 font-serif">{{ $edition->title }}</h1>
                    <p class="text-sm text-gray-600 mt-1">Visualização em formato revista</p>
                </div>
                <div class="flex gap-3">
                    <a
                        href="{{ route('editions.show', $edition->slug) }}"
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                    >
                        ← Voltar
                    </a>
                    @if($fileUrl)
                    <a
                        href="{{ route('editions.download', $edition->slug) }}"
                        class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium"
                    >
                        Baixar PDF
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Visualizador --}}
    <div class="container mx-auto px-4 lg:px-8 py-8">
        <script>
            window.PAGE_TEXTS = @json($pageTexts);
            window.HAS_ANY_PAGE_TEXT = @json($hasAnyPageText);
        </script>
        <div id="magazine-viewer" class="bg-white rounded-lg shadow-lg p-4">
            @if($hasPages)
                {{-- Visualizador de imagens (acervo legado) --}}
                <div
                    id="magazine-pages-viewer"
                    data-pages='@json($pages->map(fn ($p) => ["label" => $p->label, "url" => $p->image_url, "is_spread" => (bool) $p->is_spread])->values())'
                    class="w-full"
                >
                    <div class="flex justify-center items-center mb-4 flex-wrap gap-2">
                        <div class="flex gap-2 items-center">
                            <button id="prev-page-btn" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-900 transition-colors disabled:opacity-40" type="button">
                                ← Anterior
                            </button>
                            <span class="px-4 py-2 text-gray-700 text-sm">
                                Página <span id="current-page-label">—</span> de <span id="total-pages">{{ $pages->count() }}</span>
                            </span>
                            <button id="next-page-btn" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-900 transition-colors disabled:opacity-40" type="button">
                                Próxima →
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button id="zoom-out-btn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors" type="button">−</button>
                            <span class="px-4 py-2 text-gray-700 text-sm">Zoom: <span id="zoom-level">100</span>%</span>
                            <button id="zoom-in-btn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors" type="button">+</button>
                            <button id="zoom-reset-btn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors text-sm" type="button">Ajustar</button>
                        </div>
                    </div>
                    <div class="flex justify-center bg-gray-50 rounded p-4 overflow-auto">
                        <img
                            id="magazine-page-img"
                            src=""
                            alt=""
                            style="max-width: 100%; height: auto; transform-origin: top center;"
                            class="shadow-lg border border-gray-300"
                        >
                    </div>
                </div>

                <script>
                    (function () {
                        const root = document.getElementById('magazine-pages-viewer');
                        if (!root) return;
                        const pages = JSON.parse(root.dataset.pages || '[]');
                        if (!pages.length) return;

                        let index = 0;
                        let zoom = 1.0;

                        const img = document.getElementById('magazine-page-img');
                        const labelEl = document.getElementById('current-page-label');
                        const zoomEl = document.getElementById('zoom-level');
                        const prevBtn = document.getElementById('prev-page-btn');
                        const nextBtn = document.getElementById('next-page-btn');
                        const zoomIn = document.getElementById('zoom-in-btn');
                        const zoomOut = document.getElementById('zoom-out-btn');
                        const zoomReset = document.getElementById('zoom-reset-btn');

                        function render() {
                            const p = pages[index];
                            img.src = p.url;
                            img.alt = 'Página ' + p.label;
                            labelEl.textContent = p.label;
                            img.style.transform = 'scale(' + zoom + ')';
                            zoomEl.textContent = Math.round(zoom * 100);
                            prevBtn.disabled = index === 0;
                            nextBtn.disabled = index === pages.length - 1;
                            if (typeof window.updatePageText === 'function') {
                                window.updatePageText(p.label, null);
                            }
                        }

                        prevBtn.addEventListener('click', function () {
                            if (index > 0) { index--; render(); }
                        });
                        nextBtn.addEventListener('click', function () {
                            if (index < pages.length - 1) { index++; render(); }
                        });
                        zoomIn.addEventListener('click', function () {
                            zoom = Math.min(zoom + 0.2, 3); render();
                        });
                        zoomOut.addEventListener('click', function () {
                            zoom = Math.max(zoom - 0.2, 0.4); render();
                        });
                        zoomReset.addEventListener('click', function () {
                            zoom = 1; render();
                        });

                        document.addEventListener('keydown', function (e) {
                            if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) return;
                            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                                e.preventDefault();
                                if (index > 0) { index--; render(); }
                            } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                                e.preventDefault();
                                if (index < pages.length - 1) { index++; render(); }
                            }
                        });

                        render();
                    })();
                </script>
            @elseif($fileUrl)
                {{-- Visualizador PDF.js (edições atuais com PDF) --}}
                <div class="w-full">
                    <div class="flex justify-center items-center mb-4 flex-wrap gap-2">
                        <div class="flex gap-2">
                            <button id="prev-page" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-900 transition-colors">← Anterior</button>
                            <span class="px-4 py-2 text-gray-700">
                                Páginas <span id="page-num-left">1</span>-<span id="page-num-right">2</span> de <span id="page-count">-</span>
                            </span>
                            <button id="next-page" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-900 transition-colors">Próxima →</button>
                        </div>
                        <div class="flex gap-2">
                            <button id="zoom-out" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors">-</button>
                            <span class="px-4 py-2 text-gray-700">Zoom: <span id="zoom-level">100</span>%</span>
                            <button id="zoom-in" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors">+</button>
                            <button id="fit-width" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors text-sm">Ajustar Largura</button>
                        </div>
                    </div>
                    <div class="flex justify-center bg-gray-50 rounded p-4 overflow-x-auto">
                        <div id="pages-container" class="flex gap-2 items-start">
                            <canvas id="pdf-canvas-left" class="border border-gray-300 shadow-lg"></canvas>
                            <canvas id="pdf-canvas-right" class="border border-gray-300 shadow-lg"></canvas>
                        </div>
                    </div>
                </div>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
                <script>
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                    let pdfDoc = null;
                    let pageNum = 1;
                    let pageRendering = false;
                    let pageNumPending = null;
                    let scale = 1.0;
                    let scaleType = 'auto';
                    const canvasLeft = document.getElementById('pdf-canvas-left');
                    const canvasRight = document.getElementById('pdf-canvas-right');
                    const ctxLeft = canvasLeft.getContext('2d');
                    const ctxRight = canvasRight.getContext('2d');

                    function renderPage(canvas, ctx, pageNum) {
                        return pdfDoc.getPage(pageNum).then(function(page) {
                            let viewport;
                            if (scaleType === 'width') {
                                const containerWidth = canvas.parentElement.parentElement.clientWidth - 40;
                                const pageWidth = (containerWidth - 16) / 2;
                                viewport = page.getViewport({scale: 1});
                                scale = pageWidth / viewport.width;
                                viewport = page.getViewport({scale: scale});
                            } else if (scaleType === 'page') {
                                const containerWidth = canvas.parentElement.parentElement.clientWidth - 40;
                                const containerHeight = window.innerHeight - 300;
                                const pageWidth = (containerWidth - 16) / 2;
                                viewport = page.getViewport({scale: 1});
                                const scaleX = pageWidth / viewport.width;
                                const scaleY = containerHeight / viewport.height;
                                scale = Math.min(scaleX, scaleY);
                                viewport = page.getViewport({scale: scale});
                            } else {
                                viewport = page.getViewport({scale: scale});
                            }
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            return page.render({canvasContext: ctx, viewport: viewport}).promise;
                        });
                    }

                    function renderPages() {
                        pageRendering = true;
                        const leftPage = pageNum;
                        const rightPage = pageNum + 1 <= pdfDoc.numPages ? pageNum + 1 : null;
                        const promises = [renderPage(canvasLeft, ctxLeft, leftPage)];
                        if (rightPage) {
                            promises.push(renderPage(canvasRight, ctxRight, rightPage));
                        } else {
                            ctxRight.clearRect(0, 0, canvasRight.width, canvasRight.height);
                            canvasRight.width = 0;
                            canvasRight.height = 0;
                        }
                    Promise.all(promises).then(function() {
                        pageRendering = false;
                        document.getElementById('page-num-left').textContent = leftPage;
                        if (rightPage) {
                            document.getElementById('page-num-right').textContent = rightPage;
                            canvasRight.style.display = 'block';
                        } else {
                            document.getElementById('page-num-right').textContent = leftPage;
                            canvasRight.style.display = 'none';
                        }
                        if (typeof window.updatePageText === 'function') {
                            window.updatePageText(leftPage, rightPage);
                        }
                        if (pageNumPending !== null) {
                            pageNum = pageNumPending;
                            pageNumPending = null;
                            renderPages();
                        }
                    });
                    }

                    function queueRenderPages(num) {
                        if (pageRendering) { pageNumPending = num; } else { pageNum = num; renderPages(); }
                    }

                    document.getElementById('prev-page').addEventListener('click', function() {
                        if (pageNum <= 1) return;
                        pageNum = Math.max(1, pageNum - 2);
                        scaleType = 'auto';
                        queueRenderPages(pageNum);
                    });
                    document.getElementById('next-page').addEventListener('click', function() {
                        const nextPage = pageNum + 2;
                        if (nextPage > pdfDoc.numPages) return;
                        pageNum = nextPage;
                        scaleType = 'auto';
                        queueRenderPages(pageNum);
                    });
                    document.getElementById('zoom-in').addEventListener('click', function() {
                        scaleType = 'auto';
                        scale += 0.2;
                        document.getElementById('zoom-level').textContent = Math.round(scale * 100);
                        queueRenderPages(pageNum);
                    });
                    document.getElementById('zoom-out').addEventListener('click', function() {
                        if (scale <= 0.5) return;
                        scaleType = 'auto';
                        scale -= 0.2;
                        document.getElementById('zoom-level').textContent = Math.round(scale * 100);
                        queueRenderPages(pageNum);
                    });
                    document.getElementById('fit-width').addEventListener('click', function() {
                        scaleType = 'width';
                        queueRenderPages(pageNum);
                    });
                    document.addEventListener('keydown', function(e) {
                        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) return;
                        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                            e.preventDefault();
                            if (pageNum > 1) { pageNum = Math.max(1, pageNum - 2); scaleType = 'auto'; queueRenderPages(pageNum); }
                        } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                            e.preventDefault();
                            const nextPage = pageNum + 2;
                            if (nextPage <= pdfDoc.numPages) { pageNum = nextPage; scaleType = 'auto'; queueRenderPages(pageNum); }
                        }
                    });

                    pdfjsLib.getDocument('{{ $fileUrl }}').promise.then(function(pdf) {
                        pdfDoc = pdf;
                        document.getElementById('page-count').textContent = pdf.numPages;
                        scaleType = 'width';
                        renderPages();
                    }).catch(function(error) {
                        console.error('Erro ao carregar PDF:', error);
                        alert('Erro ao carregar o PDF. Tente novamente.');
                    });
                </script>
            @else
                <p class="text-center text-gray-600 py-12">
                    Esta edição ainda não tem páginas ou PDF associados.
                </p>
            @endif
        </div>

        {{-- Texto sincronizado com a página atual do visualizador --}}
        @if($hasPages || $fileUrl)
            <aside id="page-text-panel" class="bg-white rounded-lg shadow-lg p-6 md:p-10 mt-6 w-full">
                <div class="flex items-center justify-between flex-wrap gap-3 mb-4 pb-3 border-b border-gray-200">
                    <h2 class="font-serif text-2xl text-gray-900">Texto desta página</h2>
                    <span id="page-text-label" class="text-xs uppercase tracking-wide text-gray-500"></span>
                </div>
                <div id="page-text-content" class="quill-content w-full text-gray-800 text-base md:text-lg leading-relaxed">
                    @if($hasAnyPageText)
                        <p class="text-gray-500 italic">Carregando texto…</p>
                    @else
                        <p class="text-gray-500 italic">O texto desta edição ainda não está disponível em formato digital. Use o visualizador acima para ler as páginas escaneadas.</p>
                    @endif
                </div>
            </aside>

            <script>
                (function () {
                    var PAGE_TEXTS = window.PAGE_TEXTS || {};
                    var HAS_ANY = !!window.HAS_ANY_PAGE_TEXT;
                    var contentEl = document.getElementById('page-text-content');
                    var labelEl = document.getElementById('page-text-label');
                    if (!contentEl) return;

                    function fallbackHtml(label) {
                        if (!HAS_ANY) {
                            return '<p class="text-gray-500 italic">O texto desta edição ainda não está disponível em formato digital.</p>';
                        }
                        return '<p class="text-gray-500 italic">Texto desta página ainda não disponível.</p>';
                    }

                    function getText(key) {
                        if (key == null) return null;
                        if (Object.prototype.hasOwnProperty.call(PAGE_TEXTS, key)) {
                            var v = PAGE_TEXTS[key];
                            if (typeof v === 'string' && v.trim() !== '') return v;
                        }
                        return null;
                    }

                    window.updatePageText = function (left, right) {
                        if (right === undefined) right = null;
                        var labels = [];
                        var parts = [];

                        var leftText = getText(String(left));
                        if (leftText) {
                            parts.push('<section data-page="' + left + '">' + leftText + '</section>');
                        }
                        if (left != null) labels.push(String(left));

                        if (right != null && String(right) !== String(left)) {
                            var rightText = getText(String(right));
                            if (rightText) {
                                parts.push('<section data-page="' + right + '">' + rightText + '</section>');
                            }
                            labels.push(String(right));
                        }

                        contentEl.innerHTML = parts.length
                            ? parts.join('<hr class="my-8 border-gray-200">')
                            : fallbackHtml(labels.join(', '));

                        if (labelEl) {
                            labelEl.textContent = labels.length ? ('Página ' + labels.join(' / ')) : '';
                        }
                    };

                    if (!HAS_ANY) {
                        // Garante mensagem padrão antes do viewer iniciar.
                        contentEl.innerHTML = fallbackHtml('');
                    }
                })();
            </script>
        @endif
    </div>
</div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Visualizar Revista - ' . $edition->title . ' - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-100">
    {{-- Header --}}
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 font-serif">{{ $edition->title }}</h1>
                    <p class="text-sm text-gray-600 mt-1">Visualização em formato revista</p>
                </div>
                <div class="flex gap-3">
                    <a 
                        href="{{ route('editions.show', ['slug' => $edition->slug]) }}"
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                    >
                        ← Voltar
                    </a>
                    <a 
                        href="{{ route('editions.download', ['slug' => $edition->slug]) }}"
                        class="bg-red-800 text-white px-4 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium"
                    >
                        Baixar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Visualizador de Revista --}}
    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div id="magazine-viewer" class="bg-white rounded-lg shadow-lg p-4">
            @php
                $fileUrl = $edition->pdf_file_url;
            @endphp

            {{-- Visualizador de PDF usando PDF.js --}}
            <div class="w-full">
                <div class="flex justify-center items-center mb-4 flex-wrap gap-2">
                    <div class="flex gap-2">
                        <button id="prev-page" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-900 transition-colors">
                            ← Anterior
                        </button>
                        <span class="px-4 py-2 text-gray-700">
                            Páginas <span id="page-num-left">1</span>-<span id="page-num-right">2</span> de <span id="page-count">-</span>
                        </span>
                        <button id="next-page" class="bg-red-800 text-white px-4 py-2 rounded hover:bg-red-900 transition-colors">
                            Próxima →
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button id="zoom-out" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors">
                            -
                        </button>
                        <span class="px-4 py-2 text-gray-700">
                            Zoom: <span id="zoom-level">100</span>%
                        </span>
                        <button id="zoom-in" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors">
                            +
                        </button>
                        <button id="fit-width" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition-colors text-sm">
                            Ajustar Largura
                        </button>
                    </div>
                </div>
                <div class="flex justify-center bg-gray-50 rounded p-4 overflow-x-auto">
                    <div id="pages-container" class="flex gap-2 items-start">
                        <canvas id="pdf-canvas-left" class="border border-gray-300 shadow-lg"></canvas>
                        <canvas id="pdf-canvas-right" class="border border-gray-300 shadow-lg"></canvas>
                    </div>
                </div>
            </div>

            {{-- PDF.js --}}
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
            <script>
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                
                let pdfDoc = null;
                let pageNum = 1; // Página esquerda (ímpar)
                let pageRendering = false;
                let pageNumPending = null;
                let scale = 1.0;
                let scaleType = 'auto'; // 'auto', 'width', 'page'
                const canvasLeft = document.getElementById('pdf-canvas-left');
                const canvasRight = document.getElementById('pdf-canvas-right');
                const ctxLeft = canvasLeft.getContext('2d');
                const ctxRight = canvasRight.getContext('2d');

                function renderPage(canvas, ctx, pageNum) {
                    return pdfDoc.getPage(pageNum).then(function(page) {
                        let viewport;
                        
                        if (scaleType === 'width') {
                            // Ajustar à largura do container (cada página ocupa metade)
                            const containerWidth = canvas.parentElement.parentElement.clientWidth - 40;
                            const pageWidth = (containerWidth - 16) / 2; // 16px de gap entre páginas
                            viewport = page.getViewport({scale: 1});
                            scale = pageWidth / viewport.width;
                            viewport = page.getViewport({scale: scale});
                        } else if (scaleType === 'page') {
                            // Ajustar à página completa
                            const containerWidth = canvas.parentElement.parentElement.clientWidth - 40;
                            const containerHeight = window.innerHeight - 300;
                            const pageWidth = (containerWidth - 16) / 2;
                            viewport = page.getViewport({scale: 1});
                            const scaleX = pageWidth / viewport.width;
                            const scaleY = containerHeight / viewport.height;
                            scale = Math.min(scaleX, scaleY);
                            viewport = page.getViewport({scale: scale});
                        } else {
                            // Usar escala atual
                            viewport = page.getViewport({scale: scale});
                        }
                        
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };
                        return page.render(renderContext).promise;
                    });
                }

                function renderPages() {
                    pageRendering = true;
                    
                    // Renderizar página esquerda (ímpar)
                    const leftPage = pageNum;
                    const rightPage = pageNum + 1 <= pdfDoc.numPages ? pageNum + 1 : null;
                    
                    const promises = [renderPage(canvasLeft, ctxLeft, leftPage)];
                    
                    if (rightPage) {
                        promises.push(renderPage(canvasRight, ctxRight, rightPage));
                    } else {
                        // Se não houver página direita, limpar o canvas
                        ctxRight.clearRect(0, 0, canvasRight.width, canvasRight.height);
                        canvasRight.width = 0;
                        canvasRight.height = 0;
                    }
                    
                    Promise.all(promises).then(function() {
                        pageRendering = false;
                        
                        // Atualizar indicadores de página
                        document.getElementById('page-num-left').textContent = leftPage;
                        if (rightPage) {
                            document.getElementById('page-num-right').textContent = rightPage;
                            canvasRight.style.display = 'block';
                        } else {
                            document.getElementById('page-num-right').textContent = leftPage;
                            canvasRight.style.display = 'none';
                        }
                        
                        if (pageNumPending !== null) {
                            pageNum = pageNumPending;
                            pageNumPending = null;
                            renderPages();
                        }
                    });
                }

                function queueRenderPages(num) {
                    if (pageRendering) {
                        pageNumPending = num;
                    } else {
                        pageNum = num;
                        renderPages();
                    }
                }

                function onPrevPage() {
                    if (pageNum <= 1) {
                        return;
                    }
                    // Ir para a página anterior (duas páginas atrás)
                    pageNum = Math.max(1, pageNum - 2);
                    scaleType = 'auto';
                    queueRenderPages(pageNum);
                }

                function onNextPage() {
                    // Ir para a próxima página (duas páginas à frente)
                    const nextPage = pageNum + 2;
                    if (nextPage > pdfDoc.numPages) {
                        return;
                    }
                    pageNum = nextPage;
                    scaleType = 'auto';
                    queueRenderPages(pageNum);
                }

                function onZoomIn() {
                    scaleType = 'auto';
                    scale += 0.2;
                    document.getElementById('zoom-level').textContent = Math.round(scale * 100);
                    queueRenderPages(pageNum);
                }

                function onZoomOut() {
                    if (scale <= 0.5) {
                        return;
                    }
                    scaleType = 'auto';
                    scale -= 0.2;
                    document.getElementById('zoom-level').textContent = Math.round(scale * 100);
                    queueRenderPages(pageNum);
                }

                function onFitWidth() {
                    scaleType = 'width';
                    queueRenderPages(pageNum);
                }

                document.getElementById('prev-page').addEventListener('click', onPrevPage);
                document.getElementById('next-page').addEventListener('click', onNextPage);
                document.getElementById('zoom-in').addEventListener('click', onZoomIn);
                document.getElementById('zoom-out').addEventListener('click', onZoomOut);
                document.getElementById('fit-width').addEventListener('click', onFitWidth);

                // Navegação por teclado
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        onPrevPage();
                    } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        onNextPage();
                    } else if (e.key === '+' || e.key === '=') {
                        e.preventDefault();
                        onZoomIn();
                    } else if (e.key === '-') {
                        e.preventDefault();
                        onZoomOut();
                    }
                });

                // Carregar PDF
                pdfjsLib.getDocument('{{ $fileUrl }}').promise.then(function(pdf) {
                    pdfDoc = pdf;
                    document.getElementById('page-count').textContent = pdf.numPages;
                    scaleType = 'width'; // Iniciar ajustado à largura
                    renderPages();
                }).catch(function(error) {
                    console.error('Erro ao carregar PDF:', error);
                    alert('Erro ao carregar o PDF. Por favor, tente novamente.');
                });
            </script>
        </div>
    </div>
</div>
@endsection

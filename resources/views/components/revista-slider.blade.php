@props(['revistas'])

<section class="bg-white py-16 border-b border-gray-200">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="mb-10 pb-4 border-b-2 border-red-800">
            <h2 class="text-3xl font-bold text-gray-900 font-serif mb-2">Edições</h2>
            <p class="text-gray-600 text-sm">Nossas últimas publicações</p>
        </div>
        
        {{-- Slider de Revistas Compacto --}}
        <div class="relative">
            <div id="revista-slider" class="overflow-hidden">
                <div class="flex transition-transform duration-500 ease-in-out" id="slider-container" style="transform: translateX(0%)">
                    @foreach(array_slice($revistas, 0, 10) as $index => $revista)
                        <div class="min-w-[200px] md:min-w-[250px] lg:min-w-[200px] px-2 slider-item flex-shrink-0" data-index="{{ $index }}">
                            <a href="#" class="block group">
                                <div class="relative overflow-hidden rounded shadow-md hover:shadow-xl transition-shadow">
                                    <img 
                                        src="{{ $revista['capa'] }}" 
                                        alt="{{ $revista['titulo'] }}"
                                        class="w-full h-[280px] md:h-[320px] object-cover transition-transform duration-300 group-hover:scale-105"
                                    >
                                    @if(isset($revista['destaque']) && $revista['destaque'])
                                        <span class="absolute top-2 right-2 bg-red-800 text-white px-2 py-1 text-xs font-medium rounded">
                                            NOVA
                                        </span>
                                    @endif
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                                        <div class="text-white text-xs font-medium mb-1">{{ $revista['edicao'] ?? 'Edição' }}</div>
                                        <div class="text-white text-xs">{{ $revista['data'] ?? '' }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Botões de Navegação --}}
            <button 
                id="prev-btn" 
                class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/90 border border-gray-300 rounded-full p-2 shadow-lg hover:bg-white transition-colors z-10"
                aria-label="Anterior"
            >
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button 
                id="next-btn" 
                class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/90 border border-gray-300 rounded-full p-2 shadow-lg hover:bg-white transition-colors z-10"
                aria-label="Próximo"
            >
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
        
        {{-- Link para Edições Anteriores --}}
        <div class="mt-8 text-center">
            <a href="#" class="inline-flex items-center gap-2 text-red-800 hover:text-red-900 font-medium text-sm border-b border-red-800 hover:border-red-900 transition-colors">
                Ver todas as edições anteriores
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('slider-container');
    const items = document.querySelectorAll('.slider-item');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const indicators = document.querySelectorAll('.slider-indicator');
    
    let currentIndex = 0;
    const totalItems = items.length;
    let autoplay;
    
    function getItemsPerView() {
        if (window.innerWidth >= 1024) return 5; // Desktop: 5 revistas
        if (window.innerWidth >= 768) return 3;  // Tablet: 3 revistas
        return 2; // Mobile: 2 revistas
    }
    
    function updateSlider() {
        const itemsPerView = getItemsPerView();
        const maxIndex = Math.max(0, totalItems - itemsPerView);
        
        // Limitar o índice atual para evitar espaço em branco
        if (currentIndex > maxIndex) {
            currentIndex = maxIndex;
        }
        
        // Garantir que não há espaço em branco
        if (totalItems <= itemsPerView) {
            currentIndex = 0;
        }
        
        const itemWidth = 100 / itemsPerView;
        const translateX = -(currentIndex * itemWidth);
        container.style.transform = `translateX(${translateX}%)`;
        
        // Desabilitar botões quando necessário
        if (prevBtn && nextBtn) {
            const canGoPrev = currentIndex > 0;
            const canGoNext = currentIndex < maxIndex && totalItems > itemsPerView;
            
            prevBtn.disabled = !canGoPrev;
            nextBtn.disabled = !canGoNext;
            
            if (!canGoPrev) {
                prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                prevBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            
            if (!canGoNext) {
                nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }
    
    function nextSlide() {
        const itemsPerView = getItemsPerView();
        const maxIndex = Math.max(0, totalItems - itemsPerView);
        
        if (currentIndex < maxIndex) {
            currentIndex++;
        } else {
            // Não voltar ao início, apenas parar no último
            currentIndex = maxIndex;
        }
        updateSlider();
    }
    
    function prevSlide() {
        const itemsPerView = getItemsPerView();
        const maxIndex = Math.max(0, totalItems - itemsPerView);
        
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = maxIndex > 0 ? maxIndex : 0;
        }
        updateSlider();
    }
    
    function startAutoplay() {
        const itemsPerView = getItemsPerView();
        const maxIndex = Math.max(0, totalItems - itemsPerView);
        
        // Só inicia autoplay se houver mais itens do que o visível
        if (totalItems > itemsPerView && currentIndex < maxIndex) {
            autoplay = setInterval(nextSlide, 4000);
        }
    }
    
    function stopAutoplay() {
        if (autoplay) {
            clearInterval(autoplay);
        }
    }
    
    nextBtn?.addEventListener('click', () => {
        const itemsPerView = getItemsPerView();
        const maxIndex = Math.max(0, totalItems - itemsPerView);
        if (currentIndex < maxIndex) {
            nextSlide();
            stopAutoplay();
            startAutoplay();
        }
    });
    
    prevBtn?.addEventListener('click', () => {
        if (currentIndex > 0) {
            prevSlide();
            stopAutoplay();
            startAutoplay();
        }
    });
    
    // Auto-play apenas se necessário
    if (totalItems > getItemsPerView()) {
        startAutoplay();
    }
    
    // Pausar no hover
    const slider = document.getElementById('revista-slider');
    slider?.addEventListener('mouseenter', stopAutoplay);
    slider?.addEventListener('mouseleave', startAutoplay);
    
    // Responsive
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            currentIndex = 0;
            updateSlider();
        }, 250);
    });
    
    // Inicializar
    updateSlider();
});
</script>


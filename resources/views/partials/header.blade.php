<header class="bg-[#fdfcfb] border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex items-center justify-between gap-4 h-20">
            {{-- Logo --}}
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img 
                        src="{{ asset('img/logo.png') }}" 
                        alt="Catolicismo" 
                        class="h-12 md:h-16 object-contain"
                    >
                </a>
            </div>

            {{-- Menu Desktop --}}
            <nav class="hidden lg:flex min-w-0 flex-1 items-center justify-end gap-x-4 xl:gap-x-5">
                <a href="{{ route('home') }}" class="shrink-0 whitespace-nowrap text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                    Início
                </a>
                <a href="{{ route('pages.about') }}" class="shrink-0 whitespace-nowrap text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                    Sobre nós
                </a>
                {{-- Dropdown de Edições --}}
                <div class="relative shrink-0 group">
                    <button class="whitespace-nowrap text-gray-700 hover:text-red-800 font-medium text-sm transition-colors flex items-center gap-1">
                        Edições
                        <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute top-full left-0 mt-2 w-56 bg-[#fdfcfb] rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-2">
                            <a href="{{ route('editions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                                Lista completa
                            </a>
                            <a href="{{ route('editions.gallery') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                                Galeria por década
                            </a>
                        </div>
                    </div>
                </div>
                @if(isset($categories) && $categories->count() > 0)
                    {{-- Categorias em dropdown: evita nomes longos quebrando na barra --}}
                    <div class="relative shrink-0 group">
                        <button class="whitespace-nowrap text-gray-700 hover:text-red-800 font-medium text-sm transition-colors flex items-center gap-1">
                            Seções
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute top-full left-0 mt-2 max-h-[70vh] w-64 overflow-y-auto bg-[#fdfcfb] rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="py-2">
                                @foreach($categories as $category)
                                    <a href="{{ route('categories.show', $category->slug) }}" class="block px-4 py-2 text-sm leading-snug text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                <a
                    href="{{ route('search.index') }}"
                    class="hidden shrink-0 whitespace-nowrap lg:inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 transition-colors hover:border-red-300 hover:text-red-800"
                    title="Buscar no site"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Buscar
                </a>
                @auth
                    <div class="flex shrink-0 items-center gap-2 border-l border-gray-300 pl-3 ml-1">
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="bg-red-800 text-white px-3 py-1.5 rounded-md hover:bg-red-900 font-medium text-sm transition-colors shadow-sm">
                                Admin
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="bg-red-800 text-white px-3 py-1.5 rounded-md hover:bg-red-900 font-medium text-sm transition-colors shadow-sm">
                                Dashboard
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-gray-600 text-white px-3 py-1.5 rounded-md hover:bg-gray-700 font-medium text-sm transition-colors shadow-sm">
                                Sair
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ login_url() }}" class="shrink-0 whitespace-nowrap text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                        Entrar
                    </a>
                    <a href="{{ route('subscriptions.plans') }}" class="shrink-0 whitespace-nowrap bg-red-800 text-white px-5 py-2 rounded hover:bg-red-900 transition-colors font-medium text-sm">
                        Assinar
                    </a>
                @endauth
            </nav>

            {{-- Menu Mobile Button --}}
            <button
                id="mobile-menu-button"
                class="lg:hidden p-2 text-gray-700 hover:text-red-800 transition-colors"
                aria-label="Menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden lg:hidden pb-4 border-t border-gray-200 mt-2">
            <div class="pt-4 pb-2">
                <a href="{{ route('search.index') }}" class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 hover:border-red-300 hover:text-red-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Buscar no site
                </a>
            </div>
            <nav class="flex flex-col space-y-3 pt-2">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                    Início
                </a>
                <a href="{{ route('pages.about') }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                    Sobre nós
                </a>
                {{-- Edições (mobile) --}}
                <div>
                    <button id="editions-mobile-btn" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors flex items-center gap-1 w-full">
                        Edições
                        <svg id="editions-mobile-icon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="editions-mobile-menu" class="hidden pl-4 mt-2 space-y-2">
                        <a href="{{ route('editions.index') }}" class="block text-gray-600 hover:text-red-800 font-medium text-sm transition-colors">
                            Lista completa
                        </a>
                        <a href="{{ route('editions.gallery') }}" class="block text-gray-600 hover:text-red-800 font-medium text-sm transition-colors">
                            Galeria por década
                        </a>
                    </div>
                </div>
                @if(isset($categories) && $categories->count() > 0)
                    <div>
                        <button id="sections-mobile-btn" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors flex items-center gap-1 w-full">
                            Seções
                            <svg id="sections-mobile-icon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="sections-mobile-menu" class="hidden pl-4 mt-2 space-y-2">
                            @foreach($categories as $category)
                                <a href="{{ route('categories.show', $category->slug) }}" class="block text-gray-600 hover:text-red-800 font-medium text-sm leading-snug transition-colors">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                @auth
                    <div class="flex flex-col space-y-2 mt-2 pt-3 border-t border-gray-300">
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="bg-red-800 text-white px-4 py-2 rounded-md hover:bg-red-900 font-medium text-sm transition-colors shadow-sm text-center">
                                Admin
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="bg-red-800 text-white px-4 py-2 rounded-md hover:bg-red-900 font-medium text-sm transition-colors shadow-sm text-center">
                                Dashboard
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 font-medium text-sm transition-colors shadow-sm">
                                Sair
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ login_url() }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                        Entrar
                    </a>
                    <a href="{{ route('subscriptions.plans') }}" class="bg-red-800 text-white px-6 py-2 rounded hover:bg-red-900 transition-colors font-medium text-sm text-center mt-2">
                        Assinar
                    </a>
                @endauth
            </nav>
        </div>
    </div>
</header>

<script>
    document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });

    // Toggle dropdown de Seções no mobile
    document.getElementById('sections-mobile-btn')?.addEventListener('click', function() {
        const menu = document.getElementById('sections-mobile-menu');
        const icon = document.getElementById('sections-mobile-icon');
        if (menu && icon) {
            menu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    });

    // Toggle dropdown de Edições no mobile
    document.getElementById('editions-mobile-btn')?.addEventListener('click', function() {
        const menu = document.getElementById('editions-mobile-menu');
        const icon = document.getElementById('editions-mobile-icon');
        if (menu && icon) {
            menu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    });
</script>


<header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex items-center justify-between h-20">
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
            <nav class="hidden lg:flex items-center space-x-6">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                    Início
                </a>
                @foreach($categories ?? [] as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                        {{ $category->name }}
                    </a>
                @endforeach
                @auth
                    <div class="flex items-center space-x-2 ml-2 pl-2 border-l border-gray-300">
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
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                        Entrar
                    </a>
                    <a href="#" class="bg-red-800 text-white px-6 py-2 rounded hover:bg-red-900 transition-colors font-medium text-sm">
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
            <nav class="flex flex-col space-y-3 pt-4">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                    Início
                </a>
                @foreach($categories ?? [] as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                        {{ $category->name }}
                    </a>
                @endforeach
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
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-red-800 font-medium text-sm transition-colors">
                        Entrar
                    </a>
                    <a href="#" class="bg-red-800 text-white px-6 py-2 rounded hover:bg-red-900 transition-colors font-medium text-sm text-center mt-2">
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
</script>


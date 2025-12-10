@extends('layouts.app')

@section('title', 'Revista Catolicismo - Assine e Receba em Casa')
@section('description', 'Assine a Revista Catolicismo e receba mensalmente a revista que defende a tradição católica e o jornalismo de qualidade')

@section('content')
    {{-- Faixa de Assinatura Simples --}}
    <section class="bg-red-800 text-white py-4">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-center md:text-left">
                    <span class="text-lg md:text-xl font-medium">Assine a Revista Catolicismo por apenas</span>
                    <span class="text-2xl md:text-3xl font-bold ml-2">R$ 39,90/mês</span>
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-white/10 text-white px-6 py-2 rounded font-medium hover:bg-white/20 transition-colors whitespace-nowrap border border-white/30">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-white/10 text-white px-6 py-2 rounded font-medium hover:bg-white/20 transition-colors whitespace-nowrap border border-white/30">
                            Entrar
                        </a>
                    @endauth
                    <a href="#" class="bg-white text-red-800 px-6 py-2 rounded font-bold hover:bg-gray-100 transition-colors whitespace-nowrap">
                        ASSINE AGORA
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Slider de Revistas Compacto --}}
    <x-revista-slider :revistas="[
        [
            'titulo' => 'Edição 297 - A Vez da Segurança Pública',
            'edicao' => 'Edição 297',
            'data' => '21/11/2025',
            'capa' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=400&h=600&fit=crop&q=80',
            'destaque' => true
        ],
        [
            'titulo' => 'Edição 296 - Tradição e Modernidade',
            'edicao' => 'Edição 296',
            'data' => '21/10/2025',
            'capa' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ],
        [
            'titulo' => 'Edição 295 - A Doutrina Social da Igreja',
            'edicao' => 'Edição 295',
            'data' => '21/09/2025',
            'capa' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ],
        [
            'titulo' => 'Edição 294 - Família e Valores Cristãos',
            'edicao' => 'Edição 294',
            'data' => '21/08/2025',
            'capa' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ],
        [
            'titulo' => 'Edição 293 - Arte Sacra Brasileira',
            'edicao' => 'Edição 293',
            'data' => '21/07/2025',
            'capa' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ],
        [
            'titulo' => 'Edição 292 - Política e Valores Católicos',
            'edicao' => 'Edição 292',
            'data' => '21/06/2025',
            'capa' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ],
        [
            'titulo' => 'Edição 291 - O Papado e a Unidade',
            'edicao' => 'Edição 291',
            'data' => '21/05/2025',
            'capa' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ],
        [
            'titulo' => 'Edição 290 - Vocações Religiosas',
            'edicao' => 'Edição 290',
            'data' => '21/04/2025',
            'capa' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ],
        [
            'titulo' => 'Edição 289 - A Renovação Litúrgica',
            'edicao' => 'Edição 289',
            'data' => '21/03/2025',
            'capa' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ],
        [
            'titulo' => 'Edição 288 - Direitos Humanos e Igreja',
            'edicao' => 'Edição 288',
            'data' => '21/02/2025',
            'capa' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=400&h=600&fit=crop&q=80',
            'destaque' => false
        ]
    ]" />

    {{-- Grid de Destaques --}}
    <section class="bg-white py-16 border-t border-gray-200">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="mb-10">
                <h2 class="text-3xl font-bold text-gray-900 font-serif mb-3">Destaques</h2>
                <div class="w-20 h-1 bg-red-800"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @php
                    $destaques = [
                        [
                            'title' => 'Ação Política e Valores Católicos no Brasil',
                            'excerpt' => 'Análise sobre como os valores católicos influenciam as decisões políticas e a formação da sociedade brasileira.',
                            'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=600&h=400&fit=crop&q=80',
                            'category' => 'Política',
                            'author' => 'Prof. Carlos Mendes',
                            'date' => now()->subDays(0)->format('d/m/Y')
                        ],
                        [
                            'title' => 'O Papel da Igreja na Formação da Juventude',
                            'excerpt' => 'Reflexão sobre a importância da educação católica e da formação espiritual dos jovens na sociedade contemporânea.',
                            'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=600&h=400&fit=crop&q=80',
                            'category' => 'Igreja',
                            'author' => 'Dom Maria Santos',
                            'date' => now()->subDays(1)->format('d/m/Y')
                        ],
                        [
                            'title' => 'Arte Sacra e Tradição Cultural',
                            'excerpt' => 'Explorando a rica tradição da arte sacra católica e sua influência na cultura brasileira ao longo dos séculos.',
                            'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=600&h=400&fit=crop&q=80',
                            'category' => 'Cultura',
                            'author' => 'Dra. Ana Paula',
                            'date' => now()->subDays(2)->format('d/m/Y')
                        ],
                        [
                            'title' => 'Família e Valores Cristãos na Sociedade Moderna',
                            'excerpt' => 'Discussão sobre a importância da família como base da sociedade e os desafios enfrentados na atualidade.',
                            'image' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600&h=400&fit=crop&q=80',
                            'category' => 'Sociedade',
                            'author' => 'Pe. Roberto Alves',
                            'date' => now()->subDays(3)->format('d/m/Y')
                        ]
                    ];
                @endphp
                @foreach($destaques as $destaque)
                    <x-article-card :article="$destaque" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Conteúdo Principal - Duas Colunas --}}
    <section class="bg-gray-50 py-16 border-t border-gray-200">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                {{-- Coluna Esquerda - Artigos Recentes --}}
                <div class="lg:col-span-2">
                    <div class="mb-8 pb-4 border-b-2 border-red-800">
                        <h2 class="text-3xl font-bold text-gray-900 font-serif mb-2">Últimas Notícias</h2>
                        <p class="text-gray-600 text-sm">As principais notícias e análises do momento</p>
                    </div>
                    
                    <div class="space-y-8">
                        @php
                            $noticias = [
                                [
                                    'title' => 'Declaração do Vaticano sobre Questões Contemporâneas',
                                    'excerpt' => 'O Vaticano emite nova declaração abordando temas relevantes da sociedade moderna e a posição da Igreja Católica.',
                                    'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop&q=80',
                                    'category' => 'Política',
                                    'author' => 'Correspondente Vaticano',
                                    'date' => now()->subDays(0)->format('d/m/Y')
                                ],
                                [
                                    'title' => 'Celebração da Missa Tridentina em Cidades Brasileiras',
                                    'excerpt' => 'Cresce o número de paróquias que oferecem a Missa no rito tradicional, refletindo o interesse pela liturgia clássica.',
                                    'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=400&h=300&fit=crop&q=80',
                                    'category' => 'Igreja',
                                    'author' => 'Pe. João Batista',
                                    'date' => now()->subDays(1)->format('d/m/Y')
                                ],
                                [
                                    'title' => 'Exposição de Arte Sacra em Museu Nacional',
                                    'excerpt' => 'Museu inaugura exposição com peças históricas da arte sacra brasileira dos séculos XVII e XVIII.',
                                    'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=400&h=300&fit=crop&q=80',
                                    'category' => 'Cultura',
                                    'author' => 'Crítico de Arte',
                                    'date' => now()->subDays(2)->format('d/m/Y')
                                ],
                                [
                                    'title' => 'Iniciativas Católicas de Apoio às Famílias',
                                    'excerpt' => 'Organizações católicas lançam programas de apoio familiar em diversas regiões do país.',
                                    'image' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=400&h=300&fit=crop&q=80',
                                    'category' => 'Sociedade',
                                    'author' => 'Reporter Social',
                                    'date' => now()->subDays(3)->format('d/m/Y')
                                ],
                                [
                                    'title' => 'Opinião: A Crise de Valores na Sociedade Moderna',
                                    'excerpt' => 'Colunista analisa os desafios morais e éticos enfrentados pela sociedade contemporânea sob a perspectiva católica.',
                                    'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop&q=80',
                                    'category' => 'Opinião',
                                    'author' => 'Prof. Fernando Costa',
                                    'date' => now()->subDays(4)->format('d/m/Y')
                                ],
                                [
                                    'title' => 'Brasil: Crescimento de Vocações Religiosas',
                                    'excerpt' => 'Dados mostram aumento no número de jovens que ingressam em seminários e conventos em todo o território nacional.',
                                    'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=400&h=300&fit=crop&q=80',
                                    'category' => 'Brasil',
                                    'author' => 'Equipe Editorial',
                                    'date' => now()->subDays(5)->format('d/m/Y')
                                ]
                            ];
                        @endphp
                        @foreach($noticias as $index => $noticia)
                            <article class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-100">
                                <x-article-card 
                                    :article="$noticia"
                                    size="horizontal"
                                />
                            </article>
                        @endforeach
                        
                        {{-- Botão Ver Mais --}}
                        <div class="text-center pt-4">
                            <a href="#" class="inline-block bg-red-800 text-white px-8 py-3 rounded-lg hover:bg-red-900 transition-colors font-medium">
                                Ver Todas as Notícias
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="space-y-8">
                    {{-- Newsletter --}}
                    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                        <div class="mb-4 pb-3 border-b border-gray-200">
                            <h3 class="text-xl font-bold text-gray-900 mb-1 font-serif">Newsletter</h3>
                            <p class="text-sm text-gray-600">
                                Receba nossos artigos exclusivos e notícias diretamente em seu e-mail.
                            </p>
                        </div>
                        <form class="space-y-3">
                            <input 
                                type="email" 
                                placeholder="Seu e-mail" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent text-sm"
                            >
                            <button 
                                type="submit" 
                                class="w-full bg-red-800 text-white px-4 py-3 rounded-lg hover:bg-red-900 transition-colors text-sm font-medium"
                            >
                                Assinar Newsletter
                            </button>
                        </form>
                    </div>

                    {{-- Mais Lidas --}}
                    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                        <div class="mb-6 pb-3 border-b-2 border-red-800">
                            <h3 class="text-xl font-bold text-gray-900 font-serif">
                                Mais Lidas
                            </h3>
                        </div>
                        <div class="space-y-5">
                            @php
                                $maisLidas = [
                                    ['title' => 'A Doutrina Social da Igreja e o Mundo Contemporâneo', 'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=150&h=150&fit=crop&q=80', 'date' => now()->subDays(0)->format('d/m/Y')],
                                    ['title' => 'Tradição e Modernidade: O Equilíbrio da Fé Católica', 'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=150&h=150&fit=crop&q=80', 'date' => now()->subDays(1)->format('d/m/Y')],
                                    ['title' => 'O Papado e os Desafios do Século XXI', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=150&h=150&fit=crop&q=80', 'date' => now()->subDays(2)->format('d/m/Y')],
                                    ['title' => 'Família: Base da Sociedade Segundo a Doutrina Católica', 'image' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=150&h=150&fit=crop&q=80', 'date' => now()->subDays(3)->format('d/m/Y')],
                                    ['title' => 'A Beleza da Liturgia Tradicional', 'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=150&h=150&fit=crop&q=80', 'date' => now()->subDays(4)->format('d/m/Y')]
                                ];
                            @endphp
                            @foreach($maisLidas as $index => $artigo)
                                <article class="group cursor-pointer pb-5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                    <a href="#" class="block">
                                        <div class="flex gap-4">
                                            <div class="w-24 h-24 flex-shrink-0 rounded-lg overflow-hidden shadow-sm">
                                                <img 
                                                    src="{{ $artigo['image'] }}" 
                                                    alt="{{ $artigo['title'] }}"
                                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                >
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-xs font-bold text-red-800 mb-1">{{ $index + 1 }}º</div>
                                                <h4 class="text-sm font-bold text-gray-900 mb-2 line-clamp-3 group-hover:text-red-800 transition-colors font-serif leading-snug">
                                                    {{ $artigo['title'] }}
                                                </h4>
                                                <p class="text-xs text-gray-500">
                                                    {{ $artigo['date'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    {{-- Espaço para Anúncios --}}
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-8 rounded-lg border-2 border-dashed border-gray-300 text-center">
                        <p class="text-sm text-gray-500 font-medium">Espaço Publicitário</p>
                        <p class="text-xs text-gray-400 mt-2">300x250</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- Seções por Categoria --}}
    <section class="bg-white py-16 border-t border-gray-200">
        <x-section-block 
            title="Política" 
            :articles="[
                ['title' => 'A Influência dos Valores Católicos na Política Brasileira', 'excerpt' => 'Análise sobre como os princípios da doutrina social da Igreja influenciam as decisões políticas e a formação de políticas públicas no Brasil.', 'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=600&h=400&fit=crop&q=80', 'category' => 'Política', 'author' => 'Prof. Carlos Mendes', 'date' => now()->format('d/m/Y')],
                ['title' => 'O Voto Consciente e a Responsabilidade Cristã', 'excerpt' => 'Reflexão sobre a importância do voto consciente baseado em valores cristãos e o papel do católico na vida política.', 'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=600&h=400&fit=crop&q=80', 'category' => 'Política', 'author' => 'Dr. Paulo Roberto', 'date' => now()->subDays(1)->format('d/m/Y')],
                ['title' => 'Direitos Humanos e Doutrina Social da Igreja', 'excerpt' => 'Como a doutrina social católica fundamenta a defesa dos direitos humanos e a promoção da justiça social.', 'image' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=600&h=400&fit=crop&q=80', 'category' => 'Política', 'author' => 'Dra. Maria Silva', 'date' => now()->subDays(2)->format('d/m/Y')],
            ]"
            :columns="3"
        />
    </section>

    <section class="bg-gray-50 py-16 border-t border-gray-200">
        <x-section-block 
            title="Igreja" 
            :articles="[
                ['title' => 'A Renovação Litúrgica e a Tradição', 'excerpt' => 'Explorando o equilíbrio entre a renovação litúrgica e a preservação das tradições sagradas da Igreja Católica.', 'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=600&h=400&fit=crop&q=80', 'category' => 'Igreja', 'author' => 'Dom João Silva', 'date' => now()->format('d/m/Y')],
                ['title' => 'O Papado e a Unidade da Igreja', 'excerpt' => 'Reflexão sobre o papel do Papa como sucessor de Pedro e guardião da unidade e da doutrina católica.', 'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=400&fit=crop&q=80', 'category' => 'Igreja', 'author' => 'Pe. Antônio Costa', 'date' => now()->subDays(1)->format('d/m/Y')],
                ['title' => 'Vocações Religiosas no Brasil Contemporâneo', 'excerpt' => 'Análise do crescimento das vocações religiosas e o papel dos seminários na formação dos futuros sacerdotes.', 'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=600&h=400&fit=crop&q=80', 'category' => 'Igreja', 'author' => 'Dom Maria Santos', 'date' => now()->subDays(2)->format('d/m/Y')],
            ]"
            :columns="3"
        />
    </section>

    <section class="bg-white py-16 border-t border-gray-200">
        <x-section-block 
            title="Cultura" 
            :articles="[
                ['title' => 'Arte Sacra Brasileira: Patrimônio Cultural', 'excerpt' => 'Explorando a rica tradição da arte sacra no Brasil, desde o período colonial até os dias atuais.', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=600&h=400&fit=crop&q=80', 'category' => 'Cultura', 'author' => 'Dra. Ana Paula', 'date' => now()->format('d/m/Y')],
                ['title' => 'Música Sacra e Tradição Litúrgica', 'excerpt' => 'A importância da música sacra na liturgia católica e sua evolução ao longo dos séculos.', 'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&h=400&fit=crop&q=80', 'category' => 'Cultura', 'author' => 'Maestro Roberto', 'date' => now()->subDays(1)->format('d/m/Y')],
                ['title' => 'Literatura Católica e Formação Intelectual', 'excerpt' => 'Como a literatura católica contribui para a formação intelectual e espiritual dos fiéis.', 'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=600&h=400&fit=crop&q=80', 'category' => 'Cultura', 'author' => 'Prof. Fernando', 'date' => now()->subDays(2)->format('d/m/Y')],
            ]"
            :columns="3"
        />
    </section>
@endsection


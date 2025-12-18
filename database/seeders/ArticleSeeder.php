<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar ou criar usuário admin
        $adminUser = User::where('role', 'admin')->first();
        if (!$adminUser) {
            $adminUser = User::where('email', 'admin@catolicismo.com')->first();
        }
        if (!$adminUser) {
            $adminUser = User::first();
        }

        // Criar categorias
        $categories = [
            'Política' => 'Artigos sobre política e doutrina social da Igreja',
            'Igreja' => 'Artigos sobre a Igreja Católica, liturgia e tradição',
            'Cultura' => 'Artigos sobre arte sacra, música e literatura católica',
            'Sociedade' => 'Artigos sobre sociedade, família e valores cristãos',
            'Opinião' => 'Artigos de opinião e análise',
            'Brasil' => 'Artigos sobre o catolicismo no Brasil',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $description) {
            $categoryModels[$name] = Category::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($name)],
                ['name' => $name, 'description' => $description]
            );
        }

        // Artigos de Destaques
        $destaques = [
            [
                'title' => 'Ação Política e Valores Católicos no Brasil',
                'description' => 'Análise sobre como os valores católicos influenciam as decisões políticas e a formação da sociedade brasileira.',
                'content' => 'Análise sobre como os valores católicos influenciam as decisões políticas e a formação da sociedade brasileira.',
                'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=600&h=400&fit=crop&q=80',
                'category' => 'Política',
                'author' => 'Prof. Carlos Mendes',
                'published_at' => now()->subDays(0),
            ],
            [
                'title' => 'O Papel da Igreja na Formação da Juventude',
                'description' => 'Reflexão sobre a importância da educação católica e da formação espiritual dos jovens na sociedade contemporânea.',
                'content' => 'Reflexão sobre a importância da educação católica e da formação espiritual dos jovens na sociedade contemporânea.',
                'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=600&h=400&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Dom Maria Santos',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Arte Sacra e Tradição Cultural',
                'description' => 'Explorando a rica tradição da arte sacra católica e sua influência na cultura brasileira ao longo dos séculos.',
                'content' => 'Explorando a rica tradição da arte sacra católica e sua influência na cultura brasileira ao longo dos séculos.',
                'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=600&h=400&fit=crop&q=80',
                'category' => 'Cultura',
                'author' => 'Dra. Ana Paula',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Família e Valores Cristãos na Sociedade Moderna',
                'description' => 'Discussão sobre a importância da família como base da sociedade e os desafios enfrentados na atualidade.',
                'content' => 'Discussão sobre a importância da família como base da sociedade e os desafios enfrentados na atualidade.',
                'image' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600&h=400&fit=crop&q=80',
                'category' => 'Sociedade',
                'author' => 'Pe. Roberto Alves',
                'published_at' => now()->subDays(3),
            ],
        ];

        // Artigos de Últimas Notícias (removendo duplicatas)
        $noticias = [
            [
                'title' => 'Declaração do Vaticano sobre Questões Contemporâneas',
                'description' => 'O Vaticano emite nova declaração abordando temas relevantes da sociedade moderna e a posição da Igreja Católica.',
                'content' => 'O Vaticano emite nova declaração abordando temas relevantes da sociedade moderna e a posição da Igreja Católica.',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop&q=80',
                'category' => 'Política',
                'author' => 'Correspondente Vaticano',
                'published_at' => now()->subDays(0),
            ],
            [
                'title' => 'Celebração da Missa Tridentina em Cidades Brasileiras',
                'description' => 'Cresce o número de paróquias que oferecem a Missa no rito tradicional, refletindo o interesse pela liturgia clássica.',
                'content' => 'Cresce o número de paróquias que oferecem a Missa no rito tradicional, refletindo o interesse pela liturgia clássica.',
                'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=400&h=300&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Pe. João Batista',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Exposição de Arte Sacra em Museu Nacional',
                'description' => 'Museu inaugura exposição com peças históricas da arte sacra brasileira dos séculos XVII e XVIII.',
                'content' => 'Museu inaugura exposição com peças históricas da arte sacra brasileira dos séculos XVII e XVIII.',
                'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=400&h=300&fit=crop&q=80',
                'category' => 'Cultura',
                'author' => 'Crítico de Arte',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Iniciativas Católicas de Apoio às Famílias',
                'description' => 'Organizações católicas lançam programas de apoio familiar em diversas regiões do país.',
                'content' => 'Organizações católicas lançam programas de apoio familiar em diversas regiões do país.',
                'image' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=400&h=300&fit=crop&q=80',
                'category' => 'Sociedade',
                'author' => 'Reporter Social',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Opinião: A Crise de Valores na Sociedade Moderna',
                'description' => 'Colunista analisa os desafios morais e éticos enfrentados pela sociedade contemporânea sob a perspectiva católica.',
                'content' => 'Colunista analisa os desafios morais e éticos enfrentados pela sociedade contemporânea sob a perspectiva católica.',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop&q=80',
                'category' => 'Opinião',
                'author' => 'Prof. Fernando Costa',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Brasil: Crescimento de Vocações Religiosas',
                'description' => 'Dados mostram aumento no número de jovens que ingressam em seminários e conventos em todo o território nacional.',
                'content' => 'Dados mostram aumento no número de jovens que ingressam em seminários e conventos em todo o território nacional.',
                'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=400&h=300&fit=crop&q=80',
                'category' => 'Brasil',
                'author' => 'Equipe Editorial',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'A Doutrina Social da Igreja',
                'description' => 'Como os princípios católicos orientam a ação social e política no mundo contemporâneo.',
                'content' => 'Como os princípios católicos orientam a ação social e política no mundo contemporâneo.',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop&q=80',
                'category' => 'Política',
                'author' => 'Prof. Fernando',
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'Liturgia Tradicional',
                'description' => 'A importância da preservação dos ritos tradicionais da Igreja Católica.',
                'content' => 'A importância da preservação dos ritos tradicionais da Igreja Católica.',
                'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=400&h=300&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Pe. Antônio',
                'published_at' => now()->subDays(7),
            ],
        ];

        // Artigos de Mais Lidas
        $maisLidas = [
            [
                'title' => 'A Doutrina Social da Igreja e o Mundo Contemporâneo',
                'description' => 'Análise sobre como a doutrina social da Igreja se aplica aos desafios do mundo contemporâneo.',
                'content' => 'Análise sobre como a doutrina social da Igreja se aplica aos desafios do mundo contemporâneo.',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=150&h=150&fit=crop&q=80',
                'category' => 'Política',
                'author' => 'Prof. Carlos Mendes',
                'published_at' => now()->subDays(0),
            ],
            [
                'title' => 'Tradição e Modernidade: O Equilíbrio da Fé Católica',
                'description' => 'Reflexão sobre como a Igreja equilibra tradição e modernidade na prática da fé.',
                'content' => 'Reflexão sobre como a Igreja equilibra tradição e modernidade na prática da fé.',
                'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=150&h=150&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Dom Maria Santos',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'O Papado e os Desafios do Século XXI',
                'description' => 'Análise sobre o papel do papado e os desafios enfrentados pela Igreja no século XXI.',
                'content' => 'Análise sobre o papel do papado e os desafios enfrentados pela Igreja no século XXI.',
                'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=150&h=150&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Pe. Antônio Costa',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Família: Base da Sociedade Segundo a Doutrina Católica',
                'description' => 'Discussão sobre a importância da família como fundamento da sociedade segundo a doutrina católica.',
                'content' => 'Discussão sobre a importância da família como fundamento da sociedade segundo a doutrina católica.',
                'image' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=150&h=150&fit=crop&q=80',
                'category' => 'Sociedade',
                'author' => 'Pe. Roberto Alves',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'A Beleza da Liturgia Tradicional',
                'description' => 'Explorando a beleza e profundidade da liturgia tradicional da Igreja Católica.',
                'content' => 'Explorando a beleza e profundidade da liturgia tradicional da Igreja Católica.',
                'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=150&h=150&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Pe. João Batista',
                'published_at' => now()->subDays(4),
            ],
        ];

        // Artigos das seções por categoria
        $artigosPolitica = [
            [
                'title' => 'A Influência dos Valores Católicos na Política Brasileira',
                'description' => 'Análise sobre como os princípios da doutrina social da Igreja influenciam as decisões políticas e a formação de políticas públicas no Brasil.',
                'content' => 'Análise sobre como os princípios da doutrina social da Igreja influenciam as decisões políticas e a formação de políticas públicas no Brasil.',
                'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=600&h=400&fit=crop&q=80',
                'category' => 'Política',
                'author' => 'Prof. Carlos Mendes',
                'published_at' => now(),
            ],
            [
                'title' => 'O Voto Consciente e a Responsabilidade Cristã',
                'description' => 'Reflexão sobre a importância do voto consciente baseado em valores cristãos e o papel do católico na vida política.',
                'content' => 'Reflexão sobre a importância do voto consciente baseado em valores cristãos e o papel do católico na vida política.',
                'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=600&h=400&fit=crop&q=80',
                'category' => 'Política',
                'author' => 'Dr. Paulo Roberto',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Direitos Humanos e Doutrina Social da Igreja',
                'description' => 'Como a doutrina social católica fundamenta a defesa dos direitos humanos e a promoção da justiça social.',
                'content' => 'Como a doutrina social católica fundamenta a defesa dos direitos humanos e a promoção da justiça social.',
                'image' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=600&h=400&fit=crop&q=80',
                'category' => 'Política',
                'author' => 'Dra. Maria Silva',
                'published_at' => now()->subDays(2),
            ],
        ];

        $artigosIgreja = [
            [
                'title' => 'A Renovação Litúrgica e a Tradição',
                'description' => 'Explorando o equilíbrio entre a renovação litúrgica e a preservação das tradições sagradas da Igreja Católica.',
                'content' => 'Explorando o equilíbrio entre a renovação litúrgica e a preservação das tradições sagradas da Igreja Católica.',
                'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=600&h=400&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Dom João Silva',
                'published_at' => now(),
            ],
            [
                'title' => 'O Papado e a Unidade da Igreja',
                'description' => 'Reflexão sobre o papel do Papa como sucessor de Pedro e guardião da unidade e da doutrina católica.',
                'content' => 'Reflexão sobre o papel do Papa como sucessor de Pedro e guardião da unidade e da doutrina católica.',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=400&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Pe. Antônio Costa',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Vocações Religiosas no Brasil Contemporâneo',
                'description' => 'Análise do crescimento das vocações religiosas e o papel dos seminários na formação dos futuros sacerdotes.',
                'content' => 'Análise do crescimento das vocações religiosas e o papel dos seminários na formação dos futuros sacerdotes.',
                'image' => 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=600&h=400&fit=crop&q=80',
                'category' => 'Igreja',
                'author' => 'Dom Maria Santos',
                'published_at' => now()->subDays(2),
            ],
        ];

        $artigosCultura = [
            [
                'title' => 'Arte Sacra Brasileira: Patrimônio Cultural',
                'description' => 'Explorando a rica tradição da arte sacra no Brasil, desde o período colonial até os dias atuais.',
                'content' => 'Explorando a rica tradição da arte sacra no Brasil, desde o período colonial até os dias atuais.',
                'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=600&h=400&fit=crop&q=80',
                'category' => 'Cultura',
                'author' => 'Dra. Ana Paula',
                'published_at' => now(),
            ],
            [
                'title' => 'Música Sacra e Tradição Litúrgica',
                'description' => 'A importância da música sacra na liturgia católica e sua evolução ao longo dos séculos.',
                'content' => 'A importância da música sacra na liturgia católica e sua evolução ao longo dos séculos.',
                'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&h=400&fit=crop&q=80',
                'category' => 'Cultura',
                'author' => 'Maestro Roberto',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Literatura Católica e Formação Intelectual',
                'description' => 'Como a literatura católica contribui para a formação intelectual e espiritual dos fiéis.',
                'content' => 'Como a literatura católica contribui para a formação intelectual e espiritual dos fiéis.',
                'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=600&h=400&fit=crop&q=80',
                'category' => 'Cultura',
                'author' => 'Prof. Fernando',
                'published_at' => now()->subDays(2),
            ],
        ];

        // Combinar todos os artigos
        $todosArtigos = array_merge(
            $destaques,
            $noticias,
            $maisLidas,
            $artigosPolitica,
            $artigosIgreja,
            $artigosCultura
        );

        // Remover duplicatas baseado no título
        $artigosUnicos = [];
        $titulosVistos = [];
        foreach ($todosArtigos as $artigo) {
            if (!in_array($artigo['title'], $titulosVistos)) {
                $artigosUnicos[] = $artigo;
                $titulosVistos[] = $artigo['title'];
            }
        }

        // Criar artigos no banco
        foreach ($artigosUnicos as $artigoData) {
            $category = $categoryModels[$artigoData['category']] ?? null;

            Article::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($artigoData['title'])],
                [
                    'user_id' => $adminUser->id,
                    'category_id' => $category ? $category->id : null,
                    'title' => $artigoData['title'],
                    'description' => $artigoData['description'],
                    'content' => $artigoData['content'],
                    'image' => $artigoData['image'],
                    'category' => $artigoData['category'],
                    'author' => $artigoData['author'],
                    'published' => true,
                    'published_at' => $artigoData['published_at'],
                ]
            );
        }

        $this->command->info('Categorias e artigos criados com sucesso!');
    }
}

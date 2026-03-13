@extends('layouts.app')

@section('title', 'Sobre nós - Revista Catolicismo')
@section('description', 'Sobre nós - Revista Catolicismo')

@section('content')
<div class="relative min-h-screen bg-gray-50 py-12">
    <img
        src="{{ asset('img/textura.jpeg') }}"
        alt=""
        class="absolute inset-0 w-full h-full object-cover"
    >
    <div class="relative z-10 container mx-auto px-4 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white/90 rounded-lg shadow-md overflow-hidden">
            <div class="p-8 md:p-12">
                    <div class="mb-8 pb-6 border-b-2 border-red-800">
                        <h1 class="text-4xl font-bold text-gray-900 font-serif mb-4">Sobre nós – Revista Catolicismo</h1>
                    </div>

                    <div class="prose prose-lg max-w-none">
                        <p class="text-gray-700 mb-4">
                            Fundada em 1951, a revista Catolicismo é uma das publicações católicas de linha tradicional mais longevas do Brasil, mantendo-se em circulação ininterrupta há mais de 70 anos. Ao longo de sua história, foi profundamente marcada pela influência de Plinio Corrêa de Oliveira, que a impulsionou como um órgão de formação doutrinária, de combate de ideias e de defesa intransigente da Civilização Cristã.
                        </p>
                        <p class="text-gray-700 mb-4">
                            Desde a primeira edição, Catolicismo se propõe a ser muito mais do que um simples periódico religioso sobre assuntos eclesiásticos: é um veículo de análise e reflexão que procura iluminar tanto os acontecimentos da Igreja quanto os da sociedade temporal à luz do Magistério tradicional da Igreja Católica. Em suas páginas, temas como moral, família, educação, política, geopolítica, cultura, arte, história, nobreza e tradição são abordados dentro de uma perspectiva contra-revolucionária, ou seja, em oposição às correntes ideológicas igualitárias, laicistas e anticristãs que procuram demolir os restos da Cristandade.
                        </p>
                        <p class="text-gray-700 mb-4">
                            Ao longo das décadas, a revista destacou-se por denunciar com clareza os erros do comunismo, do socialismo, do progressismo teológico e das diversas formas de relativismo moral e doutrinário. Ao mesmo tempo, procura apresentar e exaltar os grandes modelos da História da Igreja e da Civilização Cristã: santos, mártires, estadistas católicos, famílias nobres, instituições tradicionais e exemplos de fidelidade heroica à fé.
                        </p>
                        <p class="text-gray-700 mb-4">
                            Catolicismo também ficou conhecida por divulgar e aprofundar temas como:
                        </p>
                        <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
                            <li>a devoção ao Sagrado Coração de Jesus e ao Imaculado Coração de Maria;</li>
                            <li>a importância da Nobreza e das elites tradicionais na harmonia da vida social;</li>
                            <li>a necessidade de restaurar a ordem cristã na sociedade, no espírito do “Reino de Maria” profetizado por grandes santos;</li>
                            <li>as “verdades esquecidas” da doutrina católica, frequentemente silenciadas pelo progressismo religioso contemporâneo.</li>
                        </ul>
                        <p class="text-gray-700 mb-4">
                            Em 1959, a revista teve a honra de ser o primeiro veículo a publicar o ensaio “Revolução e Contra-Revolução”, de Plinio Corrêa de Oliveira, obra que se tornaria uma referência internacional para a compreensão do processo revolucionário moderno e para a ação católica de resistência doutrinária e cultural.
                        </p>
                        <p class="text-gray-700 mb-4">
                            Fiel à sua missão original, Catolicismo continua, mês a mês, a oferecer análises, estudos históricos, artigos doutrinários e comentários de atualidade voltados para aqueles que desejam conservar e aprofundar a fé católica em toda a sua integridade e lutar para que ela inspire as instituições e costumes da sociedade. Sem concessões ao espírito do século, a revista busca servir à maior glória de Deus, à exaltação da Santa Igreja Católica Apostólica Romana e à preservação dos valores perenes da Civilização Cristã.
                        </p>
                    </div>
            </div>
        </div>
    </div>
</div>
@endsection

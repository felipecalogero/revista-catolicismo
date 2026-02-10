@extends('layouts.app')

@section('title', 'Política de Privacidade - Revista Catolicismo')
@section('description', 'Política de Privacidade da Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8 md:p-12">
            <div class="mb-8 pb-6 border-b-2 border-red-800">
                <h1 class="text-4xl font-bold text-gray-900 font-serif mb-4">Política de Privacidade</h1>
                <p class="text-gray-600">Última atualização: {{ date('d/m/Y') }}</p>
            </div>

            <div class="prose prose-lg max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">1. Informações que Coletamos</h2>
                    <p class="text-gray-700 mb-4">
                        A Revista Catolicismo coleta informações pessoais quando você se cadastra em nosso site, assina nossa revista, 
                        ou interage com nossos serviços. As informações coletadas podem incluir:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
                        <li>Nome completo</li>
                        <li>Endereço de e-mail</li>
                        <li>Endereço postal (para assinaturas físicas)</li>
                        <li>Informações de pagamento (processadas de forma segura por nossos parceiros)</li>
                        <li>Dados de navegação e preferências</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">2. Como Utilizamos suas Informações</h2>
                    <p class="text-gray-700 mb-4">
                        Utilizamos suas informações pessoais para:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
                        <li>Processar e gerenciar sua assinatura</li>
                        <li>Enviar edições da revista e conteúdo relacionado</li>
                        <li>Melhorar nossos serviços e experiência do usuário</li>
                        <li>Comunicar-nos com você sobre sua conta e assinatura</li>
                        <li>Cumprir obrigações legais e regulatórias</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">3. Compartilhamento de Informações</h2>
                    <p class="text-gray-700 mb-4">
                        Não vendemos, alugamos ou compartilhamos suas informações pessoais com terceiros, exceto:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
                        <li>Com prestadores de serviços que nos auxiliam na operação do site e processamento de pagamentos</li>
                        <li>Quando exigido por lei ou processo legal</li>
                        <li>Para proteger nossos direitos e segurança</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">4. Segurança dos Dados</h2>
                    <p class="text-gray-700 mb-4">
                        Implementamos medidas de segurança técnicas e organizacionais adequadas para proteger suas informações 
                        pessoais contra acesso não autorizado, alteração, divulgação ou destruição.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">5. Seus Direitos</h2>
                    <p class="text-gray-700 mb-4">
                        Você tem o direito de:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
                        <li>Acessar suas informações pessoais</li>
                        <li>Corrigir informações incorretas</li>
                        <li>Solicitar a exclusão de suas informações</li>
                        <li>Opor-se ao processamento de suas informações</li>
                        <li>Solicitar a portabilidade de seus dados</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">6. Cookies</h2>
                    <p class="text-gray-700 mb-4">
                        Utilizamos cookies e tecnologias similares para melhorar sua experiência em nosso site. 
                        Você pode gerenciar suas preferências de cookies através das configurações do seu navegador.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">7. Alterações nesta Política</h2>
                    <p class="text-gray-700 mb-4">
                        Podemos atualizar esta Política de Privacidade periodicamente. Notificaremos você sobre 
                        alterações significativas publicando a nova política em nosso site.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">8. Contato</h2>
                    <p class="text-gray-700 mb-4">
                        Se você tiver dúvidas sobre esta Política de Privacidade, entre em contato conosco através 
                        do e-mail: <a href="mailto:contato@revistacatolicismo.com.br" class="text-red-800 hover:text-red-900">contato@revistacatolicismo.com.br</a>
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

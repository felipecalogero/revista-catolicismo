@extends('layouts.app')

@section('title', 'Termos de Uso - Revista Catolicismo')
@section('description', 'Termos de Uso da Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8 md:p-12">
            <div class="mb-8 pb-6 border-b-2 border-red-800">
                <h1 class="text-4xl font-bold text-gray-900 font-serif mb-4">Termos de Uso</h1>
                <p class="text-gray-600">Última atualização: {{ date('d/m/Y') }}</p>
            </div>

            <div class="prose prose-lg max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">1. Aceitação dos Termos</h2>
                    <p class="text-gray-700 mb-4">
                        Ao acessar e utilizar o site da Revista Catolicismo, você concorda em cumprir e estar vinculado 
                        a estes Termos de Uso. Se você não concorda com qualquer parte destes termos, não deve utilizar nosso site.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">2. Uso do Site</h2>
                    <p class="text-gray-700 mb-4">
                        Você concorda em utilizar o site apenas para fins legais e de acordo com estes Termos de Uso. 
                        É proibido:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
                        <li>Utilizar o site de forma que viole qualquer lei ou regulamento</li>
                        <li>Reproduzir, duplicar ou copiar conteúdo sem autorização</li>
                        <li>Interferir ou interromper o funcionamento do site</li>
                        <li>Tentar obter acesso não autorizado a qualquer parte do site</li>
                        <li>Transmitir vírus ou código malicioso</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">3. Propriedade Intelectual</h2>
                    <p class="text-gray-700 mb-4">
                        Todo o conteúdo do site, incluindo textos, imagens, logotipos, gráficos e software, é propriedade 
                        da Revista Catolicismo ou de seus licenciadores e está protegido por leis de direitos autorais e 
                        outras leis de propriedade intelectual.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">4. Assinaturas</h2>
                    <p class="text-gray-700 mb-4">
                        Ao assinar a Revista Catolicismo, você concorda em:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
                        <li>Fornecer informações precisas e atualizadas</li>
                        <li>Manter a segurança de sua conta</li>
                        <li>Pagar todas as taxas associadas à sua assinatura</li>
                        <li>Respeitar os termos de cancelamento e reembolso</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">5. Conteúdo de Usuários</h2>
                    <p class="text-gray-700 mb-4">
                        Se você enviar comentários, feedback ou outro conteúdo ao site, você concede à Revista Catolicismo 
                        uma licença não exclusiva para usar, reproduzir e modificar esse conteúdo para fins de operação e 
                        promoção do site.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">6. Limitação de Responsabilidade</h2>
                    <p class="text-gray-700 mb-4">
                        A Revista Catolicismo não será responsável por quaisquer danos diretos, indiretos, incidentais ou 
                        consequenciais resultantes do uso ou incapacidade de usar o site ou seu conteúdo.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">7. Links para Sites de Terceiros</h2>
                    <p class="text-gray-700 mb-4">
                        Nosso site pode conter links para sites de terceiros. Não somos responsáveis pelo conteúdo ou 
                        práticas de privacidade desses sites externos.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">8. Modificações dos Termos</h2>
                    <p class="text-gray-700 mb-4">
                        Reservamo-nos o direito de modificar estes Termos de Uso a qualquer momento. As alterações entrarão 
                        em vigor imediatamente após a publicação no site. É sua responsabilidade revisar periodicamente estes termos.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">9. Lei Aplicável</h2>
                    <p class="text-gray-700 mb-4">
                        Estes Termos de Uso são regidos pelas leis do Brasil. Qualquer disputa relacionada a estes termos 
                        será resolvida nos tribunais competentes do Brasil.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">10. Contato</h2>
                    <p class="text-gray-700 mb-4">
                        Se você tiver dúvidas sobre estes Termos de Uso, entre em contato conosco através do e-mail: 
                        <a href="mailto:contato@revistacatolicismo.com.br" class="text-red-800 hover:text-red-900">contato@revistacatolicismo.com.br</a>
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

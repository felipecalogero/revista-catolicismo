@extends('layouts.app')

@section('title', 'Planos de Assinatura - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 lg:px-8">
        {{-- Mensagens de Erro/Sucesso --}}
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <strong>Erro:</strong> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 font-serif mb-4">
                Escolha seu Plano
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Assine a Revista Catolicismo e tenha acesso completo ao conteúdo que defende a tradição católica
            </p>
            <div class="w-24 h-1 bg-red-800 mx-auto mt-6"></div>
        </div>

        {{-- Planos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto mb-12">
            @foreach($plans as $key => $plan)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 {{ $key === 'physical' ? 'border-red-800' : 'border-gray-200' }} hover:shadow-xl transition-shadow">
                    {{-- Badge Destaque --}}
                    @if($key === 'physical')
                        <div class="bg-red-800 text-white text-center py-2 text-sm font-bold">
                            Mais Popular
                        </div>
                    @endif

                    <div class="p-8">
                        {{-- Tipo de Plano --}}
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 font-serif mb-2">
                                {{ $plan['name'] }}
                            </h3>
                            <p class="text-gray-600 text-sm">
                                {{ $plan['description'] }}
                            </p>
                        </div>

                        {{-- Preço --}}
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <div class="flex items-baseline">
                                <span class="text-5xl font-bold text-gray-900">R$</span>
                                <span class="text-5xl font-bold text-gray-900 ml-1">{{ number_format($plan['amount'], 2, ',', '.') }}</span>
                            </div>
                            <p class="text-gray-500 text-sm mt-2">por ano (12 meses)</p>
                        </div>

                        {{-- Benefícios --}}
                        <ul class="space-y-4 mb-8">
                            @if($key === 'virtual')
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Acesso digital completo a todas as edições</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Download de PDFs em alta qualidade</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Acesso imediato após confirmação</span>
                                </li>
                            @else
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Acesso digital completo a todas as edições</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Revista física enviada mensalmente</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Entrega em todo o Brasil</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Download de PDFs em alta qualidade</span>
                                </li>
                            @endif
                        </ul>

                        {{-- Botão --}}
                        @auth
                            <form action="{{ route('subscriptions.create') }}" method="POST" id="subscription-form-{{ $key }}">
                                @csrf
                                <input type="hidden" name="plan_type" value="{{ $key }}">
                                <button type="submit" 
                                    class="w-full bg-red-800 text-white px-6 py-4 rounded-lg text-lg font-bold hover:bg-red-900 transition-colors shadow-md hover:shadow-lg">
                                    Assinar Agora
                                </button>
                            </form>
                            <script>
                                // Intercepta submit do formulário para criar assinatura via AJAX
                                document.getElementById('subscription-form-{{ $key }}').addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    const form = this;
                                    const button = form.querySelector('button[type="submit"]');
                                    const originalText = button.textContent;
                                    
                                    // Desabilita botão durante processamento
                                    button.disabled = true;
                                    button.textContent = 'Processando...';
                                    
                                    // Cria assinatura no banco via AJAX
                                    fetch(form.action, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                                        },
                                        body: new URLSearchParams(new FormData(form))
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Erro na requisição');
                                        }
                                        // Verifica se é HTML ou JSON
                                        const contentType = response.headers.get('content-type');
                                        if (contentType && contentType.includes('text/html')) {
                                            // É HTML - escreve o HTML na página atual
                                            // Isso evita redirecionar para rota POST que dá 404
                                            return response.text().then(html => {
                                                document.open();
                                                document.write(html);
                                                document.close();
                                            });
                                        } else {
                                            // É JSON (fallback)
                                            return response.json().then(data => {
                                                if (data && data.redirect_url) {
                                                    window.location.href = data.redirect_url;
                                                }
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Erro ao processar:', error);
                                        // Fallback: redireciona para página de planos com erro
                                        window.location.href = '{{ route("subscriptions.plans") }}';
                                    })
                                    .finally(() => {
                                        // Reabilita botão (caso não tenha redirecionado)
                                        button.disabled = false;
                                        button.textContent = originalText;
                                    });
                                });
                            </script>
                        @else
                            <a href="{{ route('login') }}" 
                                class="w-full bg-red-800 text-white px-6 py-4 rounded-lg text-lg font-bold hover:bg-red-900 transition-colors shadow-md hover:shadow-lg text-center block">
                                Fazer Login para Assinar
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Informações Adicionais --}}
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-900 font-serif mb-6 text-center">
                Informações Importantes
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div>
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Pagamento Seguro</h3>
                    <p class="text-sm text-gray-600">Processado pelo PagBank com segurança</p>
                </div>
                <div>
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Cancele Quando Quiser</h3>
                    <p class="text-sm text-gray-600">Sem taxas de cancelamento</p>
                </div>
                <div>
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Acesso Imediato</h3>
                    <p class="text-sm text-gray-600">Após confirmação do pagamento</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

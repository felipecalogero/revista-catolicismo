@extends('layouts.app')

@section('title', 'Minha Assinatura - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 font-serif mb-2">
                Minha Assinatura
            </h1>
            <div class="w-24 h-1 bg-red-800"></div>
        </div>

        @if($subscription)
            {{-- Card Principal da Assinatura --}}
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                {{-- Status e Tipo --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 pb-6 border-b border-gray-200">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 font-serif mb-2">
                            {{ $subscription->plan_type === 'virtual' ? 'Assinatura Virtual Anual' : 'Assinatura Física Anual' }}
                        </h2>
                        <p class="text-gray-600">
                            {{ $subscription->plan_type === 'virtual' ? 'Acesso digital completo' : 'Acesso digital + revista física' }}
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        @if($subscription->isActive())
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Ativa
                            </span>
                        @elseif($subscription->isPending())
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Pendente
                            </span>
                        @elseif($subscription->isPaused())
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Pausada
                            </span>
                        @elseif($subscription->isCancelled())
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Cancelada
                            </span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Informações da Assinatura --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Tipo de Assinatura</h3>
                        <p class="text-lg font-bold text-gray-900">
                            {{ $subscription->plan_type === 'virtual' ? 'Virtual' : 'Física' }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $subscription->plan_type === 'virtual' ? 'Apenas acesso digital' : 'Digital + revista física' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Status</h3>
                        <p class="text-lg font-bold text-gray-900">
                            {{ ucfirst($subscription->status) }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            @if($subscription->isActive())
                                Sua assinatura está ativa e funcionando
                            @elseif($subscription->isPending())
                                Aguardando confirmação do pagamento
                            @elseif($subscription->isPaused())
                                Assinatura temporariamente pausada
                            @elseif($subscription->isCancelled())
                                Assinatura cancelada
                            @else
                                Status: {{ $subscription->status }}
                            @endif
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Data de Compra</h3>
                        <p class="text-lg font-bold text-gray-900">
                            {{ $subscription->purchase_date ? $subscription->purchase_date->format('d/m/Y') : 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            Data em que a assinatura foi adquirida
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Data de Expiração</h3>
                        <p class="text-lg font-bold text-gray-900">
                            {{ $subscription->end_date ? $subscription->end_date->format('d/m/Y') : 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            @if($subscription->end_date && $subscription->end_date->isFuture())
                                Expira em {{ $subscription->end_date->diffForHumans() }}
                            @elseif($subscription->end_date && $subscription->end_date->isPast())
                                Expirada
                            @else
                                Não definida
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Ações --}}
                <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                    @if($subscription->isActive())
                        <form action="{{ route('subscriptions.suspend') }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" 
                                class="w-full bg-yellow-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-yellow-700 transition-colors">
                                Suspender Assinatura
                            </button>
                        </form>
                        <form action="{{ route('subscriptions.cancel') }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" 
                                onclick="return confirm('Tem certeza que deseja cancelar sua assinatura?')"
                                class="w-full bg-red-800 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-900 transition-colors">
                                Cancelar Assinatura
                            </button>
                        </form>
                    @elseif($subscription->isPaused())
                        <form action="{{ route('subscriptions.activate') }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" 
                                class="w-full bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 transition-colors">
                                Reativar Assinatura
                            </button>
                        </form>
                        <form action="{{ route('subscriptions.cancel') }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" 
                                onclick="return confirm('Tem certeza que deseja cancelar sua assinatura?')"
                                class="w-full bg-red-800 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-900 transition-colors">
                                Cancelar Assinatura
                            </button>
                        </form>
                    @elseif($subscription->isCancelled())
                        <a href="{{ route('subscriptions.plans') }}" 
                            class="w-full bg-red-800 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-900 transition-colors text-center block">
                            Assinar Novamente
                        </a>
                    @else
                        <p class="text-gray-600 text-sm">
                            Aguardando processamento do pagamento. Você receberá uma notificação quando sua assinatura for ativada.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Mensagens de Sucesso/Erro --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Informações Adicionais --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 font-serif mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Informações
                </h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Sua assinatura é renovada automaticamente anualmente</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Você pode cancelar a qualquer momento sem taxas</span>
                    </li>
                    @if($subscription->plan_type === 'physical')
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>As revistas físicas são enviadas mensalmente para o endereço cadastrado</span>
                        </li>
                    @endif
                </ul>
            </div>
        @else
            {{-- Sem Assinatura --}}
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 font-serif mb-4">
                        Você ainda não possui uma assinatura
                    </h2>
                    <p class="text-gray-600 mb-8">
                        Assine agora e tenha acesso completo a todas as edições da Revista Catolicismo
                    </p>
                    <a href="{{ route('subscriptions.plans') }}" 
                        class="inline-block bg-red-800 text-white px-8 py-4 rounded-lg text-lg font-bold hover:bg-red-900 transition-colors shadow-md hover:shadow-lg">
                        Ver Planos Disponíveis
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

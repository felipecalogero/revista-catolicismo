@extends('layouts.app')

@section('title', 'Painel Administrativo - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Painel Administrativo
                </h1>
                <p class="text-gray-600">Bem-vindo, {{ Auth::user()->name }} (Administrador)</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 font-serif">Usuários</h3>
                    <p class="text-3xl font-bold text-red-800 mb-2">{{ $totalUsers }}</p>
                    <p class="text-gray-600 text-sm">Total de usuários</p>
                </div>

                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 font-serif">Assinaturas</h3>
                    <p class="text-3xl font-bold text-blue-800 mb-2">{{ $totalSubscriptions }}</p>
                    <p class="text-gray-600 text-sm">Assinaturas ativas</p>
                </div>

                <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 font-serif">Edições</h3>
                    <p class="text-3xl font-bold text-green-800 mb-2">{{ $totalEditions }}</p>
                    <p class="text-gray-600 text-sm">Edições publicadas</p>
                </div>

                <div class="bg-yellow-50 p-6 rounded-lg border border-yellow-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 font-serif">Artigos</h3>
                    <p class="text-3xl font-bold text-yellow-800 mb-2">{{ $totalArticles }}</p>
                    <p class="text-gray-600 text-sm">Artigos publicados</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 font-serif">Ações Rápidas</h3>
                    <div class="space-y-3">
                        <a href="#" class="block text-red-800 hover:text-red-900 font-medium">
                            Gerenciar Usuários →
                        </a>
                        <a href="{{ route('admin.editions.create') }}" class="block text-red-800 hover:text-red-900 font-medium">
                            Criar Nova Edição →
                        </a>
                        <a href="{{ route('admin.editions.index') }}" class="block text-red-800 hover:text-red-900 font-medium">
                            Gerenciar Edições →
                        </a>
                        <a href="{{ route('admin.articles.create') }}" class="block text-red-800 hover:text-red-900 font-medium">
                            Publicar Artigo →
                        </a>
                        <a href="{{ route('admin.articles.index') }}" class="block text-red-800 hover:text-red-900 font-medium">
                            Gerenciar Artigos →
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="block text-red-800 hover:text-red-900 font-medium">
                            Gerenciar Categorias →
                        </a>
                        <a href="#" class="block text-red-800 hover:text-red-900 font-medium">
                            Configurações →
                        </a>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 font-serif">Atividades Recentes</h3>
                    <p class="text-gray-600 text-sm">Nenhuma atividade recente</p>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('home') }}" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Voltar ao Site
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


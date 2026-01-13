@extends('layouts.app')

@section('title', 'Dashboard - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Bem-vindo, {{ Auth::user()->name }}!
                </h1>
                <p class="text-gray-600">Esta é sua área de usuário</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 font-serif">Minha Assinatura</h3>
                    <p class="text-gray-600 text-sm mb-4">Gerencie sua assinatura da revista</p>
                    <a href="#" class="text-red-800 hover:text-red-900 font-medium text-sm">
                        Ver detalhes →
                    </a>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 font-serif">Edições</h3>
                    <p class="text-gray-600 text-sm mb-4">Acesse suas edições digitais</p>
                    <a href="#" class="text-red-800 hover:text-red-900 font-medium text-sm">
                        Ver edições →
                    </a>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 font-serif">Configurações</h3>
                    <p class="text-gray-600 text-sm mb-4">Atualize suas informações pessoais</p>
                    <a href="{{ route('settings.index') }}" class="text-red-800 hover:text-red-900 font-medium text-sm">
                        Editar configurações →
                    </a>
                </div>
            </div>

            <div class="flex gap-4">
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


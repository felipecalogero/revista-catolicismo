@extends('layouts.app')

@section('title', 'Importar Usuários - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Importar Usuários via CSV
                </h1>
                <p class="text-gray-600">Siga as instruções abaixo para importar múltiplos usuários de uma vez.</p>
            </div>

            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800">
                <h3 class="font-bold mb-2">Instruções do Arquivo CSV:</h3>
                <ol class="list-decimal list-inside space-y-1 text-sm">
                    <li>O arquivo deve ser no formato CSV separado por vírgula.</li>
                    <li>A primeira linha (cabeçalho) será ignorada.</li>
                    <li>As colunas devem estar na seguinte ordem: <strong>Nome, Email, CPF, Telefone, Endereço</strong>.</li>
                    <li>CPF, Telefone e Endereço são opcionais, mas Nome e Email são obrigatórios.</li>
                    <li>Os usuários importados não terão senha inicial e deverão criá-la no primeiro acesso.</li>
                </ol>
            </div>

            <form action="{{ route('admin.users.storeImport') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Selecione o arquivo CSV</label>
                    <input type="file" name="file" id="file" accept=".csv,.txt" required
                        class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-lg file:border-0
                        file:text-sm file:font-semibold
                        file:bg-red-50 file:text-red-800
                        hover:file:bg-red-100 transition-all">
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-red-800 text-white px-8 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium">
                        Iniciar Importação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

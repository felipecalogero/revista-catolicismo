@extends('layouts.app')

@section('title', 'Criar Artigo - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Criar Novo Artigo
                </h1>
                <p class="text-gray-600">Preencha os campos abaixo para publicar um novo artigo</p>
            </div>

            <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Título --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Título do Artigo <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        placeholder="Digite o título do artigo"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição Principal --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição Principal <span class="text-red-600">*</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        placeholder="Digite uma descrição breve do artigo"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Conteúdo --}}
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        Conteúdo do Artigo <span class="text-red-600">*</span>
                    </label>
                    <textarea
                        id="content"
                        name="content"
                        rows="15"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent font-serif"
                        placeholder="Digite o conteúdo completo do artigo"
                    >{{ old('content') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Você pode usar HTML básico para formatação</p>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Imagem --}}
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                        Imagem Principal <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                    >
                    <p class="mt-1 text-sm text-gray-500">Formatos aceitos: JPEG, PNG, JPG, GIF, WEBP (máx. 5MB)</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- URL do Vídeo (Opcional) --}}
                <div>
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">
                        URL do Vídeo (Opcional)
                    </label>
                    <input
                        type="url"
                        id="video_url"
                        name="video_url"
                        value="{{ old('video_url') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        placeholder="https://www.youtube.com/watch?v=..."
                    >
                    <p class="mt-1 text-sm text-gray-500">O vídeo aparecerá na metade do conteúdo do artigo</p>
                    @error('video_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Categoria --}}
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Categoria
                        </label>
                        <select
                            id="category_id"
                            name="category_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        >
                            <option value="">Selecione uma categoria</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                            <option value="new">+ Adicionar Nova Categoria</option>
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        {{-- Campo para nova categoria (oculto por padrão) --}}
                        <div id="new-category-container" class="mt-3 hidden">
                            <label for="new_category" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome da Nova Categoria
                            </label>
                            <input
                                type="text"
                                id="new_category"
                                name="new_category"
                                value="{{ old('new_category') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                                placeholder="Digite o nome da nova categoria"
                            >
                            @error('new_category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Autor --}}
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700 mb-2">
                            Autor
                        </label>
                        <input
                            type="text"
                            id="author"
                            name="author"
                            value="{{ old('author') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                            placeholder="Nome do autor"
                        >
                        @error('author')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Publicado --}}
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="published"
                        name="published"
                        value="1"
                        {{ old('published') ? 'checked' : '' }}
                        class="w-4 h-4 text-red-800 border-gray-300 rounded focus:ring-red-800"
                    >
                    <label for="published" class="ml-2 text-sm font-medium text-gray-700">
                        Publicar imediatamente
                    </label>
                </div>

                {{-- Botões --}}
                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button
                        type="submit"
                        class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium"
                    >
                        Publicar Artigo
                    </button>
                    <a
                        href="{{ route('admin.articles.index') }}"
                        class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const newCategoryContainer = document.getElementById('new-category-container');
    const newCategoryInput = document.getElementById('new_category');
    const form = categorySelect.closest('form');

    categorySelect.addEventListener('change', function() {
        if (this.value === 'new') {
            newCategoryContainer.classList.remove('hidden');
            newCategoryInput.required = true;
            categorySelect.value = ''; // Limpar o select para que não seja enviado
        } else {
            newCategoryContainer.classList.add('hidden');
            newCategoryInput.required = false;
            newCategoryInput.value = '';
        }
    });

    // Interceptar submit do formulário para garantir que category_id não seja "new"
    form.addEventListener('submit', function(e) {
        if (categorySelect.value === 'new') {
            categorySelect.value = '';
        }
    });

    // Verificar se já estava selecionado "nova categoria" (após erro de validação)
    if (newCategoryInput.value) {
        newCategoryContainer.classList.remove('hidden');
        newCategoryInput.required = true;
        categorySelect.value = '';
    }
});
</script>
@endsection


@extends('layouts.app')

@section('title', 'Editar Artigo - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Editar Artigo
                </h1>
                <p class="text-gray-600">Atualize as informações do artigo</p>
            </div>

            <form action="{{ route('admin.articles.update', $article->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Título --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Título do Artigo <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $article->title) }}"
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
                    >{{ old('description', $article->description) }}</textarea>
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
                    >{{ old('content', $article->content) }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Use as ferramentas de formatação acima do campo para formatar o texto</p>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Imagem Atual --}}
                @if($article->image_url)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Imagem Atual
                        </label>
                        <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-64 h-48 object-cover rounded-lg border border-gray-200">
                    </div>
                @endif

                {{-- Nova Imagem --}}
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                        Nova Imagem Principal
                    </label>
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                    >
                    <p class="mt-1 text-sm text-gray-500">Deixe em branco para manter a imagem atual. Formatos aceitos: JPEG, PNG, JPG, GIF, WEBP (máx. 5MB)</p>
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
                        value="{{ old('video_url', $article->video_url) }}"
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
                                <option value="{{ $category->id }}" {{ old('category_id', $article->category_id) == $category->id ? 'selected' : '' }}>
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
                            value="{{ old('author', $article->author) }}"
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
                        {{ old('published', $article->published) ? 'checked' : '' }}
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
                        Atualizar Artigo
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

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/quill-editor.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Quill no campo de conteúdo
    const contentEditor = initQuillEditor('content', 'content-editor', {
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                ['link'],
                ['clean']
            ]
        },
        placeholder: 'Digite o conteúdo completo do artigo'
    }, 400);

    if (contentEditor) {
        const contentTextarea = document.querySelector('#content');
        setupQuillFormValidation(null, [
            { editor: contentEditor, textarea: contentTextarea, fieldName: 'o conteúdo do artigo' }
        ]);
    }

    // Código para categoria
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
@endpush
@endsection

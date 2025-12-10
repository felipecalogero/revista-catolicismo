@extends('layouts.app')

@section('title', 'Gerenciar Artigos - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                        Gerenciar Artigos
                    </h1>
                    <p class="text-gray-600">Gerencie todos os artigos publicados</p>
                </div>
                <a href="{{ route('admin.articles.create') }}" class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium">
                    + Novo Artigo
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($articles as $article)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($article->image)
                                        <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="w-16 h-16 object-cover rounded">
                                    @else
                                        <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Sem imagem</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ Str::limit($article->title, 50) }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($article->description, 60) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">
                                        {{ $article->categoryRelation ? $article->categoryRelation->name : ($article->category ?? 'Sem categoria') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($article->published)
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Publicado</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Rascunho</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $article->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-2">
                                        <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.articles.edit', $article->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja deletar este artigo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Deletar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Nenhum artigo encontrado. <a href="{{ route('admin.articles.create') }}" class="text-red-800 hover:text-red-900 font-medium">Criar primeiro artigo</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($articles->hasPages())
                <div class="mt-6">
                    {{ $articles->links() }}
                </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('admin.dashboard') }}" class="text-red-800 hover:text-red-900 font-medium">
                    ← Voltar ao Painel
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Editar Edição - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8" id="page-content">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Editar Edição
                </h1>
                <p class="text-gray-600">Atualize as informações da edição</p>
            </div>

            <form action="{{ route('admin.editions.update', $edition->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="edition-form">
                @csrf
                @method('PUT')

                {{-- Popup de Loading --}}
                <div id="loading-popup" class="hidden fixed inset-0 z-[100] flex items-center justify-center pointer-events-none">
                    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4 border-2 border-red-800 pointer-events-auto" style="backdrop-filter: none; -webkit-backdrop-filter: none;">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-red-800 mx-auto mb-4"></div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2" id="loading-title">Processando...</h3>
                            <p class="text-sm text-gray-600 mb-4" id="loading-message">Por favor, aguarde. Não feche esta página.</p>
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                <div id="loading-progress-bar" class="bg-red-800 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <p class="text-xs text-gray-500" id="loading-status">Iniciando...</p>
                        </div>
                    </div>
                </div>

                {{-- Mensagem de Erro --}}
                <div id="upload-error" class="hidden bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-sm font-medium text-red-800" id="upload-error-message"></p>
                </div>

                {{-- Título --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Título da Edição <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $edition->title) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        placeholder="Ex: Edição 123 - Janeiro 2024"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição <span class="text-red-600">*</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        placeholder="Descreva brevemente o conteúdo desta edição"
                    >{{ old('description', $edition->description) }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Esta descrição será exibida quando o usuário visualizar a edição</p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Imagem da Capa --}}
                    <div>
                        <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">
                            Imagem da Capa
                        </label>
                        @if($edition->cover_image)
                            <div class="mb-3">
                                <img src="{{ Storage::url($edition->cover_image) }}" alt="{{ $edition->title }}" class="w-32 h-44 object-cover rounded border border-gray-300">
                                <p class="mt-1 text-xs text-gray-500">Capa atual</p>
                            </div>
                        @endif
                        <input
                            type="file"
                            id="cover_image"
                            name="cover_image"
                            accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        >
                        <p class="mt-1 text-sm text-gray-500">Deixe em branco para manter a capa atual. Formatos: JPEG, PNG, JPG, GIF, WEBP (máx. 5MB)</p>
                        @error('cover_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Arquivo PDF --}}
                    <div>
                        <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Arquivo PDF
                        </label>
                        @if($edition->pdf_file)
                            <div class="mb-3">
                                <a href="{{ Storage::url($edition->pdf_file) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    Visualizar PDF atual
                                </a>
                                <p class="mt-1 text-xs text-gray-500">PDF atual</p>
                            </div>
                        @endif
                        <input
                            type="file"
                            id="pdf_file"
                            name="pdf_file"
                            accept=".pdf"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        >
                        <p class="mt-1 text-sm text-gray-500">Deixe em branco para manter o PDF atual. Apenas arquivos PDF (máx. 100MB)</p>
                        <p class="mt-1 text-xs text-amber-600">⚠️ Arquivos grandes podem levar alguns minutos para upload. Por favor, aguarde.</p>
                        @error('pdf_file')
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
                        {{ old('published', $edition->published) ? 'checked' : '' }}
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
                        Atualizar Edição
                    </button>
                    <a
                        href="{{ route('admin.editions.index') }}"
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
    // Inicializar Quill no campo de descrição
    const descriptionEditor = initQuillEditor('description', 'description-editor', {
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        },
        placeholder: 'Descreva brevemente o conteúdo desta edição'
    }, 120);

    if (descriptionEditor) {
        const descriptionTextarea = document.querySelector('#description');
        setupQuillFormValidation('edition-form', [
            { editor: descriptionEditor, textarea: descriptionTextarea, fieldName: 'a descrição da edição' }
        ]);
    }

    const loadingPopup = document.getElementById('loading-popup');
    const pageContent = document.getElementById('page-content');
    const loadingTitle = document.getElementById('loading-title');
    const loadingMessage = document.getElementById('loading-message');
    const loadingStatus = document.getElementById('loading-status');
    const loadingProgressBar = document.getElementById('loading-progress-bar');
    const errorDiv = document.getElementById('upload-error');
    const errorMessage = document.getElementById('upload-error-message');
    const form = document.getElementById('edition-form');
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', async function(e) {
        const pdfFile = document.getElementById('pdf_file').files[0];
        const coverImage = document.getElementById('cover_image').files[0];

        // Verificar se é um arquivo grande (maior que 10MB)
        const isLargeFile = (pdfFile && pdfFile.size > 10 * 1024 * 1024) ||
                           (coverImage && coverImage.size > 10 * 1024 * 1024);

        if (isLargeFile) {
            e.preventDefault();

            // Ocultar erro anterior
            errorDiv.classList.add('hidden');

            // Mostrar popup de loading e embaçar fundo
            loadingPopup.classList.remove('hidden');
            pageContent.classList.add('backdrop-blur-sm', 'opacity-50');
            loadingTitle.textContent = 'Enviando arquivo...';
            loadingMessage.textContent = 'Por favor, aguarde. Não feche esta página durante o upload.';
            loadingStatus.textContent = 'Preparando upload...';
            loadingProgressBar.style.width = '0%';
            submitBtn.disabled = true;

            try {
                // Primeiro, obter um novo token CSRF e renovar a sessão
                const tokenResponse = await fetch('{{ route("admin.csrf-token") }}', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (tokenResponse.ok) {
                    const data = await tokenResponse.json();
                    const newToken = data.token;

                    if (newToken) {
                        // Atualizar token no formulário
                        const csrfInput = form.querySelector('input[name="_token"]');
                        if (csrfInput) {
                            csrfInput.value = newToken;
                        }

                        // Atualizar meta tag
                        const metaToken = document.querySelector('meta[name="csrf-token"]');
                        if (metaToken) {
                            metaToken.setAttribute('content', newToken);
                        }
                    }
                }

                // Preparar FormData
                const formData = new FormData(form);

                // Garantir que o método PUT está incluído
                if (!formData.has('_method')) {
                    formData.append('_method', 'PUT');
                }

                // Fazer upload via AJAX
                const xhr = new XMLHttpRequest();

                // Monitorar progresso do upload
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const uploadPercentComplete = (e.loaded / e.total) * 100;
                        // Upload representa 60% do processo total (restante 40% é compressão)
                        const totalProgress = Math.min(uploadPercentComplete * 0.6, 60);
                        loadingProgressBar.style.width = totalProgress + '%';
                        
                        // Quando upload estiver completo, mostrar mensagem diferente
                        if (uploadPercentComplete >= 100) {
                            loadingStatus.textContent = 'Upload concluído. Iniciando compressão...';
                        } else {
                            loadingStatus.textContent = `Enviando: ${Math.round(uploadPercentComplete)}%`;
                        }
                    }
                });

                // Tratar resposta
                xhr.addEventListener('load', function() {
                    if (xhr.status === 200 || xhr.status === 302) {
                        // Upload concluído, agora está comprimindo
                        loadingTitle.textContent = 'Comprimindo PDF...';
                        loadingMessage.textContent = 'O arquivo foi enviado. Agora estamos comprimindo para otimizar o tamanho. Isso pode levar alguns minutos.';
                        loadingStatus.textContent = 'Processando compressão...';
                        loadingProgressBar.style.width = '65%';
                        
                        // Simular progresso da compressão (60% a 95%)
                        let compressProgress = 65;
                        const compressInterval = setInterval(function() {
                            compressProgress += 2;
                            if (compressProgress < 95) {
                                loadingProgressBar.style.width = compressProgress + '%';
                                loadingStatus.textContent = `Comprimindo: ${compressProgress}%`;
                            } else {
                                clearInterval(compressInterval);
                                loadingProgressBar.style.width = '95%';
                                loadingStatus.textContent = 'Finalizando...';
                            }
                        }, 500);
                        
                        // Aguardar um pouco e então redirecionar (o servidor está comprimindo)
                        setTimeout(function() {
                            clearInterval(compressInterval);
                            loadingProgressBar.style.width = '100%';
                            loadingStatus.textContent = 'Concluído!';
                            
                            setTimeout(function() {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.href = '{{ route("admin.editions.index") }}';
                            }
                        } catch {
                            window.location.href = '{{ route("admin.editions.index") }}';
                        }
                            }, 500);
                        }, 3000);
                    } else if (xhr.status === 419) {
                        // Token CSRF expirado - tentar obter novo token e reenviar
                        loadingTitle.textContent = 'Renovando sessão...';
                        loadingStatus.textContent = 'Sessão expirada. Tentando renovar...';

                        // Tentar obter novo token e reenviar
                        fetch('{{ route("admin.csrf-token") }}', {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        }).then(response => response.json())
                        .then(data => {
                            if (data.token) {
                                // Atualizar token e reenviar
                                const csrfInput = form.querySelector('input[name="_token"]');
                                if (csrfInput) {
                                    csrfInput.value = data.token;
                                }

                                const metaToken = document.querySelector('meta[name="csrf-token"]');
                                if (metaToken) {
                                    metaToken.setAttribute('content', data.token);
                                }

                                // Preparar novo FormData com token atualizado
                                const newFormData = new FormData(form);

                                // Garantir que o token está no FormData
                                const retryToken = form.querySelector('input[name="_token"]')?.value ||
                                                  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                if (retryToken) {
                                    newFormData.set('_token', retryToken);
                                }

                                // Garantir que o método PUT está incluído
                                if (!newFormData.has('_method')) {
                                    newFormData.append('_method', 'PUT');
                                }

                                // Criar nova requisição
                                const retryXhr = new XMLHttpRequest();

                                retryXhr.upload.addEventListener('progress', function(e) {
                                    if (e.lengthComputable) {
                                        const uploadPercentComplete = (e.loaded / e.total) * 100;
                                        const totalProgress = Math.min(uploadPercentComplete * 0.6, 60);
                                        loadingProgressBar.style.width = totalProgress + '%';
                                        
                                        if (uploadPercentComplete >= 100) {
                                            loadingStatus.textContent = 'Upload concluído. Iniciando compressão...';
                                        } else {
                                            loadingStatus.textContent = `Enviando: ${Math.round(uploadPercentComplete)}%`;
                                        }
                                    }
                                });

                                retryXhr.addEventListener('load', function() {
                                    if (retryXhr.status === 200 || retryXhr.status === 302) {
                                        loadingTitle.textContent = 'Comprimindo PDF...';
                                        loadingMessage.textContent = 'O arquivo foi enviado. Agora estamos comprimindo para otimizar o tamanho.';
                                        loadingStatus.textContent = 'Processando compressão...';
                                        loadingProgressBar.style.width = '65%';
                                        
                                        // Simular progresso da compressão
                                        let compressProgress = 65;
                                        const compressInterval = setInterval(function() {
                                            compressProgress += 2;
                                            if (compressProgress < 95) {
                                                loadingProgressBar.style.width = compressProgress + '%';
                                                loadingStatus.textContent = `Comprimindo: ${compressProgress}%`;
                                            } else {
                                                clearInterval(compressInterval);
                                                loadingProgressBar.style.width = '95%';
                                                loadingStatus.textContent = 'Finalizando...';
                                            }
                                        }, 500);
                                        
                                        setTimeout(function() {
                                            clearInterval(compressInterval);
                                            loadingProgressBar.style.width = '100%';
                                            loadingStatus.textContent = 'Concluído!';
                                            
                                            setTimeout(function() {
                                        try {
                                            const response = JSON.parse(retryXhr.responseText);
                                            if (response.redirect) {
                                                window.location.href = response.redirect;
                                            } else {
                                                window.location.href = '{{ route("admin.editions.index") }}';
                                            }
                                        } catch {
                                            window.location.href = '{{ route("admin.editions.index") }}';
                                        }
                                            }, 500);
                                        }, 3000);
                                    } else {
                                        errorMessage.textContent = 'Erro ao fazer upload após renovar sessão. Por favor, recarregue a página e tente novamente.';
                                        errorDiv.classList.remove('hidden');
                                        loadingPopup.classList.add('hidden');
                                        pageContent.classList.remove('backdrop-blur-sm', 'opacity-50');
                                        submitBtn.disabled = false;
                                        submitBtn.textContent = 'Atualizar Edição';
                                    }
                                });

                                retryXhr.open('POST', form.action);
                                retryXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                                retryXhr.setRequestHeader('Accept', 'application/json');

                                // Adicionar token CSRF no header
                                const retryCsrfToken = form.querySelector('input[name="_token"]')?.value ||
                                                      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                if (retryCsrfToken) {
                                    retryXhr.setRequestHeader('X-CSRF-TOKEN', retryCsrfToken);
                                }

                                retryXhr.send(newFormData);
                            } else {
                                errorMessage.textContent = 'Não foi possível renovar a sessão. Por favor, recarregue a página e tente novamente.';
                                errorDiv.classList.remove('hidden');
                                loadingPopup.classList.add('hidden');
                                pageContent.classList.remove('backdrop-blur-sm', 'opacity-50');
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Atualizar Edição';

                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            }
                        }).catch(() => {
                            errorMessage.textContent = 'Erro ao renovar sessão. Por favor, recarregue a página e tente novamente.';
                            errorDiv.classList.remove('hidden');
                            loadingPopup.classList.add('hidden');
                            pageContent.classList.remove('backdrop-blur-sm', 'opacity-50');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Atualizar Edição';

                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        });
                    } else {
                        // Outro erro
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage.textContent = response.message || 'Erro ao fazer upload. Por favor, tente novamente.';
                        } catch {
                            errorMessage.textContent = 'Erro ao fazer upload. Por favor, tente novamente.';
                        }
                        errorDiv.classList.remove('hidden');
                        loadingPopup.classList.add('hidden');
                        pageContent.classList.remove('backdrop-blur-sm', 'opacity-50');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Atualizar Edição';
                    }
                });

                xhr.addEventListener('error', function() {
                    errorMessage.textContent = 'Erro de conexão. Verifique sua internet e tente novamente.';
                    errorDiv.classList.remove('hidden');
                    loadingPopup.classList.add('hidden');
                    pageContent.classList.remove('backdrop-blur-sm', 'opacity-50');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Atualizar Edição';
                });

                xhr.addEventListener('abort', function() {
                    errorMessage.textContent = 'Upload cancelado.';
                    errorDiv.classList.remove('hidden');
                    loadingPopup.classList.add('hidden');
                    pageContent.classList.remove('backdrop-blur-sm', 'opacity-50');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Atualizar Edição';
                });

                // Enviar requisição
                xhr.open('POST', form.action);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                // Adicionar token CSRF no header
                const csrfToken = form.querySelector('input[name="_token"]')?.value ||
                                 document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }

                xhr.send(formData);

            } catch (error) {
                console.error('Erro:', error);
                errorMessage.textContent = 'Erro ao processar upload. Por favor, tente novamente.';
                errorDiv.classList.remove('hidden');
                loadingPopup.classList.add('hidden');
                pageContent.classList.remove('backdrop-blur-sm', 'opacity-50');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Atualizar Edição';
            }
        } else {
            // Para arquivos pequenos, usar submit normal mas atualizar token
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            const csrfInput = form.querySelector('input[name="_token"]');
            if (metaToken && csrfInput) {
                csrfInput.value = metaToken.getAttribute('content');
            }

            loadingPopup.classList.remove('hidden');
            loadingTitle.textContent = 'Enviando...';
            loadingMessage.textContent = 'Por favor, aguarde.';
            loadingStatus.textContent = 'Processando formulário...';
            loadingProgressBar.style.width = '50%';
            pageContent.classList.add('backdrop-blur-sm', 'opacity-50');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';
        }
    });
});
</script>
@endpush
@endsection

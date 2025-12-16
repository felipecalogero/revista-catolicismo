@extends('layouts.app')

@section('title', 'Criar Edição - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Criar Nova Edição
                </h1>
                <p class="text-gray-600">Preencha os campos abaixo para adicionar uma nova edição da revista</p>
            </div>

            <form action="{{ route('admin.editions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="edition-form">
                @csrf

                {{-- Indicador de Progresso --}}
                <div id="upload-progress" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-800"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-800">Enviando arquivo... Por favor, aguarde.</p>
                            <p class="text-xs text-blue-600 mt-1">Não feche esta página durante o upload.</p>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div id="upload-progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
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
                        value="{{ old('title') }}"
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
                    >{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Esta descrição será exibida quando o usuário visualizar a edição</p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Imagem da Capa --}}
                    <div>
                        <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">
                            Imagem da Capa <span class="text-red-600">*</span>
                        </label>
                        <input
                            type="file"
                            id="cover_image"
                            name="cover_image"
                            accept="image/*"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        >
                        <p class="mt-1 text-sm text-gray-500">Formatos aceitos: JPEG, PNG, JPG, GIF, WEBP (máx. 5MB)</p>
                        @error('cover_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Arquivo PDF --}}
                    <div>
                        <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Arquivo PDF <span class="text-red-600">*</span>
                        </label>
                        <input
                            type="file"
                            id="pdf_file"
                            name="pdf_file"
                            accept=".pdf"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800 focus:border-transparent"
                        >
                        <p class="mt-1 text-sm text-gray-500">Apenas arquivos PDF (máx. 100MB)</p>
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
                        Criar Edição
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

    const progressDiv = document.getElementById('upload-progress');
    const progressBar = document.getElementById('upload-progress-bar');
    const errorDiv = document.getElementById('upload-error');
    const errorMessage = document.getElementById('upload-error-message');
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

            // Mostrar indicador de progresso
            progressDiv.classList.remove('hidden');
            progressBar.style.width = '0%';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

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

                // Preparar FormData APÓS atualizar o token
                const formData = new FormData(form);

                // Garantir que o token está no FormData
                const currentToken = form.querySelector('input[name="_token"]')?.value ||
                                    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (currentToken) {
                    formData.set('_token', currentToken);
                }

                // Fazer upload via AJAX
                const xhr = new XMLHttpRequest();

                // Monitorar progresso
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.style.width = percentComplete + '%';
                    }
                });

                // Tratar resposta
                xhr.addEventListener('load', function() {
                    if (xhr.status === 200 || xhr.status === 302) {
                        // Sucesso - redirecionar
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
                    } else if (xhr.status === 422) {
                        // Erro de validação - mostrar mensagem específica
                        try {
                            const response = JSON.parse(xhr.responseText);
                            let errorMsg = response.message || 'Erro de validação. Verifique os dados e tente novamente.';
                            if (response.error === 'file_not_received') {
                                errorMsg += ' Limites PHP: upload_max_filesize=' + (response.php_upload_max || 'N/A') + ', post_max_size=' + (response.php_post_max || 'N/A');
                            } else if (response.error === 'file_too_large') {
                                const fileSizeMB = (response.file_size / 1024 / 1024).toFixed(2);
                                const maxSizeMB = (response.max_size / 1024 / 1024).toFixed(2);
                                errorMsg += ' Tamanho do arquivo: ' + fileSizeMB + 'MB, máximo permitido: ' + maxSizeMB + 'MB';
                            }
                            errorMessage.textContent = errorMsg;
                        } catch {
                            errorMessage.textContent = 'Erro de validação. Verifique os dados e tente novamente.';
                        }
                        errorDiv.classList.remove('hidden');
                        progressDiv.classList.add('hidden');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Criar Edição';
                    } else if (xhr.status === 419) {
                        // Token CSRF expirado - tentar obter novo token e reenviar
                        errorMessage.textContent = 'Sessão expirada. Tentando renovar...';
                        errorDiv.classList.remove('hidden');

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

                                // Criar nova requisição
                                const retryXhr = new XMLHttpRequest();

                                retryXhr.upload.addEventListener('progress', function(e) {
                                    if (e.lengthComputable) {
                                        const percentComplete = (e.loaded / e.total) * 100;
                                        progressBar.style.width = percentComplete + '%';
                                    }
                                });

                                retryXhr.addEventListener('load', function() {
                                    if (retryXhr.status === 200 || retryXhr.status === 302) {
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
                                    } else {
                                        // Tentar obter mensagem de erro mais detalhada
                                        let errorMsg = 'Erro ao fazer upload após renovar sessão.';
                                        try {
                                            const errorResponse = JSON.parse(retryXhr.responseText);
                                            if (errorResponse.message) {
                                                errorMsg = errorResponse.message;
                                            }
                                            if (errorResponse.error === 'file_not_received') {
                                                errorMsg += ' PHP upload_max_filesize: ' + (errorResponse.php_upload_max || 'N/A') + ', post_max_size: ' + (errorResponse.php_post_max || 'N/A');
                                            }
                                        } catch {
                                            // Usar mensagem padrão
                                        }
                                        errorMessage.textContent = errorMsg + ' Por favor, recarregue a página e tente novamente.';
                                        errorDiv.classList.remove('hidden');
                                        progressDiv.classList.add('hidden');
                                        submitBtn.disabled = false;
                                        submitBtn.textContent = 'Criar Edição';
                                    }
                                });

                                retryXhr.addEventListener('error', function() {
                                    errorMessage.textContent = 'Erro de conexão durante o upload. Verifique sua conexão e tente novamente.';
                                    errorDiv.classList.remove('hidden');
                                    progressDiv.classList.add('hidden');
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = 'Criar Edição';
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
                                progressDiv.classList.add('hidden');
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Criar Edição';

                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            }
                        }).catch(() => {
                            errorMessage.textContent = 'Erro ao renovar sessão. Por favor, recarregue a página e tente novamente.';
                            errorDiv.classList.remove('hidden');
                            progressDiv.classList.add('hidden');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Criar Edição';

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
                        progressDiv.classList.add('hidden');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Criar Edição';
                    }
                });

                xhr.addEventListener('error', function() {
                    errorMessage.textContent = 'Erro de conexão. Verifique sua internet e tente novamente.';
                    errorDiv.classList.remove('hidden');
                    progressDiv.classList.add('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Criar Edição';
                });

                xhr.addEventListener('abort', function() {
                    errorMessage.textContent = 'Upload cancelado.';
                    errorDiv.classList.remove('hidden');
                    progressDiv.classList.add('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Criar Edição';
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

                xhr.addEventListener('error', function() {
                    errorMessage.textContent = 'Erro de conexão durante o upload. Verifique sua conexão e tente novamente.';
                    errorDiv.classList.remove('hidden');
                    progressDiv.classList.add('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Criar Edição';
                });

            } catch (error) {
                console.error('Erro:', error);
                errorMessage.textContent = 'Erro ao processar upload. Por favor, tente novamente.';
                errorDiv.classList.remove('hidden');
                progressDiv.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Criar Edição';
            }
        } else {
            // Para arquivos pequenos, usar submit normal mas atualizar token
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            const csrfInput = form.querySelector('input[name="_token"]');
            if (metaToken && csrfInput) {
                csrfInput.value = metaToken.getAttribute('content');
            }

            progressDiv.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';
        }
    });
});
</script>
@endpush
@endsection

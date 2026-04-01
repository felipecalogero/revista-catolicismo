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
                <h3 class="font-bold mb-2">Instruções de Importação:</h3>
                <ol class="list-decimal list-inside space-y-1 text-sm">
                    <li>O sistema aceita arquivos nos formatos <strong>Excel (.xlsx, .xls)</strong> e <strong>CSV</strong>.</li>
                    <li>A primeira linha (cabeçalho) é ignorada automaticamente.</li>
                    <li>O sistema é compatível com os formatos originais de "Usuários Físicos" e "Usuários Digitais", ou o formato padronizado de 16 colunas:
                        <div class="mt-2 grid grid-cols-2 gap-x-4 bg-white/50 p-2 rounded border border-blue-100">
                            <span>1. Nome <span class="text-red-600">*</span></span>
                            <span>2. Email <span class="text-red-600">*</span></span>
                            <span>3. Endereço</span>
                            <span>4. Bairro</span>
                            <span>5. Cidade</span>
                            <span>6. Estado</span>
                            <span>7. CEP</span>
                            <span>8. CPF/CNPJ</span>
                            <span>9. Plano</span>
                            <span>10. Produto</span>
                            <span>11. Status</span>
                            <span>12. Início</span>
                            <span>13. Fim</span>
                            <span>14. Cancelamento</span>
                            <span>15. Motivo</span>
                            <span>16. Profissão</span>
                        </div>
                    </li>
                    <li class="mt-2 text-red-600 font-semibold">* Campos obrigatórios: Nome e Email.</li>
                    <li>Para arquivos CSV, utilize a vírgula (,) como separador.</li>
                </ol>
            </div>

            <form id="importForm" action="{{ route('admin.users.storeImport') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="job_id" id="job_id" value="">
                
                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Selecione o arquivo (Excel ou CSV)</label>
                    <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv,.txt" required
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
                    <button type="submit" id="submitBtn" class="bg-red-800 text-white px-8 py-2 rounded-lg hover:bg-red-900 transition-colors font-medium">
                        Iniciar Importação
                    </button>
                </div>
            </form>

            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex flex-col items-center justify-center">
                <div class="bg-white p-8 rounded-lg shadow-xl max-w-sm w-full text-center">
                    <div class="mb-4">
                        <svg class="animate-spin h-10 w-10 text-red-800 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Processando Arquivo...</h3>
                    <p id="progressText" class="text-gray-600 mb-4 text-sm">Iniciando...</p>
                    
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 dark:bg-gray-200 shadow-inner">
                        <div id="progressBar" class="bg-red-800 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p class="text-xs text-gray-500 italic">Por favor, não feche esta janela.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('importForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const fileInput = document.getElementById('file');
        if (!fileInput.files.length) return;

        // Generate unique Job ID
        const jobId = 'job_' + Math.random().toString(36).substr(2, 9) + Date.now();
        document.getElementById('job_id').value = jobId;

        // Show loading overlay
        document.getElementById('loadingOverlay').classList.remove('hidden');
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;

        // Start polling
        const progressInterval = setInterval(async () => {
            try {
                const response = await fetch(`{{ route('admin.users.importProgress') }}?job_id=${jobId}`);
                if (response.ok) {
                    const data = await response.json();
                    
                    let progressText = 'Processando...';
                    let percent = 0;
                    
                    if (data.total > 0) {
                        percent = Math.round((data.current / data.total) * 100);
                        progressText = `Processado ${data.current} de ${data.total} registros (${percent}%)`;
                    } else if (data.status === 'starting') {
                        progressText = 'Lendo arquivo...';
                    }

                    document.getElementById('progressText').innerText = progressText;
                    document.getElementById('progressBar').style.width = percent + '%';

                    if (data.status === 'done' || data.status === 'error') {
                        clearInterval(progressInterval);
                    }
                }
            } catch (error) {
                console.error('Erro ao buscar progresso:', error);
            }
        }, 1000);

        // Submit form via fetch
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Usually returns a redirect to the index page which fetch follows transparently
            // When fetch is done, we redirect the browser to the final URL carrying the session flash messages
            window.location.href = response.url;

        } catch (error) {
            console.error('Submit error:', error);
            clearInterval(progressInterval);
            document.getElementById('loadingOverlay').classList.add('hidden');
            submitBtn.disabled = false;
            alert('Falha na comunicação com o servidor.');
        }
    });
</script>
@endsection

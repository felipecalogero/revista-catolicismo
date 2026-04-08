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

            <div class="mb-6 bg-gray-50 rounded-xl border border-gray-200 border-l-4 border-l-red-800 p-4 shadow-sm">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="p-1.5 bg-red-100 text-red-800 rounded-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Instruções de Importação</h3>
                </div>
                
                <div class="space-y-2.5 text-sm text-gray-600">
                    <div class="flex gap-2.5 items-center">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>Suporta arquivos <strong class="text-gray-800">Excel (.xlsx, .xls)</strong> e <strong class="text-gray-800">CSV</strong>.</p>
                    </div>
                    
                    <div class="flex gap-2.5 items-center">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <p>A ordem das colunas é rigorosa e os cabeçalhos são descartados. <span class="font-bold text-red-700">Nome e E-mail (colunas 1 e 2) são obrigatórios.</span></p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-1.5 text-sm">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            Formatos Estruturais Reconhecidos
                        </h4>
                        
                        <div class="grid grid-cols-1 gap-2.5">
                            <!-- Fisicos -->
                            <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:border-red-300 transition-colors">
                                <div class="flex items-center gap-3 mb-1.5">
                                    <span class="bg-red-50 text-red-800 text-[11px] font-bold px-2 py-0.5 rounded border border-red-100">17+ colunas</span>
                                    <strong class="text-sm text-gray-800">Usuários Físicos (Original)</strong>
                                </div>
                                <div class="bg-gray-50 p-1.5 rounded border border-gray-100 overflow-x-auto scrollbar-hide">
                                    <p class="text-[11px] font-mono text-gray-500 whitespace-nowrap">
                                        <span class="text-red-700 font-bold">Nome*</span> | <span class="text-red-700 font-bold">Email*</span> | CPF | Telefone | Endereço | Bairro | Cidade | UF | Estado | Cep | Início | Fim | Cancelamento Data | Cancelamento Motivo | Período Ini | Período Fim | Profissão
                                    </p>
                                </div>
                            </div>

                            <!-- Digitais -->
                            <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:border-blue-300 transition-colors">
                                <div class="flex items-center gap-3 mb-1.5">
                                    <span class="bg-blue-50 text-blue-800 text-[11px] font-bold px-2 py-0.5 rounded border border-blue-100">8 a 15 colunas</span>
                                    <strong class="text-sm text-gray-800">Usuários Digitais (Original)</strong>
                                </div>
                                <div class="bg-gray-50 p-1.5 rounded border border-gray-100 overflow-x-auto scrollbar-hide">
                                    <p class="text-[11px] font-mono text-gray-500 whitespace-nowrap">
                                        <span class="text-red-700 font-bold">Nome*</span> | <span class="text-red-700 font-bold">Email*</span> | CPF / CNPJ | Fone | UF | Início | Fim | Profissão
                                    </p>
                                </div>
                            </div>

                            <!-- Padrao 16 -->
                            <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:border-green-300 transition-colors">
                                <div class="flex items-center gap-3 mb-1.5">
                                    <span class="bg-green-50 text-green-800 text-[11px] font-bold px-2 py-0.5 rounded border border-green-100">Exatas 16 colunas</span>
                                    <strong class="text-sm text-gray-800">Padrão Unificado</strong>
                                </div>
                                <div class="bg-gray-50 p-1.5 rounded border border-gray-100 overflow-x-auto scrollbar-hide">
                                    <p class="text-[11px] font-mono text-gray-500 whitespace-nowrap">
                                        <span class="text-red-700 font-bold">Nome*</span> | <span class="text-red-700 font-bold">Email*</span> | Endereço | Bairro | Cidade | Estado | CEP | CPF/CNPJ | Plano | Produto | Status | Início | Fim | Data Cancel. | Motivo | Profissão
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
            <div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4">
                <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md text-center border border-gray-100">
                    <div class="mb-6">
                        <div class="relative inline-flex">
                            <svg class="animate-spin h-12 w-12 text-red-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Importando Usuários</h3>
                    <p id="progressText" class="text-gray-600 mb-6 text-sm">Preparando registros...</p>
                    
                    <div class="w-full bg-gray-100 rounded-full h-3 mb-4 shadow-inner">
                        <div id="progressBar" class="bg-red-800 h-3 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                    </div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold">Por favor, mantenha esta aba aberta</p>
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

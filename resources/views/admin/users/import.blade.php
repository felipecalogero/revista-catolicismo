@extends('layouts.app')

@section('title', 'Importar Usuários - Revista Catolicismo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-4 border-b-2 border-red-800">
                <h1 class="text-3xl font-bold text-gray-900 font-serif mb-2">
                    Importar Usuários
                </h1>
                <p class="text-gray-600">Envie um Excel ou CSV. O sistema detecta o formato e o tipo de assinatura automaticamente.</p>
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

            <div class="mb-6 bg-gray-50 rounded-xl border border-gray-200 border-l-4 border-l-red-800 p-4 shadow-sm space-y-2.5 text-sm text-gray-600">
                <p><strong class="text-gray-900">Formatos aceitos:</strong> Excel (.xlsx, .xls) e CSV.</p>
                <p><strong class="text-gray-900">Com cabeçalho:</strong> colunas em qualquer ordem (Nome, E-mail, CPF, Telefone, Endereço, Bairro, Cidade, UF, CEP, datas, etc.).</p>
                <p><strong class="text-gray-900">Sem cabeçalho:</strong> export original é reconhecido pelo conteúdo (nome, e-mail, UF, CEP, status, valor, produto e vigência).</p>
                <p>Usuários com o mesmo e-mail são <strong class="text-gray-900">atualizados</strong> apenas nos campos preenchidos na planilha. O tipo físico/virtual é detectado pelo produto/plano.</p>
            </div>

            <form id="importForm" action="{{ route('admin.users.storeImport') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50/80 p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="extend_expired_vigence" value="1" class="mt-1 rounded border-gray-300 text-red-800 focus:ring-red-800">
                        <span class="text-sm text-gray-800">
                            <span class="font-medium">Renovar vigência ao importar</span>
                            <span class="block text-xs text-gray-600 mt-1">Se a data de fim estiver no passado (e não houver cancelamento), define término como <strong>hoje + 12 meses</strong>.</span>
                        </span>
                    </label>
                </div>

                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Selecione o arquivo</label>
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

            <div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4">
                <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md text-center border border-gray-100">
                    <div class="mb-6">
                        <svg class="animate-spin h-12 w-12 text-red-800 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Importando usuários</h3>
                    <p class="text-gray-600 mb-2 text-sm">Aguarde; a página atualizará ao concluir.</p>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold">Mantenha esta aba aberta</p>
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

        document.getElementById('loadingOverlay').classList.remove('hidden');
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                let msg = 'Falha na importação.';
                try {
                    const err = await response.json();
                    if (err.message) msg = err.message;
                    else if (err.error) msg = err.error;
                    else if (err.errors) msg = Object.values(err.errors).flat().join(' ');
                } catch (_) {}
                document.getElementById('loadingOverlay').classList.add('hidden');
                submitBtn.disabled = false;
                alert(msg);
                return;
            }

            const data = await response.json();
            window.location.href = data.redirect || @json(route('admin.users.index'));
        } catch (error) {
            console.error('Submit error:', error);
            document.getElementById('loadingOverlay').classList.add('hidden');
            submitBtn.disabled = false;
            alert('Falha na comunicação com o servidor.');
        }
    });
</script>
@endsection

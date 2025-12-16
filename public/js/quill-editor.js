/**
 * Inicializa um editor Quill em um textarea
 * @param {string} textareaId - ID do textarea a ser substituído
 * @param {string} editorId - ID do container do editor Quill
 * @param {object} options - Opções de configuração do Quill
 * @param {number} minHeight - Altura mínima do editor em pixels
 * @param {string} placeholder - Texto do placeholder
 * @returns {Quill} Instância do editor Quill
 */
function initQuillEditor(textareaId, editorId, options = {}, minHeight = 300, placeholder = '') {
    const textarea = document.querySelector(`#${textareaId}`);
    if (!textarea) {
        console.warn(`Textarea #${textareaId} não encontrado`);
        return null;
    }

    const content = textarea.value;
    const editorContainer = document.createElement('div');
    editorContainer.id = editorId;

    // Remover required antes de ocultar o textarea
    textarea.removeAttribute('required');
    textarea.style.display = 'none';
    textarea.parentNode.insertBefore(editorContainer, textarea);

    // Configuração padrão do Quill
    const defaultOptions = {
        theme: 'snow',
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
        placeholder: placeholder
    };

    // Mesclar opções personalizadas com padrão
    const quillOptions = {
        ...defaultOptions,
        ...options,
        modules: {
            ...defaultOptions.modules,
            ...(options.modules || {})
        }
    };

    const editor = new Quill(`#${editorId}`, quillOptions);

    // Carregar conteúdo existente
    if (content) {
        editor.root.innerHTML = content;
    }

    // Ajustar altura mínima
    const editorElement = document.querySelector(`#${editorId} .ql-editor`);
    if (editorElement) {
        editorElement.style.minHeight = `${minHeight}px`;
    }

    return editor;
}

/**
 * Sincroniza o conteúdo do Quill com o textarea antes do submit
 * @param {Quill} editor - Instância do editor Quill
 * @param {HTMLElement} textarea - Elemento textarea
 * @param {string} fieldName - Nome do campo para mensagem de erro
 * @returns {boolean} true se válido, false caso contrário
 */
function syncQuillToTextarea(editor, textarea, fieldName = 'campo') {
    const content = editor.root.innerHTML;
    const textOnly = editor.getText().trim();

    if (!textOnly) {
        alert(`Por favor, preencha ${fieldName}.`);
        editor.focus();
        return false;
    }

    textarea.value = content;
    return true;
}

/**
 * Configura a validação e sincronização do Quill no formulário
 * @param {string} formId - ID do formulário (null para usar o primeiro form encontrado)
 * @param {Array} editors - Array de objetos {editor, textarea, fieldName}
 * @param {Function} additionalValidation - Função adicional de validação a ser executada
 */
function setupQuillFormValidation(formId, editors, additionalValidation = null) {
    const form = formId ? document.getElementById(formId) : document.querySelector('form');
    if (!form) {
        console.warn('Formulário não encontrado');
        return;
    }

    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Validar e sincronizar editores Quill
        editors.forEach(({ editor, textarea, fieldName }) => {
            if (editor && textarea) {
                if (!syncQuillToTextarea(editor, textarea, fieldName)) {
                    isValid = false;
                }
            }
        });

        // Executar validação adicional se fornecida
        if (additionalValidation && typeof additionalValidation === 'function') {
            if (!additionalValidation()) {
                isValid = false;
            }
        }

        // Garantir que category_id não seja "new"
        const categorySelect = document.getElementById('category_id');
        if (categorySelect && categorySelect.value === 'new') {
            categorySelect.value = '';
        }

        if (!isValid) {
            e.preventDefault();
            return false;
        }
    });
}


<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Acervo (edições legado) — regras de acesso
    |--------------------------------------------------------------------------
    |
    | Todo o acervo histórico foi migrado para a tabela `editions` (com
    | `is_legacy = true`) e seus assets vivem em `storage/app/public/editions/`.
    | Os campos abaixo só controlam visibilidade.
    |
    */

    /** Caminho absoluto da pasta Num (legado em disco). Usado pelo comando de importação. */
    'legacy_acervo_num_path' => env('REVISTA_LEGACY_ACERVO_NUM_PATH'),

    /** Usuário dono das edições importadas (ID). Se vazio, usa o primeiro admin. */
    'legacy_import_user_id' => env('REVISTA_LEGACY_IMPORT_USER_ID'),

    /**
     * Se true, edições com is_legacy contam como “arquivo” para a regra de acesso
     * (qualquer usuário logado acessa sem assinatura).
     */
    'legacy_counts_as_free_tier' => env('REVISTA_LEGACY_COUNTS_AS_FREE_TIER', true),

    /**
     * Se true, visitantes não logados veem capa/sumário/páginas das edições legado sem login.
     * Use com cuidado (conteúdo aberto).
     */
    'legacy_public_access' => env('REVISTA_LEGACY_PUBLIC_ACCESS', false),

];

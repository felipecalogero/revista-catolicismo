<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Links do rodapé — edições (arquivo x nova versão)
    |--------------------------------------------------------------------------
    |
    | Edições anteriores a jan/2025: site legado.
    | Edições a partir de jan/2025: nova versão (mesmo destino de /edicoes na pré-produção).
    |
    */

    'edicoes_arquivo_url' => env('REVISTA_EDICOES_ARQUIVO_URL', 'https://catolicismo.com.br/'),

    'edicoes_novas_url' => env('REVISTA_EDICOES_NOVAS_URL', 'https://novaversao.catolicismo.com.br/edicoes'),

    /** Buscador / listagem com foco em 2025+ (ano na query da listagem de edições) */
    'edicoes_busca_url' => env('REVISTA_EDICOES_BUSCA_URL', 'https://novaversao.catolicismo.com.br/edicoes?year=2025'),

];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para os planos de assinatura e integração com PagBank
    |
    */

    'physical_price' => env('SUBSCRIPTION_PHYSICAL_PRICE', 299.90),
    'virtual_price' => env('SUBSCRIPTION_VIRTUAL_PRICE', 199.90),

    /*
    |--------------------------------------------------------------------------
    | PagBank API Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para usar a API completa do PagBank
    | Use sandbox em desenvolvimento: https://sandbox.api.pagseguro.com
    | Use produção: https://api.pagseguro.com
    |
    */

    'pagbank_api_url' => env('PAGBANK_API_URL', 'https://sandbox.api.pagseguro.com'),
    'pagbank_api_token' => env('PAGBANK_API_TOKEN'),
    'pagbank_webhook_url' => env('PAGBANK_WEBHOOK_URL', env('APP_URL') . '/webhook/pagbank'),

];

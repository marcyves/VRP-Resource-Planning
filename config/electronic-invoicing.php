<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plateforme agréée (PA)
    |--------------------------------------------------------------------------
    |
    | null     — désactivé (driver Null, tests)
    | superpdp — SuperPDP (POC)
    |
    */
    'platform' => env('E_INVOICE_PLATFORM'),

    'superpdp' => [
        'base_url' => env('SUPERPDP_BASE_URL', 'https://api.superpdp.tech'),
        // production (défaut) ou sandbox — credentials distincts chez SuperPDP (Paramètres > Applications)
        'env' => env('SUPERPDP_ENV', 'production'),
        'client_id' => env('SUPERPDP_CLIENT_ID'),
        'client_secret' => env('SUPERPDP_CLIENT_SECRET'),
        'sandbox_client_id' => env('SUPERPDP_SANDBOX_CLIENT_ID'),
        'sandbox_client_secret' => env('SUPERPDP_SANDBOX_CLIENT_SECRET'),
        'access_token' => env('SUPERPDP_ACCESS_TOKEN'),
        'webhook_secret' => env('SUPERPDP_WEBHOOK_SECRET'),
        // Sandbox : adresses de routage annuaire / PEPPOL (client test Tricatel par défaut)
        'sandbox_routing_prefix' => env('SUPERPDP_SANDBOX_ROUTING_PREFIX', '315143296'),
        'sandbox_buyer_siren' => env('SUPERPDP_SANDBOX_BUYER_SIREN', '000000001'),
        'sandbox_buyer_electronic_address' => env('SUPERPDP_SANDBOX_BUYER_ELECTRONIC_ADDRESS', '315143296_12712'),
        'force_sandbox_buyer' => env('SUPERPDP_FORCE_SANDBOX_BUYER', false),
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plateforme agréée (PA)
    |--------------------------------------------------------------------------
    |
    | null     — désactivé (driver Null)
    | superpdp — SuperPDP (POC / sandbox ; production verrouillée ci-dessous)
    |
    | Ne pas définir E_INVOICE_PLATFORM en production locataire tant que le
    | go-live n’est pas explicitement autorisé.
    |
    */
    'platform' => env('E_INVOICE_PLATFORM'),

    /*
    |--------------------------------------------------------------------------
    | Verrou production
    |--------------------------------------------------------------------------
    |
    | Même avec SUPERPDP_ENV=production et des credentials live, VRP n’émet
    | pas vers la PA tant que ce flag n’est pas true. Rester false par défaut.
    |
    */
    'allow_production' => filter_var(env('E_INVOICE_ALLOW_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),

    /*
    | Optional public webhook URL (otherwise APP_URL + /webhooks/e-invoice/superpdp).
    | Register this HTTPS URL in the SuperPDP application. Never commit the HMAC secret.
    */
    'webhook_url' => env('E_INVOICE_WEBHOOK_URL'),

    /*
    | When true, POST /webhooks/e-invoice/* over plain HTTP is rejected (400).
    | Enable on IONOS only after HTTPS (and TRUSTED_PROXIES if TLS is terminated upstream).
    */
    'require_https_webhooks' => filter_var(env('E_INVOICE_REQUIRE_HTTPS_WEBHOOKS', false), FILTER_VALIDATE_BOOLEAN),

    /*
    | Ops mailbox for submit/webhook failures. Empty = log only (storage/logs/e-invoice.log).
    */
    'alert_email' => env('E_INVOICE_ALERT_EMAIL'),

    'superpdp' => [
        'base_url' => env('SUPERPDP_BASE_URL', 'https://api.superpdp.tech'),
        // sandbox par défaut — jamais production implicite
        'env' => env('SUPERPDP_ENV', 'sandbox'),
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
        'force_sandbox_buyer' => filter_var(env('SUPERPDP_FORCE_SANDBOX_BUYER', false), FILTER_VALIDATE_BOOLEAN),
    ],

];

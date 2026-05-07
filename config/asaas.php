<?php

return [
    'env' => env('ASAAS_ENV', 'sandbox'),

    'base_url' => env('ASAAS_ENV', 'sandbox') === 'production'
        ? 'https://api.asaas.com/v3'
        : 'https://sandbox.asaas.com/api/v3',

    'api_key' => env('ASAAS_API_KEY'),

    'webhook_token' => env('ASAAS_WEBHOOK_TOKEN'),

    // Plano: dias entre cobranças e valor
    'plan' => [
        'value' => (float) env('ASAAS_PLAN_VALUE', 99.90),
        'cycle' => env('ASAAS_PLAN_CYCLE', 'MONTHLY'), // MONTHLY, QUARTERLY, YEARLY
        'description' => env('ASAAS_PLAN_DESCRIPTION', 'Assinatura secadordecafe'),
    ],

    // Após quantos dias em past_due bloqueamos a fazenda
    'block_after_days' => (int) env('ASAAS_BLOCK_AFTER_DAYS', 7),
];

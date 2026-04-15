<?php

return [
    'default_chain_id' => env('WEB3_DEFAULT_CHAIN_ID', '56'),

    // Dev-stage prefilled payment parameters (override in .env before production).
    'payment' => [
        'chain_id' => env('WEB3_PAYMENT_CHAIN_ID', '56'),
        'token_address' => env('WEB3_PAYMENT_TOKEN_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'),
    ],

    'supported_assets' => [
        'USDT',
    ],

    'supported_networks' => [
        'BSC',
        'ETH',
    ],

    // Structure: ['USDT' => ['BSC' => '0x...']]
    'treasury_addresses' => [
        'USDT' => [
            'BSC' => env('WEB3_USDT_BSC_TREASURY_ADDRESS', ''),
            'ETH' => env('WEB3_USDT_ETH_TREASURY_ADDRESS', ''),
        ],
    ],
];

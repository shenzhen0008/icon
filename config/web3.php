<?php

return [
    'default_chain_id' => env('WEB3_DEFAULT_CHAIN_ID', '56'),

    // Dev-stage prefilled payment parameters (override in .env before production).
    'payment' => [
        'chain_id' => env('WEB3_PAYMENT_CHAIN_ID', '56'),
        'token_address' => env('WEB3_PAYMENT_TOKEN_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'),
        'to_address' => env('WEB3_PAYMENT_TO_ADDRESS', '0x000000000000000000000000000000000000dEaD'),
        'walletconnect_project_id' => env('WEB3_WALLETCONNECT_PROJECT_ID', ''),
    ],

    // Structure: ['USDT' => ['BSC' => '0x...']]
    'token_contracts' => [
        'USDT' => [
            'BSC' => env('WEB3_USDT_BSC_TOKEN_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'),
            'ETH' => env('WEB3_USDT_ETH_TOKEN_ADDRESS', ''),
        ],
        'USDC' => [
            'BSC' => env('WEB3_USDC_BSC_TOKEN_ADDRESS', '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48'),
            'ETH' => env('WEB3_USDC_ETH_TOKEN_ADDRESS', ''),
        ],
        'BUSD' => [
            'BSC' => env('WEB3_BUSD_BSC_TOKEN_ADDRESS', '0x1111111111111111111111111111111111111111'),
            'ETH' => env('WEB3_BUSD_ETH_TOKEN_ADDRESS', ''),
        ],
        'DAI' => [
            'BSC' => env('WEB3_DAI_BSC_TOKEN_ADDRESS', '0x2222222222222222222222222222222222222222'),
            'ETH' => env('WEB3_DAI_ETH_TOKEN_ADDRESS', ''),
        ],
        'WBTC' => [
            'BSC' => env('WEB3_WBTC_BSC_TOKEN_ADDRESS', '0x6666666666666666666666666666666666666666'),
            'ETH' => env('WEB3_WBTC_ETH_TOKEN_ADDRESS', ''),
        ],
        'WETH' => [
            'BSC' => env('WEB3_WETH_BSC_TOKEN_ADDRESS', '0x7777777777777777777777777777777777777777'),
            'ETH' => env('WEB3_WETH_ETH_TOKEN_ADDRESS', ''),
        ],
    ],

    'supported_assets' => [
        'USDT',
        'USDC',
        'BUSD',
        'DAI',
        'WBTC',
        'WETH',
    ],

    'supported_networks' => [
        'BSC',
        'ETH',
    ],

    // Structure: ['USDT' => ['BSC' => '0x...']]
    'treasury_addresses' => [
        'USDT' => [
            'BSC' => env('WEB3_USDT_BSC_TREASURY_ADDRESS', '0x000000000000000000000000000000000000dEaD'),
            'ETH' => env('WEB3_USDT_ETH_TREASURY_ADDRESS', '0x000000000000000000000000000000000000dEaD'),
        ],
        'USDC' => [
            'BSC' => env('WEB3_USDC_BSC_TREASURY_ADDRESS', '0x3333333333333333333333333333333333333333'),
            'ETH' => env('WEB3_USDC_ETH_TREASURY_ADDRESS', ''),
        ],
        'BUSD' => [
            'BSC' => env('WEB3_BUSD_BSC_TREASURY_ADDRESS', '0x4444444444444444444444444444444444444444'),
            'ETH' => env('WEB3_BUSD_ETH_TREASURY_ADDRESS', ''),
        ],
        'DAI' => [
            'BSC' => env('WEB3_DAI_BSC_TREASURY_ADDRESS', '0x5555555555555555555555555555555555555555'),
            'ETH' => env('WEB3_DAI_ETH_TREASURY_ADDRESS', ''),
        ],
        'WBTC' => [
            'BSC' => env('WEB3_WBTC_BSC_TREASURY_ADDRESS', '0x8888888888888888888888888888888888888888'),
            'ETH' => env('WEB3_WBTC_ETH_TREASURY_ADDRESS', ''),
        ],
        'WETH' => [
            'BSC' => env('WEB3_WETH_BSC_TREASURY_ADDRESS', '0x9999999999999999999999999999999999999999'),
            'ETH' => env('WEB3_WETH_ETH_TREASURY_ADDRESS', ''),
        ],
    ],
];

<?php

return [
    'assets' => [
        'USDT' => [
            'code' => 'USDT',
            'name' => 'USDT',
            'network' => env('RECHARGE_NETWORK_USDT', 'TRC20'),
            'address' => env('RECHARGE_ADDRESS_USDT', 'TLfWAddressReplaceInEnvForProduction'),
        ],
        'USDC' => [
            'code' => 'USDC',
            'name' => 'USDC',
            'network' => env('RECHARGE_NETWORK_USDC', 'TRC20'),
            'address' => env('RECHARGE_ADDRESS_USDC', 'TLfWAddressReplaceInEnvForProduction'),
        ],
        'BTC' => [
            'code' => 'BTC',
            'name' => 'Bitcoin',
            'network' => env('RECHARGE_NETWORK_BTC', 'Bitcoin'),
            'address' => env('RECHARGE_ADDRESS_BTC', 'bc1qreplaceinenvforproduction'),
        ],
        'ETH' => [
            'code' => 'ETH',
            'name' => 'Ethereum',
            'network' => env('RECHARGE_NETWORK_ETH', 'Ethereum'),
            'address' => env('RECHARGE_ADDRESS_ETH', '0xreplaceinenvforproduction'),
        ],
        'DOGE' => [
            'code' => 'DOGE',
            'name' => 'Dogecoin',
            'network' => env('RECHARGE_NETWORK_DOGE', 'Dogecoin'),
            'address' => env('RECHARGE_ADDRESS_DOGE', 'Dreplaceinenvforproduction'),
        ],
        'BNB' => [
            'code' => 'BNB',
            'name' => 'BNB',
            'network' => env('RECHARGE_NETWORK_BNB', 'BNB Smart Chain'),
            'address' => env('RECHARGE_ADDRESS_BNB', '0xreplaceinenvforproduction'),
        ],
        'XRP' => [
            'code' => 'XRP',
            'name' => 'XRP',
            'network' => env('RECHARGE_NETWORK_XRP', 'XRP Ledger'),
            'address' => env('RECHARGE_ADDRESS_XRP', 'rreplaceinenvforproduction'),
        ],
        'SOL' => [
            'code' => 'SOL',
            'name' => 'Solana',
            'network' => env('RECHARGE_NETWORK_SOL', 'Solana'),
            'address' => env('RECHARGE_ADDRESS_SOL', 'So1replaceinenvforproduction'),
        ],
        'TRX' => [
            'code' => 'TRX',
            'name' => 'TRON',
            'network' => env('RECHARGE_NETWORK_TRX', 'TRON'),
            'address' => env('RECHARGE_ADDRESS_TRX', 'Treplaceinenvforproduction'),
        ],
        'LTC' => [
            'code' => 'LTC',
            'name' => 'Litecoin',
            'network' => env('RECHARGE_NETWORK_LTC', 'Litecoin'),
            'address' => env('RECHARGE_ADDRESS_LTC', 'ltc1replaceinenvforproduction'),
        ],
    ],
];

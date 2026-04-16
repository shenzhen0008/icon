<?php

return [
    'allowed_receive_assets' => ['USDT', 'USDC', 'BTC', 'ETH'],

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
    ],
];

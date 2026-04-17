<?php

return [
    'allowed_receive_assets' => ['USDT', 'USDC', 'BTC', 'ETH'],

    'bank_receiver' => [
        'enabled' => (bool) env('RECHARGE_BANK_ENABLED', true),
        'bank_name' => env('RECHARGE_BANK_NAME', '招商银行（测试）'),
        'account_name' => env('RECHARGE_BANK_ACCOUNT_NAME', 'ICON MARKET TEST A'),
        'card_number' => env('RECHARGE_BANK_CARD_NUMBER', '6225880000001234'),
        'branch_name' => env('RECHARGE_BANK_BRANCH_NAME', '深圳南山科技园支行（测试）'),
    ],

    'bank_receivers' => [
        'cmb_test_a' => [
            'code' => 'CMB-A',
            'enabled' => (bool) env('RECHARGE_BANK_A_ENABLED', true),
            'bank_name' => env('RECHARGE_BANK_A_NAME', '招商银行（测试）'),
            'account_name' => env('RECHARGE_BANK_A_ACCOUNT_NAME', 'ICON MARKET TEST A'),
            'card_number' => env('RECHARGE_BANK_A_CARD_NUMBER', '6225880000001234'),
            'branch_name' => env('RECHARGE_BANK_A_BRANCH_NAME', '深圳南山科技园支行（测试）'),
            'sort' => 10,
        ],
        'abc_test_b' => [
            'code' => 'ABC-B',
            'enabled' => (bool) env('RECHARGE_BANK_B_ENABLED', true),
            'bank_name' => env('RECHARGE_BANK_B_NAME', '农业银行（测试）'),
            'account_name' => env('RECHARGE_BANK_B_ACCOUNT_NAME', 'ICON MARKET TEST B'),
            'card_number' => env('RECHARGE_BANK_B_CARD_NUMBER', '6228480000005678'),
            'branch_name' => env('RECHARGE_BANK_B_BRANCH_NAME', '广州天河支行（测试）'),
            'sort' => 20,
        ],
        'icbc_test_c' => [
            'code' => 'ICBC-C',
            'enabled' => (bool) env('RECHARGE_BANK_C_ENABLED', true),
            'bank_name' => env('RECHARGE_BANK_C_NAME', '工商银行（测试）'),
            'account_name' => env('RECHARGE_BANK_C_ACCOUNT_NAME', 'ICON MARKET TEST C'),
            'card_number' => env('RECHARGE_BANK_C_CARD_NUMBER', '6222000000002468'),
            'branch_name' => env('RECHARGE_BANK_C_BRANCH_NAME', '上海浦东支行（测试）'),
            'sort' => 30,
        ],
    ],

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

<?php

return [
    'enabled' => env('SETTLEMENT_ENABLED', true),
    'run_at' => env('SETTLEMENT_RUN_AT', '00:05'),
    'timezone' => env('SETTLEMENT_TIMEZONE', 'Asia/Shanghai'),
];


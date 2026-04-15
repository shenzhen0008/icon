<?php

return [
    'enabled' => env('REFERRAL_ENABLED', true),
    'go_live_date' => env('REFERRAL_GO_LIVE_DATE', '2026-04-15'),
    'business_timezone' => env('REFERRAL_BUSINESS_TIMEZONE', 'Asia/Shanghai'),
    'invite_code_session_key' => 'referral_invite_code',
    'invite_code_cookie_name' => 'referral_invite_code',
    'invite_code_cookie_minutes' => 60 * 24 * 30,
    'invite_code_length' => 8,
    'batch_chunk_size' => 200,
];

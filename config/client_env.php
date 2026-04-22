<?php

return [
    'enabled' => env('CLIENT_ENV_ENABLED', true),
    'expose_raw_user_agent' => env('CLIENT_ENV_EXPOSE_RAW_UA', false),
    'log_path' => env('CLIENT_ENV_LOG_PATH', 'client-env/probe-log.jsonl'),
    'webview_keywords' => [
        'webview',
        '; wv)',
        'micromessenger',
        'fban',
        'fbav',
        'line/',
    ],
];

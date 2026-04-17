<?php

return [
    'default_locale' => env('I18N_DEFAULT_LOCALE', 'zh-CN'),
    'fallback_locale' => env('I18N_FALLBACK_LOCALE', 'zh-CN'),
    'session_key' => 'locale',
    'query_key' => 'locale',
    'supported_locales' => [
        'zh-CN',
        'en',
        'ja',
        'ko',
        'fr',
        'de',
        'es',
        'pt',
    ],
];

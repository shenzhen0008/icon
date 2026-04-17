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
    'locale_labels_zh' => [
        'zh-CN' => '中文',
        'en' => '英语',
        'ja' => '日语',
        'ko' => '韩语',
        'fr' => '法语',
        'de' => '德语',
        'es' => '西班牙语',
        'pt' => '葡萄牙语',
    ],
];

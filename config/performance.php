<?php

return [

    'enabled' => (bool) env('PERFORMANCE_PROBE_ENABLED', env('APP_ENV') === 'local'),

    'log_channel' => env('PERFORMANCE_PROBE_LOG_CHANNEL', 'performance'),

    'slow_query_threshold_ms' => (float) env('PERFORMANCE_PROBE_SLOW_QUERY_MS', 50),

    'max_logged_queries' => (int) env('PERFORMANCE_PROBE_MAX_QUERIES', 5),

    'excluded_paths' => [
        'build/*',
        'css/*',
        'js/*',
        'images/*',
        'favicon.ico',
        'robots.txt',
        'site.webmanifest',
        'up',
    ],

];

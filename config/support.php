<?php

return [
    'tawk' => [
        'enabled' => (bool) env('TAWK_ENABLED', false),
        'property_id' => env('TAWK_PROPERTY_ID'),
        'widget_id' => env('TAWK_WIDGET_ID'),
    ],
];

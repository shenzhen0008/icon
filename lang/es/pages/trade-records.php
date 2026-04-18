<?php

return [
    'html_lang' => 'es',
    'meta_title' => 'Registros de operaciones | Icon Market',
    'title' => 'Registros de operaciones',
    'intro' => 'Página dedicada de registros de operaciones para el panel de inicio (:mode).',
    'back_home' => 'Volver al inicio',
    'mode' => [
        'demo' => 'DEMO',
        'live' => 'LIVE',
    ],
    'columns' => [
        'type' => 'Tipo',
        'content' => 'Contenido',
        'amount_usdt' => 'Monto (USDT)',
        'status' => 'Estado',
        'time' => 'Hora',
        'time_mobile' => 'Hora',
    ],
    'event_type' => [
        'purchase_debit' => 'Compra',
        'withdrawal_debit' => 'Retiro',
        'withdrawal_refund' => 'Reembolso de retiro',
    ],
    'status' => [
        'completed' => 'Completado',
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
        'refunded' => 'Reembolsado',
        'cancelled' => 'Cancelado',
    ],
    'empty' => 'Aún no hay registros de operaciones',
];

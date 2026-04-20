<?php

return [
    'html_lang' => 'pt',
    'meta_title' => 'Registros de negociação | :app_name',
    'title' => 'Registros de negociação',
    'intro' => 'Página dedicada de registros de negociação para o painel inicial (:mode).',
    'back_home' => 'Voltar para a página inicial',
    'mode' => [
        'demo' => 'DEMO',
        'live' => 'LIVE',
    ],
    'columns' => [
        'type' => 'Tipo',
        'content' => 'Conteúdo',
        'amount_usdt' => 'Valor (USDT)',
        'status' => 'Status',
        'time' => 'Hora',
        'time_mobile' => 'Hora',
    ],
    'event_type' => [
        'purchase_debit' => 'Compra',
        'withdrawal_debit' => 'Saque',
        'withdrawal_refund' => 'Reembolso de saque',
    ],
    'status' => [
        'completed' => 'Concluído',
        'pending' => 'Pendente',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado',
        'refunded' => 'Reembolsado',
        'cancelled' => 'Cancelado',
    ],
    'empty' => 'Ainda não há registros de negociação',
];

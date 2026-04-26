<?php

return [
    'html_lang' => 'en',
    'meta_title' => 'Trade Records | :app_name',
    'title' => 'Trade Records',
    'intro' => 'Dedicated trade records page for the home panel (:mode).',
    'back_home' => 'Back to Home',
    'mode' => [
        'demo' => 'DEMO',
        'live' => 'LIVE',
    ],
    'columns' => [
        'type' => 'Type',
        'content' => 'Content',
        'amount_usdt' => 'Amount (USDT)',
        'status' => 'Status',
        'time' => 'Time',
        'time_mobile' => 'Time',
    ],
    'event_type' => [
        'purchase_debit' => 'Purchase',
        'principal_return_credit' => 'Principal Return',
        'withdrawal_debit' => 'Withdrawal',
        'withdrawal_refund' => 'Withdrawal Refund',
    ],
    'status' => [
        'completed' => 'Completed',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ],
    'empty' => 'No trade records yet',
];

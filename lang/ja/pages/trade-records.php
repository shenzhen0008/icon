<?php

return [
    'html_lang' => 'ja',
    'meta_title' => '取引履歴 | :app_name',
    'title' => '取引履歴',
    'intro' => 'ホームパネル（:mode）専用の取引履歴ページです。',
    'back_home' => 'ホームに戻る',
    'mode' => [
        'demo' => 'デモ',
        'live' => 'ライブ',
    ],
    'columns' => [
        'type' => '種類',
        'content' => '内容',
        'amount_usdt' => '金額 (USDT)',
        'status' => '状態',
        'time' => '時間',
        'time_mobile' => '時間',
    ],
    'event_type' => [
        'purchase_debit' => '購入',
        'principal_return_credit' => '元本返還',
        'withdrawal_debit' => '出金',
        'withdrawal_refund' => '出金返金',
    ],
    'status' => [
        'completed' => '完了',
        'pending' => '保留中',
        'approved' => '承認済み',
        'rejected' => '却下',
        'refunded' => '返金済み',
        'cancelled' => 'キャンセル済み',
    ],
    'empty' => '取引履歴はまだありません',
];

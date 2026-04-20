<?php

return [
    'html_lang' => 'ja',
    'meta_title' => '注文 | :app_name',
    'title' => '注文',
    'intro' => '現在のポジションと最近の収益履歴を確認できます。',
    'positions' => [
        'title' => 'ポジション',
        'empty' => '有効なポジションはまだありません',
        'principal_prefix' => '元本: ',
        'view_order' => '注文を見る',
        'recent_profit_title' => '直近3日間の収益',
        'recent_profit_empty' => '収益履歴はまだありません',
        'status' => [
            'open' => '進行中',
            'redeeming' => '償還中',
            'redeemed' => '償還済み',
        ],
    ],
    'reservations' => [
        'title' => '予約注文',
        'badge' => '予約',
        'empty' => '予約注文はまだありません',
        'amount_prefix' => '金額: ',
        'status' => [
            'pending' => '審査待ち',
            'approved' => '承認済み',
            'rejected' => '却下',
            'converted' => '購入に変換済み',
            'cancelled' => 'キャンセル済み',
        ],
    ],
];

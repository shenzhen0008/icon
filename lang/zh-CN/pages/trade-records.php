<?php

return [
    'html_lang' => 'zh-CN',
    'meta_title' => '交易记录 | :app_name',
    'title' => '交易记录',
    'intro' => '首页组件专属交易记录页面（:mode）。',
    'back_home' => '返回首页',
    'mode' => [
        'demo' => 'DEMO',
        'live' => 'LIVE',
    ],
    'columns' => [
        'type' => '类型',
        'content' => '内容',
        'amount_usdt' => '金额(USDT)',
        'status' => '状态',
        'time' => '时间',
        'time_mobile' => '时间',
    ],
    'event_type' => [
        'purchase_debit' => '购买',
        'principal_return_credit' => '本金返还',
        'withdrawal_debit' => '提款',
        'withdrawal_refund' => '提款退款',
    ],
    'status' => [
        'completed' => '已完成',
        'pending' => '待处理',
        'approved' => '已通过',
        'rejected' => '已拒绝',
        'refunded' => '已退款',
        'cancelled' => '已取消',
    ],
    'empty' => '暂无交易记录',
];

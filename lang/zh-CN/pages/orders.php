<?php

return [
    'html_lang' => 'zh-CN',
    'meta_title' => '订单 | :app_name',
    'title' => '订单',
    'intro' => '查看当前持仓与最近收益记录。',
    'positions' => [
        'title' => '持仓产品',
        'empty' => '暂无持仓产品',
        'principal_prefix' => '本金：',
        'view_order' => '查看订单',
        'recent_profit_title' => '最近3天收益',
        'recent_profit_empty' => '暂无收益记录',
        'status' => [
            'open' => '持有中',
            'redeeming' => '赎回中',
            'redeemed' => '已赎回',
        ],
    ],
    'reservations' => [
        'title' => '预订订单',
        'badge' => '预订订单',
        'empty' => '暂无预订订单',
        'amount_prefix' => '金额：',
        'status' => [
            'pending' => '待审核',
            'approved' => '审核通过',
            'rejected' => '已拒绝',
            'converted' => '已转购买',
            'cancelled' => '已取消',
        ],
    ],
];

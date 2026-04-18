<?php

return [
    'html_lang' => 'ko',
    'meta_title' => '거래 내역 | Icon Market',
    'title' => '거래 내역',
    'intro' => '홈 패널(:mode) 전용 거래 내역 페이지입니다.',
    'back_home' => '홈으로 돌아가기',
    'mode' => [
        'demo' => '데모',
        'live' => '라이브',
    ],
    'columns' => [
        'type' => '유형',
        'content' => '내용',
        'amount_usdt' => '금액 (USDT)',
        'status' => '상태',
        'time' => '시간',
        'time_mobile' => '시간',
    ],
    'event_type' => [
        'purchase_debit' => '구매',
        'withdrawal_debit' => '출금',
        'withdrawal_refund' => '출금 환불',
    ],
    'status' => [
        'completed' => '완료',
        'pending' => '대기 중',
        'approved' => '승인됨',
        'rejected' => '거절됨',
        'refunded' => '환불됨',
        'cancelled' => '취소됨',
    ],
    'empty' => '거래 내역이 없습니다',
];

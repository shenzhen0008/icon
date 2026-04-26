<?php

return [
    'html_lang' => 'de',
    'meta_title' => 'Handelsverlauf | :app_name',
    'title' => 'Handelsverlauf',
    'intro' => 'Spezielle Handelsverlaufsseite für das Startseiten-Panel (:mode).',
    'back_home' => 'Zurück zur Startseite',
    'mode' => [
        'demo' => 'DEMO',
        'live' => 'LIVE',
    ],
    'columns' => [
        'type' => 'Typ',
        'content' => 'Inhalt',
        'amount_usdt' => 'Betrag (USDT)',
        'status' => 'Status',
        'time' => 'Zeit',
        'time_mobile' => 'Zeit',
    ],
    'event_type' => [
        'purchase_debit' => 'Kauf',
        'principal_return_credit' => 'Kapitalrückzahlung',
        'withdrawal_debit' => 'Auszahlung',
        'withdrawal_refund' => 'Auszahlungsrückerstattung',
    ],
    'status' => [
        'completed' => 'Abgeschlossen',
        'pending' => 'Ausstehend',
        'approved' => 'Genehmigt',
        'rejected' => 'Abgelehnt',
        'refunded' => 'Erstattet',
        'cancelled' => 'Storniert',
    ],
    'empty' => 'Noch kein Handelsverlauf vorhanden',
];

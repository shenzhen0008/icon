<?php

return [
    'html_lang' => 'fr',
    'meta_title' => 'Historique des transactions | :app_name',
    'title' => 'Historique des transactions',
    'intro' => 'Page dédiée à l’historique des transactions pour le panneau d’accueil (:mode).',
    'back_home' => 'Retour à l’accueil',
    'mode' => [
        'demo' => 'DÉMO',
        'live' => 'LIVE',
    ],
    'columns' => [
        'type' => 'Type',
        'content' => 'Contenu',
        'amount_usdt' => 'Montant (USDT)',
        'status' => 'Statut',
        'time' => 'Heure',
        'time_mobile' => 'Heure',
    ],
    'event_type' => [
        'purchase_debit' => 'Achat',
        'principal_return_credit' => 'Retour du principal',
        'withdrawal_debit' => 'Retrait',
        'withdrawal_refund' => 'Remboursement de retrait',
    ],
    'status' => [
        'completed' => 'Terminé',
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
        'refunded' => 'Remboursé',
        'cancelled' => 'Annulé',
    ],
    'empty' => 'Aucune transaction pour le moment',
];

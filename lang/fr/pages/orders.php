<?php

return [
    'html_lang' => 'fr',
    'meta_title' => 'Ordres | :app_name',
    'title' => 'Ordres',
    'intro' => 'Consultez vos positions en cours et vos derniers enregistrements de profits.',
    'positions' => [
        'title' => 'Positions',
        'empty' => 'Aucune position active pour le moment',
        'principal_prefix' => 'Principal : ',
        'view_order' => 'Voir l’ordre',
        'recent_profit_title' => 'Profit des 3 derniers jours',
        'recent_profit_empty' => 'Aucun enregistrement de profit',
        'status' => [
            'open' => 'Ouvert',
            'redeeming' => 'Rachat en cours',
            'redeemed' => 'Racheté',
        ],
    ],
    'reservations' => [
        'title' => 'Ordres de réservation',
        'badge' => 'Réservation',
        'empty' => 'Aucun ordre de réservation pour le moment',
        'amount_prefix' => 'Montant : ',
        'status' => [
            'pending' => 'En attente de validation',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'converted' => 'Converti en achat',
            'cancelled' => 'Annulé',
        ],
    ],
];

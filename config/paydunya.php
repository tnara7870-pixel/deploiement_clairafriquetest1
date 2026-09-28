<?php

return [
    'master_key' => env('PAYDUNYA_MASTER_KEY'),
    'private_key' => env('PAYDUNYA_PRIVATE_KEY'),
    'public_key' => env('PAYDUNYA_PUBLIC_KEY'),
    'token' => env('PAYDUNYA_TOKEN'),
    'mode' => env('PAYDUNYA_MODE', 'test'),
    'store' => [
        'name' => env('NOM_BOUTIQUE', 'ClaireAfrique'),
        'tagline' => 'Librairie Papeterie Dakar',
        'phone_number' => env('WAVE_NUMERO', '+221770000000'),
        'postal_address' => 'Dakar, Sénégal',
        'logo_url' => '',
        'website_url' => env('APP_URL', 'http://127.0.0.1:8000'),
    ],
    'cancel_url' => env('APP_URL').'/catalogue/paiement/annulation',
    'return_url' => env('APP_URL').'/catalogue/paiement/retour',
    'ipn_url' => env('APP_URL').'/catalogue/paiement/ipn',
];

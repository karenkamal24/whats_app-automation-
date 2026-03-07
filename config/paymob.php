<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paymob API Configuration
    |--------------------------------------------------------------------------
    */

    'base_url'    => env('PAYMOB_BASE_URL', 'https://accept.paymob.com'),
    'api_key'     => env('PAYMOB_API_KEY'),
    'secret_key'  => env('PAYMOB_SECRET_KEY'),
    'public_key'  => env('PAYMOB_PUBLIC_KEY'),

    'integration_id' => env('PAYMOB_INTEGRATION_ID'),
    'iframe_id'      => env('PAYMOB_IFRAME_ID'),
    'hmac_secret'    => env('PAYMOB_HMAC_SECRET'),

    'currency' => env('PAYMOB_CURRENCY', 'EGP'),

];

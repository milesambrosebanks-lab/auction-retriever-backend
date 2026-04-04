<?php

// return [


//     'paths' => ['api/*', 'sanctum/csrf-cookie', 'graphql'],

//     'allowed_methods' => ['*'],

//     'allowed_origins' => ['*'],

//     'allowed_origins_patterns' => [],

//     'allowed_headers' => ['*'],

//     'exposed_headers' => [],

//     'max_age' => 0,

//     'supports_credentials' => false,

// ];

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000', // React
        'http://localhost:5173', // Vite
        'https://auctionretriever.com',
        'https://auction-retriver-beta.vercel.app'
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // JWT হলে false
];

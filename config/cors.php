<?php

return [
    'paths' => ['api/*', 'login', 'register', 'logout'], // any endpoints you call cross-origin
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://cal-web.test',        // your frontend
        'https://calendar.kewlor.com',
        'http://127.0.0.1:8100',
        'http://localhost:8100',
        'http://localhost:3000',     // add your dev host if needed
        'http://localhost:5173',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // You are using JWT, not cookies, so keep this false
    'supports_credentials' => false,
];

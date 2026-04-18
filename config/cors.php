<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | This file controls what external domains can access your API.
    | It is important when your frontend (Angular, React, etc.) is
    | running on a different domain or port.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // GET, POST, PUT, PATCH, DELETE

    'allowed_origins' => explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:3000,http://localhost:4200,http://localhost:5173,http://localhost:5500,http://127.0.0.1:3000,http://127.0.0.1:4200,http://127.0.0.1:5173,http://127.0.0.1:5500,http://localhost:8000,http://127.0.0.1:8000'
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

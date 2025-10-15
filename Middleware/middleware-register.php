<?php
return [
    // runs on EVERY request
    'global' => [
        \Middleware\SessionsSetupMiddleware::class,
        \Middleware\MiddlewareA::class,
        \Middleware\MiddlewareB::class,
        \Middleware\MiddlewareC::class,
        \Middleware\CSRFMiddleware::class,
        \Middleware\HttpLoggingMiddleware::class, // keep your HTTP logger
    ],

    // short names usable by routes
    'aliases' => [
        'auth'  => \Middleware\AuthenticatedMiddleware::class,
        'guest' => \Middleware\GuestMiddleware::class,
    ],
];

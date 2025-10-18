<?php
return [
    'global' => [
        \Middleware\SessionsSetupMiddleware::class,
        \Middleware\MiddlewareA::class,
        \Middleware\MiddlewareB::class,
        \Middleware\MiddlewareC::class,
        \Middleware\CSRFMiddleware::class,
    ],
    'aliases' => [
        'auth'      => \Middleware\AuthenticatedMiddleware::class,
        'guest'     => \Middleware\GuestMiddleware::class,
        'signature' => \Middleware\SignatureValidationMiddleware::class,
    ],
];


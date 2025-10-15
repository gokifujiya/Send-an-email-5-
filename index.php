<?php
// 1) Start session for every request (needed for Authenticate / FlashData)
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Optionally harden cookies:
    // session_set_cookie_params(['httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS'])]);
    session_start();
}

// 2) Bootstrap app + routes
require __DIR__.'/init-app.php';
$routes = [];
require __DIR__.'/Routing/routes.php';

// 3) Resolve path (accept both "/register" and "register" keys)
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = $path === '' ? '/' : $path;
$candidates = [$path, ltrim($path, '/'), '/'.ltrim($path, '/')];

$handler = null;
foreach ($candidates as $c) {
    if (isset($routes[$c])) { $handler = $routes[$c]; break; }
}

if (!$handler) {
    http_response_code(404);
    echo "404 Not Found: The requested route was not found on this server.";
    exit;
}

// 4) Execute handler and render response
$response = $handler();

// If it looks like an HTTPRenderer, send headers + content
if (is_object($response)) {
    if (method_exists($response, 'getFields')) {
        foreach ($response->getFields() as $k => $v) {
            header("$k: $v");
        }
    }
    if (method_exists($response, 'getContent')) {
        echo $response->getContent();
        exit;
    }
    if (method_exists($response, 'render')) {
        echo $response->render();
        exit;
    }
}

// Fallback: string/echo-able responses
if (is_string($response)) {
    echo $response;
    exit;
}

http_response_code(500);
echo "Invalid response from route handler.";

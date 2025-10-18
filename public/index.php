<?php
declare(strict_types=1);

// public/index.php (top, before routes are loaded)
require_once __DIR__ . '/../Helpers/Settings.php';
\Helpers\Settings::load(__DIR__ . '/../.env');

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/..'));
spl_autoload_extensions(".php");
spl_autoload_register(function($class) {
    $file = realpath(__DIR__ . '/..') . '/'  . str_replace('\\', '/', $class). '.php';
    if (file_exists($file)) include($file);
});

$DEBUG = true;

// serve static files in dev
if (preg_match('/\.(?:png|jpg|jpeg|gif|svg|js|css|ico|html)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

// load routes (returns array<string, \Routing\Route>)
$routes = include('Routing/routes.php');

// parse normalized path
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$path = ltrim($path, '/');

if (isset($routes[$path])) {
    $route = $routes[$path];

    try {
        if (!($route instanceof \Routing\Route)) {
            throw new \InvalidArgumentException("Invalid route type");
        }

        // build middleware stack: global + route aliases
        $reg = include('Middleware/middleware-register.php');
        $global  = $reg['global']  ?? [];
        $aliases = $reg['aliases'] ?? [];

        $routeStack = [];
        foreach ($route->getMiddleware() as $alias) {
            if (!isset($aliases[$alias])) {
                throw new \RuntimeException("Unknown middleware alias: {$alias}");
            }
            $routeStack[] = $aliases[$alias];
        }

        $stack = array_merge($global, $routeStack);
        $handler = new \Middleware\MiddlewareHandler(array_map(fn($c) => new $c(), $stack));

        // last callable is the route callback, which returns an HTTPRenderer
        $renderer = $handler->run($route->getCallback());

        // output headers + content
        foreach ($renderer->getFields() as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $renderer->getContent();

    } catch (\Throwable $e) {
        http_response_code(500);
        echo "Internal error, please contact the admin.<br>";
        if ($DEBUG) echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
} else {
    http_response_code(404);
    echo "404 Not Found: The requested route was not found on this server.";
}


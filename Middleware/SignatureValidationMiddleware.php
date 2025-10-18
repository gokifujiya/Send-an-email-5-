<?php
namespace Middleware;

use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;

class SignatureValidationMiddleware implements Middleware
{
    public function handle(callable $next): HTTPRenderer
    {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUrl = parse_url($currentPath);
        $pathOnly  = $parsedUrl['path'] ?? '/';

        // Build a Route object for the current path
        $route = Route::create($pathOnly, function () {});

        // Compose "host + currentPath" (no scheme); Route will normalize
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $full = $host . $currentPath;

        if ($route->isSignedURLValid($full, /* absolute */ false)) {
            // Good signature → continue chain
            // Also tell bots not to index these signed links
            header('X-Robots-Tag: noindex, nofollow');
            return $next();
        }

        // Bad/expired signature → redirect somewhere safe
        FlashData::setFlashData('error', sprintf("Invalid URL (%s).", $pathOnly));
        return new RedirectRenderer('random/part');
    }
}

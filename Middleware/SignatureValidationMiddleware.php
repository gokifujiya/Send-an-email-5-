<?php
namespace Middleware;

use Helpers\ValidationHelper;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;

class SignatureValidationMiddleware implements Middleware
{
    public function handle(callable $next): HTTPRenderer
    {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        $parsedUrl = parse_url($currentPath);
        $pathWithoutQuery = $parsedUrl['path'] ?? '';

        // Build a Route for the current path
        $route = Route::create($pathWithoutQuery, function(){});

        // Validate signature first (includes all params except 'signature')
        if ($route->isSignedURLValid(($_SERVER['HTTP_HOST'] ?? 'localhost') . $currentPath, /*absolute*/ false)) {

            // Optional: enforce expiration if present (supports 'expiration' or 'exp')
            $now = time();
            $hasExpiration = false;

            if (isset($_GET['expiration'])) {
                $expires = (int) $_GET['expiration'];   // ← replaced ValidationHelper::integer(...)               
                $hasExpiration = true;
                if ($expires < $now) {
                    FlashData::setFlashData('error', "The URL has expired.");
                    return new RedirectRenderer('random/part');
                }
            } elseif (isset($_GET['exp'])) {
                $expires = (int) $_GET['exp'];          // ← replaced ValidationHelper::integer(...)
                $hasExpiration = true;
                if ($expires < $now) {
                    FlashData::setFlashData('error', "The URL has expired.");
                    return new RedirectRenderer('random/part');
                }
            }

            // If signature is valid (and not expired if an expiry param exists), continue
            return $next();
        }

        // Invalid signature → bounce
        FlashData::setFlashData('error', sprintf("Invalid URL (%s).", $pathWithoutQuery));
        return new RedirectRenderer('random/part');
    }
}


<?php
namespace Middleware;

use Response\HTTPRenderer;

class SessionsSetupMiddleware implements Middleware {
  public function handle(callable $next): HTTPRenderer {
    error_log('Setting up sessions...');
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    return $next();
  }
}


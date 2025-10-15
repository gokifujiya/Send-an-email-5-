# CSRF-Protection-Middleware-3-

This project adds a CSRF (Cross-Site Request Forgery) protection layer to a PHP middleware architecture.
It uses **one token per session**, following OWASP recommendations, ensuring that all non-GET requests
carry a valid CSRF token generated on the server.

## Features
- CSRFMiddleware class that validates CSRF tokens on every non-GET request.
- Tokens are stored in session and embedded into all HTML forms.
- Secure, per-session CSRF tokens generated with 32 random bytes (64 hex chars).
- Integration with existing middleware pipeline and FlashData redirect handling.
- Example pages: `/login`, `/register`, and `/update/part`.

## Security Recommendations
Add these session settings to your session setup middleware for safer cookies:
```php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax'); // or 'Strict'
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', '1'); // enable only on HTTPS
}
```

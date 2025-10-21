# URL-Signature-Verification-Middleware-5-

This project demonstrates a time-limited **Signed URL Verification System** implemented in PHP.

## Features
- Generates signed URLs for protected resources
- Middleware verifies URL signatures and expiration times
- Supports query parameter `lasts` to define temporary link duration
- Redirects unauthorized or expired requests
- Includes secure file serving with fallback images

## Testing
1. Start local server:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

2. Generate signed URL:
   ```bash
   http://127.0.0.1:8000/test/share/files/jpg/generate-url?user=233&filename=elephant&lasts=30
   ```

3. Open the returned URL in your browser.
- ✅ Displays image before expiration
-  ⛔ Redirects after expiration or signature tampering

## Files 
- Middleware/SignatureValidationMiddleware.php — verifies signature and expiration
- Routing/Route.php — generates and validates signed URLs
- Routing/routes.php — defines the test routes
- Response/Render/MediaRenderer.php — displays media safely
- .env — holds SIGNATURE_SECRET_KEY and other configs (excluded from Git)

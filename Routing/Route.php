<?php
namespace Routing;

use Helpers\Settings;
use InvalidArgumentException;

class Route
{
    private string $path;
    private $handler;              // ?callable
    private array $middleware = []; // aliases like ['signature', 'auth']

    private function __construct(string $path, ?callable $handler = null)
    {
        $this->path = '/' . ltrim($path, '/');
        $this->handler = $handler;
    }

    public static function create(string $path, ?callable $handler = null): self
    {
        return new self($path, $handler);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setMiddleware(array $aliases): self
    {
        $this->middleware = array_values($aliases);
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getCallback(): ?callable
    {
        return $this->handler;
    }

    /**
     * Host + path (no scheme)
     */
    private function getBaseURL(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $host . $this->getPath(); // e.g. "example.com/test/share/files/jpg"
    }

    private function getSecretKey(): string
    {
        $key = (string) Settings::env('SIGNATURE_SECRET_KEY', '');
        if ($key === '') {
            throw new InvalidArgumentException('SIGNATURE_SECRET_KEY is not set in .env');
        }
        return $key;
    }

    /**
     * Create a signed URL for this route: adds exp and signature.
     */
    public function getSignedURL(array $queryParameters, int $ttlSeconds = 3600): string
    {
        $secret = $this->getSecretKey();
        $base   = $this->getBaseURL();

        $queryParameters['exp'] = time() + $ttlSeconds;

        $unsigned = $this->buildCanonical($base, $queryParameters);
        $sig      = hash_hmac('sha256', $unsigned, $secret);

        return $this->appendQuery($unsigned, ['signature' => $sig]);
    }

    /**
     * Verify signature + not expired.
     */
    public function isSignedURLValid(string $url, bool $absolute = true): bool
    {
        $secret = $this->getSecretKey();

        // normalize scheme if needed
        if (!$absolute || !preg_match('#^https?://#i', $url)) {
            $preferred = Settings::env('APP_SCHEME')
                ?: ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
            $url = $preferred . '://' . ltrim($url, '/');
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['query'])) {
            return false;
        }

        parse_str($parts['query'], $params);

        if (!isset($params['signature'], $params['exp'])) {
            return false;
        }

        if (!ctype_digit((string)$params['exp']) || time() >= (int)$params['exp']) {
            return false; // expired or invalid exp
        }

        $providedSig = (string)$params['signature'];
        unset($params['signature']);

        $scheme = $parts['scheme'] ?? (Settings::env('APP_SCHEME') ?: 'https');
        $host   = $parts['host']   ?? 'localhost';
        $path   = $parts['path']   ?? '/';
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';

        $unsigned  = $this->buildCanonical("{$scheme}://{$host}{$port}{$path}", $params);
        $expected  = hash_hmac('sha256', $unsigned, $secret);

        return hash_equals($expected, $providedSig);
    }

    /**
     * Build absolute canonical URL (scheme://host[:port]/path?sorted=query)
     */
    private function buildCanonical(string $base, array $params = []): string
    {
        // Ensure scheme
        if (!preg_match('#^https?://#i', $base)) {
            $preferred = Settings::env('APP_SCHEME')
                ?: ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
            $base = $preferred . '://' . ltrim($base, '/');
        }

        $parts = parse_url($base);
        parse_str($parts['query'] ?? '', $existing);

        $merged = array_merge($existing, $params);
        ksort($merged);

        $query  = http_build_query($merged, '', '&', PHP_QUERY_RFC3986);
        $scheme = $parts['scheme'] ?? 'https';
        $host   = $parts['host']   ?? 'localhost';
        $path   = $parts['path']   ?? '/';
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';

        return "{$scheme}://{$host}{$port}{$path}" . ($query ? "?{$query}" : '');
    }

    /**
     * Append params (sorted) to a URL (absolute or canonical).
     */
    private function appendQuery(string $url, array $more): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $q);

        $q = array_merge($q, $more);
        ksort($q);

        $query  = http_build_query($q, '', '&', PHP_QUERY_RFC3986);
        $scheme = $parts['scheme'] ?? 'https';
        $host   = $parts['host']   ?? 'localhost';
        $path   = $parts['path']   ?? '/';
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';

        return "{$scheme}://{$host}{$port}{$path}" . ($query ? "?{$query}" : '');
    }
}


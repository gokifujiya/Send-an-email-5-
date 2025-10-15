<?php
namespace Middleware;

use Response\HTTPRenderer;

class HttpLoggingMiddleware implements Middleware
{
    private string $logDriver;
    private string $logPath;

    public function __construct(string $driver = 'file', ?string $path = null)
    {
        $this->logDriver = $driver;
        $this->logPath   = $path ?? __DIR__ . '/../storage/logs/http-' . date('Y-m-d') . '.log';
    }

    public function handle(callable $next): HTTPRenderer
    {
        $start = microtime(true);

        // ---- Request ----
        $reqInfo = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method'    => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'url'       => $_SERVER['REQUEST_URI'] ?? '',
            'query'     => $_SERVER['QUERY_STRING'] ?? '',
            'headers'   => $this->getHeaders(),
        ];
        $this->log('REQUEST', $reqInfo);

        // Proceed
        $response = $next();

        // ---- Response ----
        $duration = round((microtime(true) - $start) * 1000, 2);
        $resInfo = [
            'timestamp'   => date('Y-m-d H:i:s'),
            'status_code' => http_response_code(),
            'duration_ms' => $duration,
            'headers'     => headers_list(),
        ];
        $this->log('RESPONSE', $resInfo);

        return $response;
    }

    private function getHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name = str_replace('_', '-', substr($k, 5));
                $headers[$name] = $v;
            }
        }
        return $headers;
    }

    private function log(string $type, array $data): void
    {
        $line = sprintf("[%s] %s %s\n", $data['timestamp'] ?? date('Y-m-d H:i:s'), $type, json_encode($data, JSON_UNESCAPED_SLASHES));

        if ($this->logDriver === 'stdout') {
            // writes to the PHP built-in server console
            error_log(rtrim($line));
            return;
        }

        // file driver (default)
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->logPath, $line, FILE_APPEND);
    }
}


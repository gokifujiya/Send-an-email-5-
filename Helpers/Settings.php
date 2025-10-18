<?php
namespace Helpers;

final class Settings
{
    /** In-process cache of env vars loaded from .env */
    private static array $env = [];

    /**
     * Load a .env file (KEY=VALUE per line). Lines starting with "#" are comments.
     * Supports quoted values: KEY="some value"
     */
    public static function load(string $envPath): void
    {
        if (!is_file($envPath) || !is_readable($envPath)) {
            return; // silently ignore if no .env (ok in prod/CI)
        }
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Split KEY=VALUE on the first '='
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));

            // Strip optional surrounding quotes
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }

            self::$env[$key] = $val;
            $_ENV[$key] = $val;          // allow getenv()/$_ENV access
            putenv("$key=$val");         // optional: expose to child processes
        }
    }

    /**
     * Read a value from loaded .env/ENV; returns $default if not set.
     */
    public static function env(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$env)) {
            return self::$env[$key];
        }
        $fromEnv = $_ENV[$key] ?? getenv($key);
        return ($fromEnv === false || $fromEnv === null) ? $default : $fromEnv;
    }
}


<?php
namespace Helpers;

final class Settings
{
    private static bool $loaded = false;

    private static function ensureLoaded(): void
    {
        if (self::$loaded) return;
        $path = __DIR__ . '/../.env';
        if (is_readable($path)) {
            // very small .env loader (KEY=VALUE, no quotes)
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if ($line[0] === '#' || !str_contains($line, '=')) continue;
                [$k, $v] = array_map('trim', explode('=', $line, 2));
                putenv("$k=$v");
            }
        }
        self::$loaded = true;
    }

    public static function env(string $key, ?string $default = null): ?string
    {
        self::ensureLoaded();
        $val = getenv($key);
        return $val === false ? $default : $val;
    }
}

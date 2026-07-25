<?php
namespace Core;

class Config
{
    private static array $loaded = [];
    private static array $cache = [];

    public static function load(string $envPath): void
    {
        $envPath = rtrim($envPath, '/') . '/.env';
        $key = md5($envPath);

        if (isset(self::$loaded[$key])) {
            return;
        }

        if (!file_exists($envPath)) {
            self::$loaded[$key] = true;
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, '"\'');
            self::$cache[$name] = $value;
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }

        self::$loaded[$key] = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$cache[$key]
            ?? $_ENV[$key]
            ?? getenv($key)
            ?: $default;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function isDevelopment(): bool
    {
        return self::get('APP_ENV', 'production') === 'development';
    }

    public static function isProduction(): bool
    {
        return !self::isDevelopment();
    }

    public static function all(): array
    {
        return self::$cache;
    }
}

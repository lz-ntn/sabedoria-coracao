<?php
/**
 * Funcoes globais compartilhadas entre projetos
 */

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \Core\Csrf::field();
    }
}

if (!function_exists('csrf_validar')) {
    function csrf_validar(): void
    {
        \Core\Csrf::validateOrFail();
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return \Core\Config::get($key, $default);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never
    {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('db_ssl_options')) {
    function db_ssl_options(bool $development = false): array
    {
        if ($development) {
            return [];
        }

        $candidates = [];

        $envCa = (string)\Core\Config::get('DB_SSL_CA', '');
        if ($envCa !== '' && file_exists($envCa)) {
            $candidates[] = $envCa;
        }

        $candidates[] = __DIR__ . '/../ssl/ca-certificates.crt';

        $candidates[] = '/etc/ssl/certs/ca-certificates.crt';
        $candidates[] = '/etc/pki/tls/certs/ca-bundle.crt';
        $candidates[] = '/etc/ssl/ca-bundle.pem';

        foreach ($candidates as $caPath) {
            if (file_exists($caPath) && is_readable($caPath)) {
                return [PDO::MYSQL_ATTR_SSL_CA => $caPath];
            }
        }

        return [];
    }
}

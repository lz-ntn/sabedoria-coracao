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

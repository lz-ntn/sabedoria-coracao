<?php
namespace Core;

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_KEY] = $token;
        return $token;
    }

    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::TOKEN_KEY])) {
            return self::generate();
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    public static function validate(?string $token = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $token ?? ($_POST['_csrf_token'] ?? null);
        $expected = $_SESSION[self::TOKEN_KEY] ?? null;

        if ($token === null || $expected === null) {
            return false;
        }

        return hash_equals($expected, $token);
    }

    public static function validateOrFail(): void
    {
        if (!self::validate()) {
            if (str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
                http_response_code(403);
                echo json_encode(['error' => 'Token CSRF invalido. Recarregue a pagina e tente novamente.']);
                exit;
            }
            $_SESSION['_csrf_error'] = 'Token de seguranca invalido. Tente novamente.';
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$referer}");
            exit;
        }
    }

    public static function rotate(): void
    {
        self::generate();
    }
}

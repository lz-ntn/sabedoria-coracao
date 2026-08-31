<?php
/**
 * Health Check — O Caminho: Saberes Ancestrais
 *
 * GET /api/health.php — retorna JSON com status da aplicação e do banco.
 * Usado pelos health checks do Render (healthCheckPath) para decidir se o
 * serviço está saudável. Retorna HTTP 200 quando OK, 503 se o banco caiu.
 *
 * ⚠️ Usa uma conexão com TIMEOUT curto (e não o singleton Database) para que
 * este endpoint NUNCA trave o servidor `php -S` (single-thread) quando o banco
 * estiver inacessível.
 */

require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$dados = [
    'status'  => 'ok',
    'service' => 'caminho-saberes',
    'version' => defined('APP_VERSION') ? APP_VERSION : '2.0.0',
    'env'     => defined('APP_ENV') ? APP_ENV : 'production',
    'time'    => time(),
];

$host = \Core\Config::get('DB_HOST', '127.0.0.1');
$port = \Core\Config::get('DB_PORT', '3306');
$name = \Core\Config::get('DB_NAME', 'database');
$user = \Core\Config::get('DB_USER', 'root');
$pass = \Core\Config::get('DB_PASS', '');
$charset = \Core\Config::get('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT            => 3,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $row = $pdo->query('SELECT 1 AS ok')->fetch();
    $dados['db'] = $row ? 'ok' : 'error';
} catch (Throwable $e) {
    $dados['status'] = 'error';
    $dados['db']     = 'unavailable';
    http_response_code(503);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

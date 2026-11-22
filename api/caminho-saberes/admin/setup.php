<?php
/**
 * admin/setup.php — Cria/redefine o admin via HTTP (para planos sem Shell, ex: Render Free).
 *
 * 1. No Render (dashboard), vá em Environment e adicione a variável:
 *    ADMIN_SETUP_TOKEN = <qualquer valor secreto, ex: sabedoria-setup-2026>
 *    Faça redeploy (salvar já dispara o redeploy automático).
 *
 * 2. Rode (navegador ou curl):
 *    POST https://saberes-de-coracao.onrender.com/admin/setup.php
 *    Header: Authorization: Bearer <seu_token>
 *    Ou:   https://saberes-de-coracao.onrender.com/admin/setup.php?token=<seu_token>
 *
 * 3. Body opcional (JSON ou form):
 *    { "usuario": "admin", "senha": "MinhaSenha123", "email": "admin@saberes.com" }
 *
 * Retorna JSON com usuário, email e senha (se gerada aleatoriamente).
 *
 * ⚠️ Após criar o admin, você pode remover a variável ADMIN_SETUP_TOKEN do Render
 * e fazer novo redeploy para desativar este endpoint.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$expected = (string)\Core\Config::get('ADMIN_SETUP_TOKEN', '');
$received = $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['token'] ?? '';
if ($received !== '' && str_starts_with($received, 'Bearer ')) {
    $received = substr($received, 7);
}
if (!$expected || !hash_equals($expected, $received)) {
    http_response_code(403);
    echo json_encode(['error' => 'não autorizado']);
    exit;
}

$usuario = $_POST['usuario'] ?? $_GET['usuario'] ?? \Core\Config::get('ADMIN_USER', 'admin');
$email   = $_POST['email']   ?? $_GET['email']   ?? \Core\Config::get('ADMIN_EMAIL', 'admin@saberes.com');
$senhaDefinida = !empty($_POST['senha']) || \Core\Config::has('ADMIN_PASSWORD');
$senha = !empty($_POST['senha']) ? $_POST['senha']
    : (\Core\Config::has('ADMIN_PASSWORD') ? \Core\Config::get('ADMIN_PASSWORD') : bin2hex(random_bytes(12)));

$hash = password_hash($senha, PASSWORD_BCRYPT);
$db = \Core\Database::getInstance();

$sql = "INSERT INTO `admin` (`usuario`, `senha`, `email`)
        VALUES (:usuario, :senha, :email)
        ON DUPLICATE KEY UPDATE `senha` = VALUES(`senha`), `email` = VALUES(`email`)";
$stmt = $db->getPdo()->prepare($sql);
$stmt->execute([
    ':usuario' => $usuario,
    ':senha'   => $hash,
    ':email'   => $email,
]);

echo json_encode([
    'ok'   => true,
    'usuario' => $usuario,
    'email'   => $email,
    'senha'   => $senhaDefinida ? 'definida via variável de ambiente' : $senha,
    'url'     => '/admin/',
]);

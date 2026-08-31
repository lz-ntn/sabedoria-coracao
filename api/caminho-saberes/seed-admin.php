<?php
/**
 * seed-admin.php — Cria/atualiza o administrador d'O Caminho.
 *
 * Uso (a partir da raiz do app):
 *   php seed-admin.php
 *
 * Por padrão gera uma senha aleatória forte e a imprime UMA única vez.
 * Para definir uma senha própria (ex.: em deploy), use as variáveis de
 * ambiente abaixo no .env:
 *   ADMIN_USER     (default: admin)
 *   ADMIN_PASSWORD (default: senha aleatória)
 *   ADMIN_EMAIL    (default: admin@saberes.com)
 *
 * ⚠️ Rode pelo CLI (php), nunca exposto pela web.
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
    http_response_code(403);
    exit("Acesso negado — execute seed-admin.php apenas pelo CLI (php seed-admin.php).\n");
}

$usuario = env('ADMIN_USER', 'admin');
$email   = env('ADMIN_EMAIL', 'admin@saberes.com');

// TRUE apenas se ADMIN_PASSWORD foi realmente declarado no .env/ambiente.
$senhaDefinida = \Core\Config::has('ADMIN_PASSWORD');
$senha = $senhaDefinida ? env('ADMIN_PASSWORD', '') : bin2hex(random_bytes(12));

$hash = password_hash($senha, PASSWORD_BCRYPT);

$db = Database::getInstance();

// UPSERT: cria se não existir, atualiza a senha se já existir.
$sql = "INSERT INTO `admin` (`usuario`, `senha`, `email`)
        VALUES (:usuario, :senha, :email)
        ON DUPLICATE KEY UPDATE `senha` = VALUES(`senha`), `email` = VALUES(`email`)";
$stmt = $db->getPdo()->prepare($sql);
$stmt->execute([
    ':usuario' => $usuario,
    ':senha'   => $hash,
    ':email'   => $email,
]);

echo "✅ Admin configurado n'O Caminho.\n";
echo "   Usuário: {$usuario}\n";
echo "   Email  : {$email}\n";
if ($senhaDefinida) {
    echo "   Senha  : definida via env ADMIN_PASSWORD\n";
} else {
    echo "   Senha  : {$senha}   (gerada aleatoriamente — guarde-a bem)\n";
}
echo "\nAcesse /admin/ para entrar.\n";

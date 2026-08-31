<?php
/**
 * seed-admin.php — Cria/atualiza o administrador do Portal Saberes.
 *
 * Uso (a partir da raiz do app):
 *   php seed-admin.php
 *
 * Por padrão gera uma senha aleatória forte e a imprime UMA única vez.
 * Para definir uma senha própria (ex.: em deploy), use as variáveis de
 * ambiente abaixo no .env:
 *   ADMIN_EMAIL    (default: admin@saberes.com)
 *   ADMIN_PASSWORD (default: senha aleatória)
 *   ADMIN_NAME     (default: Administrador)
 *
 * ⚠️ Rode pelo CLI (php), nunca exposto pela web.
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
    http_response_code(403);
    exit("Acesso negado — execute seed-admin.php apenas pelo CLI (php seed-admin.php).\n");
}

$email = env('ADMIN_EMAIL', 'admin@saberes.com');
$nome  = env('ADMIN_NAME', 'Administrador');

// TRUE apenas se ADMIN_PASSWORD foi realmente declarado no .env/ambiente.
$senhaDefinida = \Core\Config::has('ADMIN_PASSWORD');
$senha = $senhaDefinida ? env('ADMIN_PASSWORD', '') : bin2hex(random_bytes(12));

$hash = password_hash($senha, PASSWORD_BCRYPT);

$db = Database::getInstance();

// UPSERT: cria se não existir, atualiza a senha se já existir.
$sql = "INSERT INTO `usuarios` (`nome`, `email`, `senha`, `nivel`)
        VALUES (:nome, :email, :senha, 'admin')
        ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `senha` = VALUES(`senha`), `nivel` = 'admin'";
$stmt = $db->getPdo()->prepare($sql);
$stmt->execute([
    ':nome'  => $nome,
    ':email' => $email,
    ':senha' => $hash,
]);

echo "✅ Admin configurado no Portal Saberes.\n";
echo "   Email  : {$email}\n";
echo "   Usuário: {$nome} (nivel: admin)\n";
if ($senhaDefinida) {
    echo "   Senha  : definida via env ADMIN_PASSWORD\n";
} else {
    echo "   Senha  : {$senha}   (gerada aleatoriamente — guarde-a bem)\n";
}
echo "\nAcesse /admin/ para entrar.\n";

<?php
/**
 * admin/setup.php — Provisionamento via HTTP (para planos sem Shell, ex.: Render Free).
 *
 * Faz, em uma única chamada:
 *   1. cria/redefine o usuário do admin;
 *   2. popula o conteúdo (biblioteca + lições) com os importadores idempotentes;
 *   3. devolve o estado do acervo (lições, artigos, categorias).
 *
 * ── Como usar ──────────────────────────────────────────────────────────────
 * 1. No painel do Render (Environment) defina:
 *      ADMIN_SETUP_TOKEN = <segredo qualquer, 16+ caracteres>
 *    Opcional, mas recomendado (torna o login determinístico):
 *      ADMIN_USER     = admin
 *      ADMIN_PASSWORD = <sua senha>
 *      ADMIN_EMAIL    = admin@saberes.com
 *    Salvar já dispara o redeploy.
 *
 * 2. Abra no navegador (ou curl):
 *      https://SEU-SITE/admin/setup.php?token=<segredo>
 *
 *    Ações parciais:
 *      ...&acao=admin     só cria/redefine o admin
 *      ...&acao=popular   só popula o conteúdo
 *      ...&acao=status    só mostra os contadores
 *      ...&forcar=1       redefine a senha de um admin já existente
 *                         (sem isso, admin existente não tem a senha trocada
 *                          a menos que ADMIN_PASSWORD/senha esteja definida)
 *
 * 3. ⚠️ Depois de usar, APAGUE a variável ADMIN_SETUP_TOKEN no Render e faça
 *    um novo redeploy: o endpoint passa a responder 403 e o segredo deixa de
 *    existir. Guarde a senha no gerenciador de senhas.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
set_time_limit(0);

/** Responde e encerra. */
function responder(array $dados, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// ── Portão: token ──────────────────────────────────────────────────────────
$tokenEsperado = (string) env('ADMIN_SETUP_TOKEN', '');
$tokenRecebido = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['token'] ?? $_POST['token'] ?? '');
if (str_starts_with($tokenRecebido, 'Bearer ')) {
    $tokenRecebido = substr($tokenRecebido, 7);
}

if ($tokenEsperado === '') {
    responder([
        'ok'   => false,
        'erro' => 'ADMIN_SETUP_TOKEN não está definido no ambiente. '
                . 'Defina essa variável no painel do Render e aguarde o redeploy.',
    ], 403);
}

if (!hash_equals($tokenEsperado, $tokenRecebido)) {
    // Mesma resposta para token vazio e token errado: não vira oráculo.
    responder(['ok' => false, 'erro' => 'Token inválido.'], 403);
}

$acao = (string) ($_GET['acao'] ?? $_POST['acao'] ?? 'tudo');
if (!in_array($acao, ['tudo', 'admin', 'popular', 'status'], true)) {
    responder(['ok' => false, 'erro' => 'Ação desconhecida. Use: tudo, admin, popular ou status.'], 400);
}

$resposta = ['ok' => true, 'acao' => $acao, 'ambiente' => APP_ENV];
$db = Database::getInstance();

// ── 1. Admin ───────────────────────────────────────────────────────────────
if ($acao === 'admin' || $acao === 'tudo') {
    $usuario = trim((string) ($_POST['usuario'] ?? $_GET['usuario'] ?? env('ADMIN_USER', 'admin')));
    $email   = trim((string) ($_POST['email']   ?? $_GET['email']   ?? env('ADMIN_EMAIL', 'admin@saberes.com')));

    $senhaEnv   = (string) env('ADMIN_PASSWORD', '');
    $senhaParam = (string) ($_POST['senha'] ?? $_GET['senha'] ?? '');
    $senha      = $senhaParam !== '' ? $senhaParam : $senhaEnv;
    $forcar     = isset($_GET['forcar']) || isset($_POST['forcar']);

    if ($usuario === '') {
        responder(['ok' => false, 'erro' => 'Usuário do admin vazio.'], 400);
    }
    if ($senha !== '' && mb_strlen($senha) < 8) {
        responder(['ok' => false, 'erro' => 'A senha do admin precisa ter ao menos 8 caracteres.'], 400);
    }

    $existente = $db->fetch('SELECT id FROM `admin` WHERE `usuario` = ?', [$usuario]);

    if ($existente && $senha === '' && !$forcar) {
        // Não sobrescreve a senha de um admin existente por acidente.
        $resposta['admin'] = [
            'usuario' => $usuario,
            'acao'    => 'mantido',
            'aviso'   => 'Admin já existe. Defina ADMIN_PASSWORD (ou &senha=) para redefinir, '
                       . 'ou use &forcar=1 para gerar uma senha nova.',
        ];
    } else {
        $senhaGerada = false;
        if ($senha === '') {
            $senha = bin2hex(random_bytes(12));
            $senhaGerada = true;
        }

        $sql = "INSERT INTO `admin` (`usuario`, `senha`, `email`)
                VALUES (:usuario, :senha, :email)
                ON DUPLICATE KEY UPDATE `senha` = VALUES(`senha`), `email` = VALUES(`email`)";
        $db->getPdo()->prepare($sql)->execute([
            ':usuario' => $usuario,
            ':senha'   => password_hash($senha, PASSWORD_BCRYPT),
            ':email'   => $email,
        ]);

        $resposta['admin'] = [
            'usuario'  => $usuario,
            'email'    => $email,
            'acao'     => $existente ? 'senha_redefinida' : 'criado',
            'senha'    => $senhaGerada ? $senha : 'a que você definiu em ADMIN_PASSWORD/senha',
            'login_em' => '/admin/',
        ];
        if ($senhaGerada) {
            $resposta['admin']['aviso'] = 'Senha gerada agora — copie e guarde. Ela não será mostrada de novo.';
        }
    }
}

// ── 2. Popular conteúdo ────────────────────────────────────────────────────
if ($acao === 'popular' || $acao === 'tudo') {
    $script = __DIR__ . '/../database/popular.php';
    ob_start();
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' 2>&1', $codigo);
    $log = (string) ob_get_clean();

    $resposta['importacao'] = [
        'exit' => $codigo,
        'ok'   => $codigo === 0,
        // Últimas linhas bastam para conferir; o log completo vai em 'log'.
        'log'  => $log,
    ];
}

// ── 3. Estado do acervo ────────────────────────────────────────────────────
$totais = $db->fetch(
    "SELECT SUM(`tipo` = 'licao') AS licoes,
            SUM(`tipo` = 'artigo') AS artigos,
            (SELECT COUNT(*) FROM `categorias`) AS categorias,
            SUM(`publicado_em` IS NOT NULL) AS publicados
       FROM `licoes`"
) ?: [];

$resposta['acervo'] = [
    'licoes'     => (int) ($totais['licoes'] ?? 0),
    'artigos'    => (int) ($totais['artigos'] ?? 0),
    'categorias' => (int) ($totais['categorias'] ?? 0),
    'publicados' => (int) ($totais['publicados'] ?? 0),
];

if ($acao === 'tudo') {
    $resposta['proximos_passos'] = [
        '1. Entre em /admin/ com o usuário e senha acima.',
        '2. Confira a home: os contadores e "Adicionados recentemente" já devem aparecer.',
        '3. APAGUE a variável ADMIN_SETUP_TOKEN no Render e faça um novo redeploy.',
    ];
}

responder($resposta);

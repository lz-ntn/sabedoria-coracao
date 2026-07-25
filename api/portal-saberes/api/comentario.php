<?php
/**
 * API: Postar Comentário
 * POST /api/comentario.php
 * Body: { artigo_id, conteudo, parent_id?, autor_nome?, autor_email? }
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$artigo_id = (int)($input['artigo_id'] ?? 0);
$conteudo = trim($input['conteudo'] ?? '');
$parent_id = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;
$autor_nome = trim($input['autor_nome'] ?? '');
$autor_email = trim($input['autor_email'] ?? '');

if (!$artigo_id || !$conteudo) {
    json_error('artigo_id e conteudo obrigatórios', 400);
}

if (mb_strlen($conteudo) < 3) {
    json_error('Comentário muito curto (mínimo 3 caracteres)', 400);
}

if (mb_strlen($conteudo) > 2000) {
    json_error('Comentário muito longo (máximo 2000 caracteres)', 400);
}

$db = Database::getInstance();
$ip = $_SERVER['REMOTE_ADDR'];
$usuario_id = esta_logado() ? ($_SESSION['usuario_id'] ?? null) : null;

if (!$usuario_id) {
    if (!$autor_nome || !$autor_email) {
        json_error('Nome e email obrigatórios para visitantes', 400);
    }
    if (!filter_var($autor_email, FILTER_VALIDATE_EMAIL)) {
        json_error('Email inválido', 400);
    }
    if (mb_strlen($autor_nome) < 2 || mb_strlen($autor_nome) > 100) {
        json_error('Nome deve ter entre 2 e 100 caracteres', 400);
    }
}

if ($parent_id) {
    $parent = $db->fetch('SELECT id, artigo_id FROM comentarios WHERE id = ?', [$parent_id]);
    if (!$parent || $parent['artigo_id'] != $artigo_id) {
        json_error('Comentário pai inválido', 400);
    }
}

$rate_key = 'comentario_' . $ip;
$recent = $db->fetch("
    SELECT COUNT(*) AS c FROM comentarios
    WHERE ip = ? AND criado_em > DATE_SUB(NOW(), INTERVAL 5 MINUTE)",
    [$ip]
);
if ($recent['c'] >= 5) {
    json_error('Muitas tentativas. Aguarde 5 minutos.', 429);
}

try {
    $autor_nome_db = $autor_nome;
    if ($usuario_id) {
        $user = $db->fetch('SELECT nome FROM usuarios WHERE id = ?', [$usuario_id]);
        $autor_nome_db = $user['nome'] ?? $autor_nome;
    }

    $id = $db->insert('comentarios', [
        'artigo_id' => $artigo_id,
        'usuario_id' => $usuario_id,
        'autor_nome' => $autor_nome_db,
        'autor_email' => $autor_email,
        'ip' => $ip,
        'conteudo' => $conteudo,
        'status' => 'aprovado',
        'parent_id' => $parent_id,
    ]);

    $comentario = $db->fetch('SELECT * FROM comentarios WHERE id = ?', [$id]);

    json_response([
        'success' => true,
        'comentario' => [
            'id' => $comentario['id'],
            'autor_nome' => $comentario['autor_nome'],
            'conteudo' => $comentario['conteudo'],
            'criado_em' => $comentario['criado_em'],
            'tempo_relativo' => tempo_relativo($comentario['criado_em']),
            'parent_id' => $comentario['parent_id'],
            'usuario_id' => $comentario['usuario_id'],
            'likes' => 0,
        ]
    ]);
} catch (Exception $e) {
    json_error('Erro ao postar comentário', 500);
}

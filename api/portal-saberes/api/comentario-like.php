<?php
/**
 * API: Curtir Comentário
 * POST /api/comentario-like.php
 * Body: { comentario_id }
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$comentario_id = (int)($input['comentario_id'] ?? 0);
$acao = $input['acao'] ?? 'toggle';

if (!$comentario_id) {
    json_error('comentario_id obrigatório', 400);
}

$db = Database::getInstance();
$ip = $_SERVER['REMOTE_ADDR'];
$usuario_id = esta_logado() ? ($_SESSION['usuario_id'] ?? null) : null;

try {
    $existente = null;
    if ($usuario_id) {
        $existente = $db->fetch('SELECT id FROM comentario_likes WHERE comentario_id = ? AND usuario_id = ?', [$comentario_id, $usuario_id]);
    } else {
        $existente = $db->fetch('SELECT id FROM comentario_likes WHERE comentario_id = ? AND ip = ? AND usuario_id IS NULL', [$comentario_id, $ip]);
    }

    if ($acao === 'toggle') {
        if ($existente) {
            $db->delete('DELETE FROM comentario_likes WHERE id = ?', [$existente['id']]);
            $liked = false;
        } else {
            $db->insert('comentario_likes', [
                'comentario_id' => $comentario_id,
                'usuario_id' => $usuario_id,
                'ip' => $usuario_id ? null : $ip,
            ]);
            $liked = true;
        }
    } else {
        $liked = (bool)$existente;
    }

    $total = $db->fetch('SELECT COUNT(*) AS c FROM comentario_likes WHERE comentario_id = ?', [$comentario_id])['c'];

    json_response(['liked' => $liked, 'total' => (int)$total]);
} catch (Exception $e) {
    json_error('Erro ao curtir comentário', 500);
}

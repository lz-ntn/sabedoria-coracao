<?php
/**
 * API: Curtir / Descurtir Artigo
 * POST /api/like.php
 * Body: { artigo_id: int }
 * Auth: Não requer login (usa IP como fallback)
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$artigo_id = (int)($input['artigo_id'] ?? 0);
$acao = $input['acao'] ?? 'toggle';

if (!$artigo_id) {
    json_error('artigo_id obrigatório', 400);
}

$db = Database::getInstance();
$ip = $_SERVER['REMOTE_ADDR'];
$usuario_id = esta_logado() ? ($_SESSION['usuario_id'] ?? null) : null;

try {
    $existente = null;
    if ($usuario_id) {
        $existente = $db->fetch('SELECT id FROM artigo_likes WHERE artigo_id = ? AND usuario_id = ?', [$artigo_id, $usuario_id]);
    } else {
        $existente = $db->fetch('SELECT id FROM artigo_likes WHERE artigo_id = ? AND ip = ? AND usuario_id IS NULL', [$artigo_id, $ip]);
    }

    if ($acao === 'toggle') {
        if ($existente) {
            $db->delete('DELETE FROM artigo_likes WHERE id = ?', [$existente['id']]);
            $liked = false;
        } else {
            $db->insert('artigo_likes', [
                'artigo_id' => $artigo_id,
                'usuario_id' => $usuario_id,
                'ip' => $usuario_id ? null : $ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
            $liked = true;
        }
    } elseif ($acao === 'like' && !$existente) {
        $db->insert('artigo_likes', [
            'artigo_id' => $artigo_id,
            'usuario_id' => $usuario_id,
            'ip' => $usuario_id ? null : $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
        $liked = true;
    } elseif ($acao === 'unlike' && $existente) {
        $db->delete('DELETE FROM artigo_likes WHERE id = ?', [$existente['id']]);
        $liked = false;
    } else {
        $liked = (bool)$existente;
    }

    $total = $db->fetch('SELECT COUNT(*) AS c FROM artigo_likes WHERE artigo_id = ?', [$artigo_id])['c'];

    json_response([
        'liked' => $liked,
        'total' => (int)$total,
    ]);
} catch (Exception $e) {
    json_error('Erro ao processar like', 500);
}

<?php
/**
 * API: Track Social Share
 * POST /api/compartilhar.php
 * Body: { artigo_id, plataforma }
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$artigo_id = (int)($input['artigo_id'] ?? 0);
$plataforma = trim($input['plataforma'] ?? '');

$permitidos = ['facebook', 'twitter', 'whatsapp', 'telegram', 'linkedin', 'email', 'copy'];

if (!$artigo_id || !in_array($plataforma, $permitidos)) {
    json_error('artigo_id e plataforma válidos obrigatórios', 400);
}

$db = Database::getInstance();

try {
    $db->insert('compartilhamentos', [
        'artigo_id' => $artigo_id,
        'plataforma' => $plataforma,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);

    $stats = $db->fetch('
        SELECT
            COUNT(*) AS total,
            COUNT(DISTINCT plataforma) AS plataformas
        FROM compartilhamentos
        WHERE artigo_id = ?
    ', [$artigo_id]);

    json_response([
        'success' => true,
        'total' => (int)$stats['total'],
        'plataformas' => (int)$stats['plataformas'],
    ]);
} catch (Exception $e) {
    json_error('Erro ao registrar compartilhamento', 500);
}

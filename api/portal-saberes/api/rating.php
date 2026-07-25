<?php
/**
 * API: Avaliar Artigo (1-5 estrelas)
 * POST /api/rating.php
 * Body: { artigo_id: int, rating: 1-5 }
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$artigo_id = (int)($input['artigo_id'] ?? 0);
$rating = (int)($input['rating'] ?? 0);

if (!$artigo_id || $rating < 1 || $rating > 5) {
    json_error('artigo_id e rating (1-5) obrigatórios', 400);
}

$db = Database::getInstance();
$ip = $_SERVER['REMOTE_ADDR'];
$usuario_id = esta_logado() ? ($_SESSION['usuario_id'] ?? null) : null;

try {
    $existente = null;
    if ($usuario_id) {
        $existente = $db->fetch('SELECT id FROM artigo_ratings WHERE artigo_id = ? AND usuario_id = ?', [$artigo_id, $usuario_id]);
    } else {
        $existente = $db->fetch('SELECT id FROM artigo_ratings WHERE artigo_id = ? AND ip = ? AND usuario_id IS NULL', [$artigo_id, $ip]);
    }

    if ($existente) {
        $db->update('artigo_ratings', ['rating' => $rating, 'atualizado_em' => date('Y-m-d H:i:s')], 'id = ?', [$existente['id']]);
    } else {
        $db->insert('artigo_ratings', [
            'artigo_id' => $artigo_id,
            'usuario_id' => $usuario_id,
            'ip' => $usuario_id ? null : $ip,
            'rating' => $rating,
        ]);
    }

    $stats = $db->fetch('
        SELECT
            COUNT(*) AS total,
            AVG(rating) AS media
        FROM artigo_ratings
        WHERE artigo_id = ?',
        [$artigo_id]
    );

    $distribuicao = $db->select('
        SELECT rating, COUNT(*) AS total
        FROM artigo_ratings
        WHERE artigo_id = ?
        GROUP BY rating
        ORDER BY rating DESC',
        [$artigo_id]
    );

    json_response([
        'rating' => $rating,
        'total' => (int)$stats['total'],
        'media' => round((float)$stats['media'], 2),
        'distribuicao' => $distribuicao,
    ]);
} catch (Exception $e) {
    json_error('Erro ao processar rating', 500);
}

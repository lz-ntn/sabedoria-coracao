<?php
/**
 * API: Favoritar Artigo
 * POST /api/favorito.php
 * Body: { artigo_id }
 * Auth: Requer login
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');

if (!esta_logado()) {
    json_error('Faça login para favoritar', 401);
}

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
$usuario_id = $_SESSION['usuario_id'];

try {
    $existente = $db->fetch('SELECT id FROM favoritos WHERE artigo_id = ? AND usuario_id = ?', [$artigo_id, $usuario_id]);

    if ($acao === 'toggle') {
        if ($existente) {
            $db->delete('DELETE FROM favoritos WHERE id = ?', [$existente['id']]);
            $favorito = false;
        } else {
            $db->insert('favoritos', ['artigo_id' => $artigo_id, 'usuario_id' => $usuario_id]);
            $favorito = true;
        }
    } elseif ($acao === 'add' && !$existente) {
        $db->insert('favoritos', ['artigo_id' => $artigo_id, 'usuario_id' => $usuario_id]);
        $favorito = true;
    } elseif ($acao === 'remove' && $existente) {
        $db->delete('DELETE FROM favoritos WHERE id = ?', [$existente['id']]);
        $favorito = false;
    } else {
        $favorito = (bool)$existente;
    }

    $total = $db->fetch('SELECT COUNT(*) AS c FROM favoritos WHERE artigo_id = ?', [$artigo_id])['c'];

    json_response([
        'favorito' => $favorito,
        'total' => (int)$total,
    ]);
} catch (Exception $e) {
    json_error('Erro ao favoritar', 500);
}

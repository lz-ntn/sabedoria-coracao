<?php
/**
 * API de Favoritos
 * 
 * GET    /api/favoritos.php              - Listar favoritos
 * POST   /api/favoritos.php              - Adicionar favorito
 * DELETE /api/favoritos.php?licao_id=X   - Remover favorito
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../config/app.php';

$db = Database::getInstance();
$usuario_id = obter_usuario_id($db);
$method = $_SERVER['REQUEST_METHOD'];

// ══════════════════════════════════════════
// GET - Listar favoritos do usuário
// ══════════════════════════════════════════
if ($method === 'GET') {
    if ($usuario_id === null) {
        json_response(['total' => 0, 'favoritos' => []]);
    }
    $favoritos = $db->select(
        'SELECT l.id, l.titulo, l.slug, c.nome AS categoria, 
                f.adicionado_em
         FROM favoritos f
         JOIN licoes l ON l.id = f.licao_id
         JOIN categorias c ON c.id = l.categoria_id
         WHERE f.usuario_id = ?
         ORDER BY f.adicionado_em DESC',
        [$usuario_id]
    );

    json_response([
        'total' => count($favoritos),
        'favoritos' => $favoritos
    ]);
}

// ══════════════════════════════════════════
// POST - Adicionar favorito
// ══════════════════════════════════════════
if ($method === 'POST') {
    if ($usuario_id === null) {
        json_error('Consentimento necessário para salvar favoritos.', 403);
    }
    validar_csrf_api();
    $data = ler_corpo();

    $erro = validar_campos(['licao_id'], $data);
    if ($erro) {
        json_error($erro);
    }

    $licao_id = (int) $data['licao_id'];

    // Verificar se lição existe
    $licao = $db->fetch('SELECT id FROM licoes WHERE id = ?', [$licao_id]);
    if (!$licao) {
        json_error('Lição não encontrada.', 404);
    }

    // Verificar duplicata
    $existe = $db->fetch(
        'SELECT id FROM favoritos WHERE usuario_id = ? AND licao_id = ?',
        [$usuario_id, $licao_id]
    );

    if ($existe) {
        json_error('Favorito já existe.', 409);
    }

    $db->insert('favoritos', [
        'usuario_id'    => $usuario_id,
        'licao_id'      => $licao_id,
        'adicionado_em' => date('Y-m-d H:i:s')
    ]);

    json_response([
        'success' => true,
        'message' => 'Favorito adicionado!'
    ]);
}

// ══════════════════════════════════════════
// DELETE - Remover favorito
// ══════════════════════════════════════════
if ($method === 'DELETE') {
    if ($usuario_id === null) {
        json_error('Consentimento necessário.', 403);
    }
    validar_csrf_api();
    if (!isset($_GET['licao_id'])) {
        json_error('Informe licao_id.');
    }

    $licao_id = (int) $_GET['licao_id'];

    $db->delete(
        'DELETE FROM favoritos WHERE usuario_id = ? AND licao_id = ?',
        [$usuario_id, $licao_id]
    );

    json_response([
        'success' => true,
        'message' => 'Favorito removido.'
    ]);
}

if ($method === 'OPTIONS') {
    json_response([]);
}

json_error('Método não permitido.', 405);

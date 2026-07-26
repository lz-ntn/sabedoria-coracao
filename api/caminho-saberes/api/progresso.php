<?php
/**
 * API de Progresso do Usuário
 * 
 * GET    /api/progresso.php               - Buscar progresso do usuário logado
 * POST   /api/progresso.php               - Marcar lição como concluída
 * DELETE /api/progresso.php?licao_id=X    - Desmarcar lição
 * DELETE /api/progresso.php?reset=1       - Resetar todo progresso
 * 
 * Fluxo:
 *   1. Cliente envia requisição com UUID do usuário (cookie automático)
 *   2. Servidor valida, processa e retorna JSON
 *   3. Frontend atualiza a interface
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../config/app.php';

$db = Database::getInstance();
$usuario_id = obter_usuario_id($db);
$method = $_SERVER['REQUEST_METHOD'];

// ══════════════════════════════════════════
// GET - Buscar progresso
// ══════════════════════════════════════════
if ($method === 'GET') {
    if ($usuario_id === null) {
        json_response(['categorias' => [], 'total_licoes' => 0, 'total_concluidas' => 0]);
    }
    $progresso = $db->select(
        'SELECT l.id, l.slug, l.titulo, l.categoria_id, p.concluida, p.concluida_em
         FROM licoes l
         LEFT JOIN progresso p ON p.licao_id = l.id AND p.usuario_id = ?
         ORDER BY l.categoria_id, l.ordem',
        [$usuario_id]
    );

    $categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');

    // Montar progresso por categoria
    $resultado = [
        'categorias' => [],
        'total_licoes' => count($progresso),
        'total_concluidas' => 0
    ];

    foreach ($categorias as $cat) {
        $licoes = array_filter($progresso, function($l) use ($cat) {
            return $l['categoria_id'] == $cat['id'];
        });

        $concluidas = array_filter($licoes, function($l) {
            return $l['concluida'] == 1;
        });

        $resultado['categorias'][] = [
            'id' => $cat['id'],
            'nome' => $cat['nome'],
            'slug' => $cat['slug'],
            'icone' => $cat['icone'],
            'cor' => $cat['cor'],
            'total' => count($licoes),
            'concluidas' => count($concluidas),
            'percentual' => count($licoes) > 0
                ? round((count($concluidas) / count($licoes)) * 100)
                : 0
        ];

        $resultado['total_concluidas'] += count($concluidas);
    }

    json_response($resultado);
}

// ══════════════════════════════════════════
// POST - Marcar lição como concluída
// ══════════════════════════════════════════
if ($method === 'POST') {
    if ($usuario_id === null) {
        json_error('Consentimento necessário para salvar progresso.', 403);
    }
    validar_csrf_api();
    $data = ler_corpo();

    $erro = validar_campos(['licao_id'], $data);
    if ($erro) {
        json_error($erro);
    }

    $licao_id = (int) $data['licao_id'];

    // Verifica se lição existe
    $licao = $db->fetch('SELECT id FROM licoes WHERE id = ?', [$licao_id]);
    if (!$licao) {
        json_error('Lição não encontrada.', 404);
    }

    // Verifica se já foi concluída (evita duplicatas)
    $existe = $db->fetch(
        'SELECT id FROM progresso WHERE usuario_id = ? AND licao_id = ?',
        [$usuario_id, $licao_id]
    );

    if (!$existe) {
        $db->insert('progresso', [
            'usuario_id'   => $usuario_id,
            'licao_id'     => $licao_id,
            'concluida'    => 1,
            'concluida_em' => date('Y-m-d H:i:s')
        ]);
    }

    json_response([
        'success'  => true,
        'message'  => 'Lição marcada como concluída.',
        'licao_id' => $licao_id
    ]);
}

// ══════════════════════════════════════════
// DELETE - Remover progresso
// ══════════════════════════════════════════
if ($method === 'DELETE') {
    if ($usuario_id === null) {
        json_error('Consentimento necessário.', 403);
    }
    validar_csrf_api();
    // Resetar tudo
    if (isset($_GET['reset'])) {
        $db->delete('DELETE FROM progresso WHERE usuario_id = ?', [$usuario_id]);
        json_response(['success' => true, 'message' => 'Progresso resetado.']);
    }

    // Remover lição específica
    if (isset($_GET['licao_id'])) {
        $licao_id = (int) $_GET['licao_id'];
        $db->delete(
            'DELETE FROM progresso WHERE usuario_id = ? AND licao_id = ?',
            [$usuario_id, $licao_id]
        );
        json_response(['success' => true, 'message' => 'Progresso removido.']);
    }

    json_error('Informe licao_id ou reset.');
}

// ══════════════════════════════════════════
// OPTIONS - CORS Preflight
// ══════════════════════════════════════════
if ($method === 'OPTIONS') {
    json_response([]);
}

json_error('Método não permitido.', 405);

<?php
/**
 * API: Listar Comentários
 * GET /api/comentario-listar.php?artigo_id=X
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');

$artigo_id = (int)($_GET['artigo_id'] ?? 0);
if (!$artigo_id) {
    json_error('artigo_id obrigatório', 400);
}

$db = Database::getInstance();

$comentarios = $db->select("
    SELECT
        c.id,
        c.usuario_id,
        c.autor_nome,
        c.conteudo,
        c.parent_id,
        c.criado_em,
        u.nivel as usuario_nivel,
        (SELECT COUNT(*) FROM comentario_likes WHERE comentario_id = c.id) AS likes
    FROM comentarios c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    WHERE c.artigo_id = ? AND c.status = 'aprovado'
    ORDER BY c.criado_em ASC
", [$artigo_id]);

$tree = [];
$byId = [];
foreach ($comentarios as $c) {
    $c['tempo_relativo'] = tempo_relativo($c['criado_em']);
    $c['children'] = [];
    $byId[$c['id']] = $c;
}
foreach ($byId as &$c) {
    if ($c['parent_id'] && isset($byId[$c['parent_id']])) {
        $byId[$c['parent_id']]['children'][] = &$c;
    } else {
        $tree[] = &$c;
    }
}
unset($c);

json_response([
    'comentarios' => $tree,
    'total' => count($comentarios),
]);

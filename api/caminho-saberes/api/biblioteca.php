<?php
/**
 * API de Biblioteca (Artigos) - tipo='artigo'
 * 
 * GET /api/biblioteca.php                    - Listar artigos publicados (com paginação, busca, filtro)
 * GET /api/biblioteca.php?id=X               - Buscar artigo por ID
 * GET /api/biblioteca.php?slug=X             - Buscar artigo por slug
 * GET /api/biblioteca.php?categoria=gnose    - Filtrar por categoria
 * GET /api/biblioteca.php?q=termo            - Busca fulltext
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    json_error('Método não permitido.', 405);
}

// Parâmetros
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$slug = $_GET['slug'] ?? null;
$categoriaSlug = $_GET['categoria'] ?? null;
$q = $_GET['q'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));
$offset = ($page - 1) * $perPage;

// ══════════════════════════════════════════
// Buscar artigo único (por ID ou slug)
// ══════════════════════════════════════════
if ($id || $slug) {
    $where = $id ? 'l.id = ?' : 'l.slug = ?';
    $param = $id ?? $slug;
    
    $artigo = $db->fetch(
        "SELECT l.*, c.nome as categoria_nome, c.slug as categoria_slug, c.cor as categoria_cor, c.icone as categoria_icone, u.nome as autor_nome
         FROM licoes l
         LEFT JOIN categorias c ON c.id = l.categoria_id
         LEFT JOIN usuarios u ON u.id = l.autor_id
         WHERE l.tipo = 'artigo' AND l.publicado_em IS NOT NULL AND $where",
        [$param]
    );

    if (!$artigo) {
        json_error('Artigo não encontrado.', 404);
    }

    // Incrementar views
    $db->update('licoes', ['views' => $artigo['views'] + 1], 'id = ?', [$artigo['id']]);
    $artigo['views']++;

    json_response($artigo);
}

// ══════════════════════════════════════════
// Listar artigos com filtros
// ══════════════════════════════════════════
$where = 'l.tipo = "artigo" AND l.publicado_em IS NOT NULL';
$params = [];

if ($categoriaSlug) {
    $where .= ' AND c.slug = ?';
    $params[] = $categoriaSlug;
}

if ($q) {
    // Busca fulltext
    $where .= ' AND MATCH(l.titulo, l.conteudo, l.tags) AGAINST(? IN NATURAL LANGUAGE MODE)';
    $params[] = $q;
}

// Total para paginação
$total = $db->fetch(
    "SELECT COUNT(*) as total FROM licoes l LEFT JOIN categorias c ON c.id = l.categoria_id WHERE $where",
    $params
)['total'];

// Buscar artigos
$artigos = $db->select(
    "SELECT l.id, l.titulo, l.slug, l.resumo, l.tags, l.imagem, l.views, l.publicado_em, 
            c.nome as categoria_nome, c.slug as categoria_slug, c.cor as categoria_cor, c.icone as categoria_icone,
            u.nome as autor_nome
     FROM licoes l
     LEFT JOIN categorias c ON c.id = l.categoria_id
     LEFT JOIN usuarios u ON u.id = l.autor_id
     WHERE $where
     ORDER BY l.publicado_em DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

json_response([
    'total' => $total,
    'page' => $page,
    'per_page' => $perPage,
    'total_pages' => (int)ceil($total / $perPage),
    'artigos' => $artigos
]);
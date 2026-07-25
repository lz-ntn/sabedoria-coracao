<?php
/**
 * API: Buscar Artigos
 * GET /api/busca.php?q=termo&categoria=ID&pagina=1
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$categoria = (int)($_GET['categoria'] ?? 0);
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$por_pagina = 10;

$db = Database::getInstance();

if (mb_strlen($q) < 2 && !$categoria) {
    json_error('Termo de busca muito curto', 400);
}

$termos = array_filter(explode(' ', $q));
$params = [];
$where = ["a.status = 'publicado'"];
$match = [];

foreach ($termos as $termo) {
    $t = '%' . $termo . '%';
    $match[] = '(a.titulo LIKE ? OR a.resumo LIKE ? OR a.conteudo LIKE ? OR a.tags LIKE ?)';
    $params[] = $t;
    $params[] = $t;
    $params[] = $t;
    $params[] = $t;
}

if ($match) {
    $where[] = '(' . implode(' AND ', $match) . ')';
}

if ($categoria) {
    $where[] = 'a.categoria_id = ?';
    $params[] = $categoria;
}

$whereSql = implode(' AND ', $where);

$offset = ($pagina - 1) * $por_pagina;

$total = $db->fetch("SELECT COUNT(*) AS c FROM artigos a WHERE $whereSql", $params)['c'];

$resultados = $db->select("
    SELECT
        a.id,
        a.titulo,
        a.slug,
        a.resumo,
        a.tags,
        a.imagem,
        a.views,
        a.publicado_em,
        c.nome AS categoria_nome,
        c.slug AS categoria_slug,
        c.cor AS categoria_cor,
        c.icone AS categoria_icone
    FROM artigos a
    LEFT JOIN categorias c ON c.id = a.categoria_id
    WHERE $whereSql
    ORDER BY a.publicado_em DESC
    LIMIT $por_pagina OFFSET $offset
", $params);

if ($q) {
    try {
        $db->insert('historico_buscas', [
            'query' => mb_substr($q, 0, 250),
            'resultados' => $total,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Exception $e) {}
}

$autocomplete = [];
if (count($termos) === 1 && mb_strlen($q) >= 2) {
    $autocomplete = $db->select("
        SELECT DISTINCT titulo, slug
        FROM artigos
        WHERE status = 'publicado' AND titulo LIKE ?
        LIMIT 8
    ", ['%' . $q . '%']);
}

json_response([
    'resultados' => $resultados,
    'total' => (int)$total,
    'pagina' => $pagina,
    'por_pagina' => $por_pagina,
    'total_paginas' => ceil($total / $por_pagina),
    'autocomplete' => $autocomplete,
    'query' => $q,
]);

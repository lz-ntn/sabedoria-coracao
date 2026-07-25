<?php
/**
 * API de Estatísticas
 * 
 * GET /api/stats.php - Estatísticas gerais do sistema
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../config/app.php';

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    json_error('Método não permitido.', 405);
}

// Total de lições
$total_licoes = $db->fetch('SELECT COUNT(*) as total FROM licoes');
$total_licoes = $total_licoes['total'];

// Total de categorias
$total_categorias = $db->fetch('SELECT COUNT(*) as total FROM categorias');
$total_categorias = $total_categorias['total'];

// Total de usuários
$total_usuarios = $db->fetch('SELECT COUNT(*) as total FROM usuarios');
$total_usuarios = $total_usuarios['total'];

// Total de progresso (lições concluídas)
$total_progresso = $db->fetch('SELECT COUNT(*) as total FROM progresso');
$total_progresso = $total_progresso['total'];

// Newsletter
$total_newsletter = $db->fetch('SELECT COUNT(*) as total FROM newsletter WHERE ativo = 1');
$total_newsletter = $total_newsletter['total'];

// Total de favoritos
$total_favoritos = $db->fetch('SELECT COUNT(*) as total FROM favoritos');
$total_favoritos = $total_favoritos['total'];

// Quiz realizados
$total_quiz = $db->fetch('SELECT COUNT(*) as total FROM quiz_resultados');
$total_quiz = $total_quiz['total'];

// Progresso por categoria
$progresso_categorias = $db->select(
    'SELECT c.nome, c.slug, 
            COUNT(DISTINCT p.id) as concluidas,
            (SELECT COUNT(*) FROM licoes WHERE categoria_id = c.id) as total
     FROM categorias c
     LEFT JOIN licoes l ON l.categoria_id = c.id
     LEFT JOIN progresso p ON p.licao_id = l.id
     GROUP BY c.id, c.nome, c.slug
     ORDER BY c.ordem'
);

// Últimos usuários
$ultimos_usuarios = $db->select(
    'SELECT uuid, criado_em, ultimo_acesso 
     FROM usuarios 
     ORDER BY ultimo_acesso DESC 
     LIMIT 10'
);

json_response([
    'total_licoes' => $total_licoes,
    'total_categorias' => $total_categorias,
    'total_usuarios' => $total_usuarios,
    'total_progresso' => $total_progresso,
    'total_newsletter' => $total_newsletter,
    'total_favoritos' => $total_favoritos,
    'total_quiz' => $total_quiz,
    'progresso_categorias' => $progresso_categorias,
    'ultimos_usuarios' => $ultimos_usuarios,
    'versao_app' => APP_VERSION,
    'ambiente' => APP_ENV
]);

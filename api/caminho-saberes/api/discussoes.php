<?php
/**
 * API de Discussões/Comentários
 * 
 * GET  /api/discussoes.php?licao_id=X       - Listar discussões de uma lição/artigo
 * POST /api/discussoes.php                   - Adicionar comentário
 * PUT  /api/discussoes.php?id=X              - Atualizar (apenas admin/autor)
 * DELETE /api/discussoes.php?id=X            - Excluir (apenas admin/autor)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$usuario_id = obter_usuario_id($db);
$method = $_SERVER['REQUEST_METHOD'];

// ══════════════════════════════════════════
// GET - Listar discussões
// ══════════════════════════════════════════
if ($method === 'GET') {
    $licao_id = isset($_GET['licao_id']) ? (int)$_GET['licao_id'] : null;
    if (!$licao_id) {
        json_error('Informe licao_id.');
    }

    // Verificar se lição/artigo existe
    $licao = $db->fetch('SELECT id, tipo FROM licoes WHERE id = ?', [$licao_id]);
    if (!$licao) {
        json_error('Conteúdo não encontrado.', 404);
    }

    $discussoes = $db->select(
        'SELECT d.*, 
                CASE 
                    WHEN d.usuario_id IS NOT NULL THEN u.nome
                    ELSE d.autor_nome
                END as autor_nome_exibicao
         FROM discussoes d
         LEFT JOIN usuarios u ON u.id = d.usuario_id
         WHERE d.licao_id = ? AND d.status = "aprovado" AND d.parent_id IS NULL
         ORDER BY d.criado_em DESC',
        [$licao_id]
    );

    // Buscar respostas para cada discussão
    foreach ($discussoes as &$d) {
        $respostas = $db->select(
            'SELECT r.*, 
                    CASE 
                        WHEN r.usuario_id IS NOT NULL THEN u.nome
                        ELSE r.autor_nome
                    END as autor_nome_exibicao
             FROM discussoes r
             LEFT JOIN usuarios u ON u.id = r.usuario_id
             WHERE r.parent_id = ? AND r.status = "aprovado"
             ORDER BY r.criado_em ASC',
            [$d['id']]
        );
        $d['respostas'] = $respostas;
    }
    unset($d);

    json_response([
        'total' => count($discussoes),
        'discussoes' => $discussoes
    ]);
}

// ══════════════════════════════════════════
// POST - Adicionar comentário
// ══════════════════════════════════════════
if ($method === 'POST') {
    // Comentários podem ser anônimos (sem usuario_id) se tiver consentimento LGPD
    // Mas preferimos ter usuario_id se disponível
    validar_csrf_api();
    $data = ler_corpo();

    $erro = validar_campos(['licao_id', 'conteudo'], $data);
    if ($erro) {
        json_error($erro);
    }

    $licao_id = (int)$data['licao_id'];
    $conteudo = trim($data['conteudo']);
    $parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : null;

    if (mb_strlen($conteudo) < 3) {
        json_error('Comentário muito curto.');
    }
    if (mb_strlen($conteudo) > 5000) {
        json_error('Comentário muito longo (máx 5000 caracteres).');
    }

    // Verificar se lição existe
    $licao = $db->fetch('SELECT id FROM licoes WHERE id = ?', [$licao_id]);
    if (!$licao) {
        json_error('Conteúdo não encontrado.', 404);
    }

    // Se for resposta, verificar se parent existe
    if ($parent_id) {
        $parent = $db->fetch('SELECT id FROM discussoes WHERE id = ? AND licao_id = ?', [$parent_id, $licao_id]);
        if (!$parent) {
            json_error('Comentário pai não encontrado.', 404);
        }
    }

    $dados = [
        'licao_id' => $licao_id,
        'conteudo' => $conteudo,
        'parent_id' => $parent_id,
        'status' => 'aprovado', // Auto-aprovar por enquanto; pode mudar para 'pendente' com moderação
        'criado_em' => date('Y-m-d H:i:s'),
    ];

    if ($usuario_id) {
        $dados['usuario_id'] = $usuario_id;
    } else {
        // Anônimo - pegar nome/email do body
        $dados['autor_nome'] = trim($data['autor_nome'] ?? 'Visitante');
        $dados['autor_email'] = trim($data['autor_email'] ?? '');
        if (!filter_var($dados['autor_email'], FILTER_VALIDATE_EMAIL)) {
            $dados['autor_email'] = null;
        }
    }

    $id = $db->insert('discussoes', $dados);

    json_response([
        'success' => true,
        'message' => 'Comentário adicionado!',
        'id' => $id
    ]);
}

// ══════════════════════════════════════════
// PUT - Atualizar (apenas autor ou admin)
// ══════════════════════════════════════════
if ($method === 'PUT') {
    if ($usuario_id === null) {
        json_error('Login necessário.', 403);
    }
    validar_csrf_api();
    $data = ler_corpo();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    if (!$id) {
        json_error('Informe id.');
    }

    $discussao = $db->fetch('SELECT * FROM discussoes WHERE id = ?', [$id]);
    if (!$discussao) {
        json_error('Comentário não encontrado.', 404);
    }

    // Verificar permissão (autor ou admin)
    $isAdmin = isset($_SESSION['admin_logado']);
    if (!$isAdmin && $discussao['usuario_id'] !== $usuario_id) {
        json_error('Sem permissão.', 403);
    }

    if (!empty($data['conteudo'])) {
        $conteudo = trim($data['conteudo']);
        if (mb_strlen($conteudo) < 3) {
            json_error('Comentário muito curto.');
        }
        $db->update('discussoes', ['conteudo' => $conteudo], 'id = ?', [$id]);
    }

    json_response(['success' => true, 'message' => 'Atualizado!']);
}

// ══════════════════════════════════════════
// DELETE - Excluir (apenas autor ou admin)
// ══════════════════════════════════════════
if ($method === 'DELETE') {
    if ($usuario_id === null && !isset($_SESSION['admin_logado'])) {
        json_error('Login necessário.', 403);
    }
    validar_csrf_api();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    if (!$id) {
        json_error('Informe id.');
    }

    $discussao = $db->fetch('SELECT * FROM discussoes WHERE id = ?', [$id]);
    if (!$discussao) {
        json_error('Comentário não encontrado.', 404);
    }

    $isAdmin = isset($_SESSION['admin_logado']);
    if (!$isAdmin && $discussao['usuario_id'] !== $usuario_id) {
        json_error('Sem permissão.', 403);
    }

    $db->delete('DELETE FROM discussoes WHERE id = ?', [$id]);

    json_response(['success' => true, 'message' => 'Excluído!']);
}

if ($method === 'OPTIONS') {
    json_response([]);
}

json_error('Método não permitido.', 405);
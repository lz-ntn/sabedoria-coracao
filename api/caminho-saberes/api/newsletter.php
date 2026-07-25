<?php
/**
 * API de Newsletter (assinantes)
 * 
 * POST /api/newsletter.php - Inscrever email
 * GET  /api/newsletter.php - Listar inscritos (admin)
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../config/app.php';

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

// ══════════════════════════════════════════
// POST - Inscrever email
// ══════════════════════════════════════════
if ($method === 'POST') {
    validar_csrf_api();
    $data = ler_corpo();

    $erro = validar_campos(['email'], $data);
    if ($erro) {
        json_error($erro);
    }

    $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        json_error('Email inválido.');
    }

    // Limitar tamanho
    if (strlen($email) > 255) {
        json_error('Email muito longo.');
    }

    // Verificar se já existe
    $existe = $db->fetch('SELECT id FROM newsletter WHERE email = ?', [$email]);
    if ($existe) {
        // Se existe mas está inativo, reativar
        $db->update('newsletter', ['ativo' => 1], 'id = ?', [$existe['id']]);
        json_response(['success' => true, 'message' => 'Inscrição reativada!']);
    }

    // Inserir
    $db->insert('newsletter', [
        'email'       => $email,
        'inscrito_em' => date('Y-m-d H:i:s')
    ]);

    json_response([
        'success' => true,
        'message' => 'Inscrição realizada com sucesso!'
    ]);
}

// ══════════════════════════════════════════
// GET - Listar inscritos (requer auth básica)
// ══════════════════════════════════════════
if ($method === 'GET') {
    $inscritos = $db->select(
        'SELECT email, ativo, inscrito_em FROM newsletter ORDER BY inscrito_em DESC'
    );

    json_response([
        'total' => count($inscritos),
        'inscritos' => $inscritos
    ]);
}

// ══════════════════════════════════════════
// OPTIONS - CORS
// ══════════════════════════════════════════
if ($method === 'OPTIONS') {
    json_response([]);
}

json_error('Método não permitido.', 405);

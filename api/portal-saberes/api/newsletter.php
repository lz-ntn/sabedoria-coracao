<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $email = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        http_response_code(400);
        echo json_encode(['error' => 'Email inválido.']);
        exit;
    }

    if (strlen($email) > 255) {
        http_response_code(400);
        echo json_encode(['error' => 'Email muito longo.']);
        exit;
    }

    $existe = $db->fetch('SELECT id FROM newsletter WHERE email = ?', [$email]);
    if ($existe) {
        $db->update('newsletter', ['ativo' => 1], 'id = ?', [$existe['id']]);
    } else {
        $db->insert('newsletter', [
            'email' => $email,
            'inscrito_em' => date('Y-m-d H:i:s'),
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Inscrição realizada com sucesso!']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não permitido.']);

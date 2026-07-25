<?php
/**
 * Logout
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

if (esta_logado()) {
    $db = Database::getInstance();
    log_atividade($db, $_SESSION['usuario_id'], 'logout', 'Usuário fez logout');
}

session_destroy();
header('Location: login.php');
exit;

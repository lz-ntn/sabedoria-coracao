<?php
/**
 * Redireciona para página dinâmica Sobre
 */
require_once __DIR__ . '/config/app.php';
header('Location: ' . APP_URL . '/pagina/sobre', true, 301);
exit;

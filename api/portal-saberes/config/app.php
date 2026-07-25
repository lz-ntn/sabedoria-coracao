<?php
/**
 * Configurações do Portal Saberes Ancestrais
 */

// Carrega autoloader do Composer
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Carrega variáveis de ambiente do .env
\Core\Config::load(__DIR__ . '/..');

define('APP_NAME', env('APP_NAME', 'Portal Saberes Ancestrais'));
define('APP_DESC', 'Wiki colaborativa sobre saberes ancestrais');
define('APP_URL', env('APP_URL', 'http://localhost/portal-saberes'));
define('APP_VERSION', '1.0.0');
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_ADMIN_EMAIL', env('APP_ADMIN_EMAIL', 'admin@saberes.com'));

date_default_timezone_set('America/Sao_Paulo');

// Configurações de paginação
define('ARTIGOS_POR_PAGINA', 12);
define('COMENTARIOS_POR_PAGINA', 20);

// Uploads
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Rate limiting
define('LOGIN_MAX_ATTEMPTS', (int)env('LOGIN_MAX_ATTEMPTS', 5));
define('LOGIN_BLOCK_MINUTES', (int)env('LOGIN_BLOCK_MINUTES', 15));

// Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_name('PORTAL_SABERES_SID');
    session_start();
}

// Gera token CSRF se ainda não existir
if (empty($_SESSION['_csrf_token'])) {
    \Core\Csrf::generate();
}

// Error handling
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

function app_path($relative = '') {
    return __DIR__ . '/../' . ltrim($relative, '/');
}

function app_url($relative = '') {
    return APP_URL . '/' . ltrim($relative, '/');
}

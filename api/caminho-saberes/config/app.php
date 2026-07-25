<?php
/**
 * Configurações gerais da aplicação
 */

// Carrega autoloader do Composer
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Carrega variáveis de ambiente do .env
\Core\Config::load(__DIR__ . '/..');

// 👷 Título do site
define('APP_NAME', env('APP_NAME', 'O Caminho — Saberes Ancestrais'));

// 👷 Descrição (meta tags)
define('APP_DESC', 'Uma jornada através dos saberes que unem ciência, espiritualidade e sabedoria prática.');

// 👷 URL base
define('APP_URL', env('APP_URL', 'http://localhost/caminho-saberes'));

// 👷 Versão do sistema
define('APP_VERSION', '2.0.0');

// 👷 Email do administrador
define('APP_EMAIL', env('APP_ADMIN_EMAIL', 'contato@saberesancestrais.com'));

// Ambiente
define('APP_ENV', env('APP_ENV', 'production'));

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// =============================================
// Configurações de Segurança
// =============================================

define('APP_HASH_ALGO', PASSWORD_BCRYPT);
define('APP_RATE_LIMIT', (int)env('RATE_LIMIT', 100));
define('LOGIN_MAX_ATTEMPTS', (int)env('LOGIN_MAX_ATTEMPTS', 5));
define('LOGIN_BLOCK_MINUTES', (int)env('LOGIN_BLOCK_MINUTES', 15));

// =============================================
// Configurações de Sessão
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_name('CAMINHO_SABERES_SESSID');
    session_start();
}

// Gera token CSRF se ainda não existir
if (empty($_SESSION['_csrf_token'])) {
    \Core\Csrf::generate();
}

// =============================================
// Utilitários
// =============================================

function app_path($relativePath = '') {
    return __DIR__ . '/../' . ltrim($relativePath, '/');
}

function app_url($relativePath = '') {
    return APP_URL . '/' . ltrim($relativePath, '/');
}

/**
 * Modo debug
 */
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

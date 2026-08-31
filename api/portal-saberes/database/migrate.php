<?php

require_once __DIR__ . '/../config/app.php';

use Core\Migration;

$host    = env('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
$port    = env('DB_PORT', getenv('MYSQLPORT') ?: '3306');
$name    = env('DB_NAME', getenv('MYSQLDATABASE') ?: 'portal_saberes');
$user    = env('DB_USER', getenv('MYSQLUSER') ?: 'root');
$pass    = env('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];

$development = env('APP_ENV', 'production') === 'development';
$ssl = db_ssl_options($development);
if ($ssl !== []) {
    $options += $ssl;
}

try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, $options);
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$name}`");

    $migration = new Migration($pdo, __DIR__);
    $migration->run();

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}

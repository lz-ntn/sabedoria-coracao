<?php
/**
 * Script de migração do banco de dados
 * Executado automaticamente no deploy do Railway
 *
 * Uso: php database/migrate.php
 */

require_once __DIR__ . '/../config/app.php';

// Suporta DB_* (app) e MYSQL* (Railway nativo)
$host    = env('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
$port    = env('DB_PORT', getenv('MYSQLPORT') ?: '3306');
$name    = env('DB_NAME', getenv('MYSQLDATABASE') ?: 'portal_saberes');
$user    = env('DB_USER', getenv('MYSQLUSER') ?: 'root');
$pass    = env('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
try {
    // Conecta sem selecionar banco para criar se necessário
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec("SET NAMES utf8mb4");

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$name}`");

    // Executa schema SQL
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $statements = explode(';', $schema);
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt) && !str_starts_with($stmt, '--') && !str_starts_with($stmt, '#')) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // Ignora erros como "já existe"
                if (str_contains($e->getMessage(), 'already exists')) {
                    continue;
                }
                fwrite(STDERR, "Migration warning: " . $e->getMessage() . "\n");
            }
        }
    }

    // Executa migration complementar se existir
    $migrationFile = __DIR__ . '/migration-v1.1.sql';
    if (file_exists($migrationFile)) {
        $migration = file_get_contents($migrationFile);
        $statements = explode(';', $migration);
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt) && !str_starts_with($stmt, '--') && !str_starts_with($stmt, '#')) {
                try {
                    $pdo->exec($stmt);
                } catch (PDOException $e) {
                    if (str_contains($e->getMessage(), 'already exists')) {
                        continue;
                    }
                    fwrite(STDERR, "Migration warning: " . $e->getMessage() . "\n");
                }
            }
        }
    }

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}

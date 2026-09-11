<?php

require_once __DIR__ . '/../config/app.php';

$host    = env('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
$port    = env('DB_PORT', getenv('MYSQLPORT') ?: '3306');
$name    = env('DB_NAME', getenv('MYSQLDATABASE') ?: 'portal_saberes');
$user    = env('DB_USER', getenv('MYSQLUSER') ?: 'root');
$pass    = env('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

try {
    $mysqlCmd = "mysql -h{$host} -P{$port} -u{$user}";
    if ($pass !== '') {
        $mysqlCmd .= " -p{$pass}";
    }
    $mysqlCmd .= " --default-character-set=utf8mb4";

    $createDb = "CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    exec("{$mysqlCmd} -e \"{$createDb}\"", $out, $code);
    if ($code !== 0) {
        throw new Exception("Failed to create database: " . implode("\n", $out));
    }

    $files = glob(__DIR__ . '/*.sql');
    sort($files);

    foreach ($files as $file) {
        $filename = basename($file);
        echo "  Migrating: {$filename}\n";
        
        $cmd = "{$mysqlCmd} {$name} < " . escapeshellarg($file);
        exec($cmd, $out, $code);
        if ($code !== 0) {
            throw new Exception("Migration failed [{$filename}]: " . implode("\n", $out));
        }
        
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations");
        $batch = (int)$stmt->fetchColumn();
        
        $pdo->exec("INSERT INTO migrations (migration, batch) VALUES (" . $pdo->quote($filename) . ", {$batch})");
        echo "  Migrated: {$filename}\n";
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}
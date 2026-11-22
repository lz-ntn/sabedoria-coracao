<?php
/**
 * Migrations do Caminho Saberes.
 *
 * Regras (corrigidas em 2026-11-22):
 *  - Idempotente de verdade: verifica a tabela `migrations` ANTES de executar o SQL.
 *  - Um batch por execução (todas as migrations novas entram no mesmo batch).
 *  - SSL: desligado só em dev (APP_ENV=development); em produção usa o CA bundle
 *    do sistema (exigência do TiDB Serverless), o mesmo de db_ssl_options().
 *    O cliente instalado é o mariadb-client: use --ssl-ca/--ssl-verify-server-cert
 *    (o --ssl-mode do MySQL 8 não existe no cliente MariaDB).
 */

require_once __DIR__ . '/../config/app.php';

$host    = env('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
$port    = env('DB_PORT', getenv('MYSQLPORT') ?: '3306');
$name    = env('DB_NAME', getenv('MYSQLDATABASE') ?: 'caminho_saberes');
$user    = env('DB_USER', getenv('MYSQLUSER') ?: 'root');
$pass    = env('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
$dev     = env('APP_ENV', 'production') === 'development';

// CA: mesma estratégia do docker-entrypoint.sh (que ja conecta com SSL no TiDB)
$caPath = '';
foreach ([
    (string)getenv('DB_SSL_CA'),
    __DIR__ . '/../../core/ssl/ca-certificates.crt',
    '/app/core/ssl/ca-certificates.crt',
    '/etc/ssl/certs/ca-certificates.crt',
    '/etc/pki/tls/certs/ca-bundle.crt',
    '/etc/ssl/ca-bundle.pem',
] as $candidato) {
    if ($candidato !== '' && file_exists($candidato) && is_readable($candidato)) {
        $caPath = $candidato;
        break;
    }
}

// Opções PDO — obrigatório no TiDB (insecure transport e bloqueado)
$pdoOpts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if (!$dev && $caPath !== '') {
    $pdoOpts[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
}

// Flags do cliente mysql (mariadb-client nao tem --ssl-mode do MySQL 8)
$sslCli = '';
if ($dev) {
    $sslCli = ' --ssl=0';
} else {
    $sslCli = $caPath !== ''
        ? ' --ssl-ca=' . escapeshellarg($caPath) . ' --ssl-verify-server-cert'
        : ' --ssl';
}

function tabelaExiste(PDO $pdo, string $tabela): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $stmt->execute([$tabela]);
    return (bool)$stmt->fetchColumn();
}

function colunaExiste(PDO $pdo, string $tabela, string $coluna): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $stmt->execute([$tabela, $coluna]);
    return (bool)$stmt->fetchColumn();
}

echo "Migração: host=$host, port=$port, db=$name, user=$user, ssl=" . ($dev ? 'off (dev)' : 'on') . ", ca=" . ($caPath ?: 'NAO ENCONTRADO') . "\n";

try {
    // Cliente mysql: lida com comentários e multi-statement dos arquivos SQL
    $mysqlCmd = "mysql -h{$host} -P{$port} -u{$user}{$sslCli}";
    if ($pass !== '') {
        $mysqlCmd .= ' -p' . escapeshellarg($pass);
    }
    $mysqlCmd .= ' --default-character-set=utf8mb4';

    // 1. Garante o banco (CREATE DATABASE é idempotente)
    $createDb = "CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    $out = [];
    exec("{$mysqlCmd} -e " . escapeshellarg($createDb) . " 2>&1", $out, $code);
    if ($code !== 0) {
        throw new Exception('Falha ao criar banco: ' . implode("\n", $out));
    }

    // 2. Tabela de controle ANTES do loop (PDO com as mesmas opções SSL)
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        $pdoOpts
    );
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Autocura: se o banco foi recriado e sobrou so a tabela migrations,
    //    os registros estao mentindo — limpa para reaplicar 001/002/003.
    $tabelas = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $reais = array_values(array_filter($tabelas, fn($t) => $t !== 'migrations'));
    if (count($reais) === 0) {
        $pdo->exec('DELETE FROM migrations');
        echo "  Banco sem tabelas: limpando registros de migrations (reaplicando schema)\n";
    }

    // Um batch para todas as migrations aplicadas nesta execução
    $batch = (int)$pdo->query('SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations')->fetchColumn();

    $files = glob(__DIR__ . '/*.sql');
    sort($files);

    foreach ($files as $file) {
        $filename = basename($file);

        // 4. Só confia no registro se o schema esperado realmente existir.
        //    (Evita o caso classico: migrations diz "aplicada" mas o banco foi recriado)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
        $stmt->execute([$filename]);
        $registrada = (int)$stmt->fetchColumn() > 0;

        $schemaFaltando = false;
        if ($filename === '001_core_schema.sql') {
            $schemaFaltando = !tabelaExiste($pdo, 'licoes');
        } elseif ($filename === '003_portal_merge.sql') {
            $schemaFaltando = tabelaExiste($pdo, 'licoes') && !colunaExiste($pdo, 'licoes', 'tipo');
        }

        if ($registrada && !$schemaFaltando) {
            echo "  Já aplicada: {$filename}\n";
            continue;
        }
        if ($schemaFaltando) {
            echo "  Schema ausente detectado: reaplicando {$filename}\n";
        }

        $out = []; // 5. reset: $out acumula entre chamadas de exec()
        $cmd = "{$mysqlCmd} {$name} < " . escapeshellarg($file) . ' 2>&1';
        exec($cmd, $out, $code);
        if ($code !== 0) {
            throw new Exception("Falha na migration [{$filename}]: " . implode("\n", $out));
        }

        $stmt = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?) ON DUPLICATE KEY UPDATE batch = VALUES(batch)');
        $stmt->execute([$filename, $batch]);
        echo "  Migrada: {$filename} (batch {$batch})\n";
    }

    echo "Migrations concluídas com sucesso!\n";

} catch (Throwable $e) {
    fwrite(STDERR, 'Migration falhou: ' . $e->getMessage() . "\n");
    exit(1);
}

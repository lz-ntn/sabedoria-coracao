<?php
namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;
    private string $charset;

    private function __construct()
    {
        $host    = Config::get('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
        $port    = Config::get('DB_PORT', getenv('MYSQLPORT') ?: '3306');
        $name    = Config::get('DB_NAME', getenv('MYSQLDATABASE') ?: 'database');
        $user    = Config::get('DB_USER', getenv('MYSQLUSER') ?: 'root');
        $pass    = Config::get('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
        $this->charset = Config::get('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host, $port, $name, $this->charset
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $caCandidates = [
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/ca-bundle.pem',
        ];
        foreach ($caCandidates as $caPath) {
            if (file_exists($caPath)) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
                break;
            }
        }

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            if (Config::isDevelopment()) {
                throw $e;
            }
            throw new PDOException('Erro ao conectar ao banco de dados.');
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function insert(string $table, array $data): string|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $params = []): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge(array_values($data), $params));
        return $stmt->rowCount();
    }

    public function delete(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function count(string $table, string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    public function paginate(string $sql, array $params = [], int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        return $this->select($sql, $params);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function contar(string $table, string $where = '1=1', array $params = []): int
    {
        return $this->count($table, $where, $params);
    }

    public function paginar(string $sql, array $params = [], int $page = 1, int $perPage = 12): array
    {
        return $this->paginate($sql, $params, $page, $perPage);
    }
}

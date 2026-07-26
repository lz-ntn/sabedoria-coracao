<?php
/**
 * Conexão com MySQL usando PDO
 */

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host    = \Core\Config::get('DB_HOST', '127.0.0.1');
        $port    = \Core\Config::get('DB_PORT', '3306');
        $name    = \Core\Config::get('DB_NAME', 'caminho_saberes');
        $user    = \Core\Config::get('DB_USER', 'root');
        $pass    = \Core\Config::get('DB_PASS', '');
        $charset = \Core\Config::get('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host, $port, $name, $charset
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $caPath = '/etc/ssl/certs/tidb-ca.pem';
        if (file_exists($caPath)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die('❌ Erro de conexão: ' . $e->getMessage());
            }
            die('❌ Erro ao conectar ao banco de dados.');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function select($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $params = []) {
        $set = implode(', ', array_map(function($col) {
            return "{$col} = ?";
        }, array_keys($data)));

        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge(array_values($data), $params));

        return $stmt->rowCount();
    }

    public function delete($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function getPdo() {
        return $this->pdo;
    }
}

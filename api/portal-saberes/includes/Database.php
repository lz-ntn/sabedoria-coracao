<?php
/**
 * Conexão PDO com MySQL (Singleton aprimorado)
 */

class Database {
    private static $instancia = null;
    private $pdo;

    private function __construct() {
        $host    = \Core\Config::get('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
        $port    = \Core\Config::get('DB_PORT', getenv('MYSQLPORT') ?: '3306');
        $name    = \Core\Config::get('DB_NAME', getenv('MYSQLDATABASE') ?: 'portal_saberes');
        $user    = \Core\Config::get('DB_USER', getenv('MYSQLUSER') ?: 'root');
        $pass    = \Core\Config::get('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s',
            $host, $port, $name);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $caPath = '/etc/ssl/certs/tidb-ca.pem';
        if (file_exists($caPath)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            $this->pdo->exec("SET NAMES utf8mb4");
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die('Erro DB: ' . $e->getMessage());
            }
            die('Erro ao conectar ao banco.');
        }
    }

    public static function getInstance() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
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
        $cols = implode(', ', array_keys($data));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$vals})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $params = []) {
        $set = implode(', ', array_map(fn($c) => "{$c} = ?", array_keys($data)));
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

    public function contar($tabela, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM {$tabela} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    public function paginar($sql, $params = [], $pagina = 1, $porPagina = 12) {
        $pagina = max(1, (int)$pagina);
        $offset = ($pagina - 1) * $porPagina;
        $sql .= " LIMIT {$porPagina} OFFSET {$offset}";
        return $this->select($sql, $params);
    }

    public function getPdo() {
        return $this->pdo;
    }
}

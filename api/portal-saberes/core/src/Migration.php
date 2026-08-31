<?php
namespace Core;

use PDO;
use PDOException;

class Migration
{
    private PDO $pdo;
    private string $table = 'migrations';
    private string $path;

    public function __construct(PDO $pdo, string $path)
    {
        $this->pdo = $pdo;
        $this->path = rtrim($path, '/');
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function run(): void
    {
        $executed = $this->getExecuted();
        $files = glob($this->path . '/*.sql');
        sort($files);

        $batch = $this->getNextBatch();
        $count = 0;

        foreach ($files as $file) {
            $filename = basename($file);

            if (in_array($filename, $executed, true)) {
                continue;
            }

            $sql = file_get_contents($file);
            $statements = explode(';', $sql);

            try {
                $this->pdo->beginTransaction();

                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (!empty($stmt) && !str_starts_with($stmt, '--') && !str_starts_with($stmt, '#')) {
                        $this->pdo->exec($stmt);
                    }
                }

                $this->pdo->exec(
                    "INSERT INTO {$this->table} (migration, batch) VALUES (" . $this->pdo->quote($filename) . ", {$batch})"
                );

                $this->pdo->commit();
                echo "  Migrated: {$filename}\n";
                $count++;

            } catch (PDOException $e) {
                $this->pdo->rollBack();
                throw new PDOException("Migration failed [{$filename}]: " . $e->getMessage());
            }
        }

        if ($count === 0) {
            echo "  Nothing to migrate.\n";
        }
    }

    private function getExecuted(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT migration FROM {$this->table} ORDER BY id");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException) {
            return [];
        }
    }

    private function getNextBatch(): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM {$this->table}");
            return (int) $stmt->fetchColumn();
        } catch (PDOException) {
            return 1;
        }
    }

    public function reset(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS {$this->table}");
        $this->ensureTable();
        echo "  Migration table reset.\n";
    }
}

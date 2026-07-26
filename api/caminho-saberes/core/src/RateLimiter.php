<?php
namespace Core;

class RateLimiter
{
    private \PDO $pdo;
    private string $table;
    private int $maxAttempts;
    private int $blockMinutes;

    public function __construct(
        \PDO $pdo,
        string $table = 'login_attempts',
        int $maxAttempts = 5,
        int $blockMinutes = 15
    ) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->maxAttempts = $maxAttempts;
        $this->blockMinutes = $blockMinutes;
    }

    public function isBlocked(string $identifier): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as attempts,
                    MAX(attempted_at) as last_attempt
             FROM {$this->table}
             WHERE identifier = ?
               AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
        );
        $stmt->execute([$identifier, $this->blockMinutes]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result['attempts'] >= $this->maxAttempts) {
            $minutesLeft = $this->blockMinutes - $this->minutesSince($result['last_attempt']);
            $_SESSION['_rate_limit_message'] = sprintf(
                'Muitas tentativas. Tente novamente em %d minuto(s).',
                ceil($minutesLeft)
            );
            return true;
        }
        return false;
    }

    public function recordAttempt(string $identifier): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (identifier, ip, attempted_at)
             VALUES (?, ?, NOW())"
        );
        $stmt->execute([
            $identifier,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    public function clearAttempts(string $identifier): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE identifier = ?"
        );
        $stmt->execute([$identifier]);
    }

    public function getRemainingAttempts(string $identifier): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as attempts
             FROM {$this->table}
             WHERE identifier = ?
               AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
        );
        $stmt->execute([$identifier, $this->blockMinutes]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return max(0, $this->maxAttempts - (int)$result['attempts']);
    }

    private function minutesSince(string $dateTime): float
    {
        $now = new \DateTime();
        $then = new \DateTime($dateTime);
        return ($now->getTimestamp() - $then->getTimestamp()) / 60;
    }
}

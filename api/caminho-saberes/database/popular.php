<?php
/**
 * Popula o Caminho Saberes com todo o conteúdo disponível.
 * Roda cada importador em processo separado (um exit(1) não mata os demais).
 * Todos são idempotentes (checam slug antes de inserir).
 *
 * Uso: php database/popular.php
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$scripts = [
    'seed_biblioteca.php',
    'import_saberes_wiki.php',
    'import_jesus_artigos.php',
    'import_blavatsky.php',
];

echo "=== Popular Caminho Saberes ===\n";
foreach ($scripts as $arquivo) {
    $path = __DIR__ . '/' . $arquivo;
    echo "\n----- {$arquivo} -----\n";
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path) . ' 2>&1', $codigo);
    echo "[exit={$codigo}]\n";
}

$db = Database::getInstance();
$total = $db->fetch("SELECT
    SUM(tipo = 'licao') AS licoes,
    SUM(tipo = 'artigo') AS artigos,
    (SELECT COUNT(*) FROM categorias) AS categorias
    FROM licoes");
echo "\n=== Resultado final ===\n";
echo "Lições: {$total['licoes']} | Artigos: {$total['artigos']} | Categorias: {$total['categorias']}\n";

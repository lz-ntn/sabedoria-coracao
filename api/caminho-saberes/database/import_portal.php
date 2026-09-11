<?php
/**
 * Script de importação: Portal Saberes → Caminho Saberes
 * Migra artigos publicados do Portal para o Caminho como tipo='artigo'
 * 
 * Uso: php database/import_portal.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

use Core\Database;

echo "=== Iniciando importação Portal → Caminho ===\n\n";

$dbCaminho = Database::getInstance();

// Conectar no banco do Portal (mesmo MySQL, database diferente)
$host    = env('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
$port    = env('DB_PORT', getenv('MYSQLPORT') ?: '3306');
$user    = env('DB_USER', getenv('MYSQLUSER') ?: 'root');
$pass    = env('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

try {
    $pdoPortal = new PDO("mysql:host={$host};port={$port};dbname=portal_saberes;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Conectado ao portal_saberes\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Erro ao conectar no portal_saberes: " . $e->getMessage() . "\n");
    exit(1);
}

// Buscar categorias do Caminho para mapear por slug/nome
$categoriasMap = [];
$catsCaminho = $dbCaminho->select('SELECT id, nome, slug FROM categorias');
foreach ($catsCaminho as $c) {
    $categoriasMap[strtolower($c['slug'])] = $c['id'];
    $categoriasMap[strtolower($c['nome'])] = $c['id'];
}

// Buscar usuário admin do Caminho (para ser autor dos artigos importados)
$admin = $dbCaminho->fetch("SELECT id FROM usuarios WHERE email LIKE '%admin%' OR nome LIKE '%admin%' LIMIT 1");
$autorId = $admin ? $admin['id'] : 1;
echo "✓ Autor padrão para artigos: usuario_id = $autorId\n\n";

// Buscar artigos PUBLICADOS do Portal
$artigos = $pdoPortal->query("
    SELECT a.*, c.nome as cat_nome, c.slug as cat_slug, u.nome as autor_nome
    FROM artigos a
    LEFT JOIN categorias c ON c.id = a.categoria_id
    LEFT JOIN usuarios u ON u.id = a.autor_id
    WHERE a.status = 'publicado'
    ORDER BY a.publicado_em DESC
")->fetchAll();

echo "Encontrados " . count($artigos) . " artigos publicados no Portal.\n\n";

$importados = 0;
$pulados = 0;
$erros = 0;

foreach ($artigos as $artigo) {
    // Determinar categoria_id no Caminho
    $categoriaId = null;
    if (!empty($artigo['cat_slug']) && isset($categoriasMap[strtolower($artigo['cat_slug'])])) {
        $categoriaId = $categoriasMap[strtolower($artigo['cat_slug'])];
    } elseif (!empty($artigo['cat_nome']) && isset($categoriasMap[strtolower($artigo['cat_nome'])])) {
        $categoriaId = $categoriasMap[strtolower($artigo['cat_nome'])];
    } else {
        // Fallback: primeira categoria
        $categoriaId = $catsCaminho[0]['id'] ?? 1;
    }

    // Verificar se já existe (por slug)
    $existente = $dbCaminho->fetch('SELECT id FROM licoes WHERE slug = ? AND tipo = "artigo"', [$artigo['slug']]);
    if ($existente) {
        echo "⊘ Pulado (já existe): {$artigo['titulo']}\n";
        $pulados++;
        continue;
    }

    // Preparar dados
    $dados = [
        'categoria_id' => $categoriaId,
        'titulo'       => $artigo['titulo'],
        'slug'         => $artigo['slug'],
        'resumo'       => $artigo['resumo'] ?: null,
        'conteudo'     => $artigo['conteudo'],
        'tags'         => $artigo['tags'] ?: null,
        'imagem'       => $artigo['imagem'] ?: null,
        'fonte'        => $artigo['fonte'] ?: 'Portal Saberes Ancestrais (importado)',
        'nivel'        => 'avancado', // artigos são nível avançado
        'duracao_min'  => max(15, (int)ceil((strlen(strip_tags($artigo['conteudo'] ?? '')) / 1000) * 5)), // estimativa
        'ordem'        => 999, // artigos ficam no final da categoria
        'tipo'         => 'artigo',
        'autor_id'     => $autorId,
        'publicado_em' => $artigo['publicado_em'] ?: date('Y-m-d H:i:s'),
        'criada_em'    => $artigo['criado_em'] ?: date('Y-m-d H:i:s'),
    ];

    try {
        $novoId = $dbCaminho->insert('licoes', $dados);
        echo "✓ Importado: {$artigo['titulo']} (ID: $novoId, Cat: $categoriaId)\n";
        $importados++;
    } catch (Exception $e) {
        echo "✗ Erro ao importar '{$artigo['titulo']}': " . $e->getMessage() . "\n";
        $erros++;
    }
}

echo "\n=== Resumo ===\n";
echo "Importados: $importados\n";
echo "Pulados (já existiam): $pulados\n";
echo "Erros: $erros\n";

if ($importados > 0) {
    echo "\n✓ Importação concluída com sucesso!\n";
    echo "Execute 'make migrate-caminho-saberes' para garantir que a migration 003 rodou.\n";
} else {
    echo "\n⚠ Nenhum artigo novo importado.\n";
}
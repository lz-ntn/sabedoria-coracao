<?php
/**
 * Importador: lições helena-blavatsky/estudo-web → Caminho Saberes
 *
 * Uso: php database/import_blavatsky.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();

$baseDir = '/app/data/blavatsky';
if (!file_exists($baseDir)) {
    $hostPath = getenv('BLAVATSKY_PATH') ?: '/home/lz-ntn/Modelos/Lz_ntn/helena-blavatsky/estudo-web';
    if (file_exists($hostPath)) $baseDir = $hostPath;
    else { fwrite(STDERR, "ERRO: diretório não encontrado\n"); exit(1); }
}

echo "=== Importador Helena Blavatsky → Caminho Saberes ===\n\n";

$admin = $db->fetch("SELECT id FROM usuarios ORDER BY id LIMIT 1");
$autorId = $admin ? (int)$admin['id'] : null;
$catId = $db->fetch("SELECT id FROM categorias WHERE slug = 'teosofia'")['id'] ?? 1;

$licoes = [
    ['slug' => 'blavatsky-introducao-html',    'titulo' => 'Helena Blavatsky: Introdução à Sabedoria Universal',       'arq' => 'licao-1'],
    ['slug' => 'blavatsky-conceitos-basicos',  'titulo' => 'Helena Blavatsky: Conceitos Fundamentais da Teosofia',     'arq' => 'licao-2'],
    ['slug' => 'blavatsky-elementos',          'titulo' => 'Helena Blavatsky: Os Elementos da Doutrina Secreta',       'arq' => 'licao-3'],
    ['slug' => 'blavatsky-pratica',            'titulo' => 'Helena Blavatsky: Prática e Aplicação Teosófica',         'arq' => 'licao-4'],
    ['slug' => 'blavatsky-avancado',           'titulo' => 'Helena Blavatsky: Estudo Avançado — A Chave da Teosofia','arq' => 'licao-5'],
];

$importados = 0;
foreach ($licoes as $l) {
    $existe = $db->fetch('SELECT id FROM licoes WHERE slug = ?', [$l['slug']]);
    if ($existe) { echo "  ⊘ {$l['titulo']}\n"; continue; }

    $file = "{$baseDir}/{$l['arq']}/index.html";
    if (!file_exists($file)) { echo "  ✗ {$l['arq']}/index.html não encontrado\n"; continue; }

    $html = file_get_contents($file);
    // Extrair conteúdo do <main>
    if (preg_match('/<main[^>]*>(.+?)<\/main>/si', $html, $m)) {
        $conteudo = $m[1];
    } else {
        $conteudo = $html;
    }
    // Limpar tags de layout
    $conteudo = preg_replace('/<header[^>]*>.*?<\/header>/si', '', $conteudo);
    $conteudo = preg_replace('/<footer[^>]*>.*?<\/footer>/si', '', $conteudo);
    $conteudo = preg_replace('/<nav[^>]*>.*?<\/nav>/si', '', $conteudo);
    $conteudo = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $conteudo);
    $conteudo = trim($conteudo);

    try {
        $db->insert('licoes', [
            'categoria_id' => $catId,
            'titulo'       => $l['titulo'],
            'slug'         => $l['slug'],
            'resumo'       => 'Série de lições de Helena Blavatsky sobre Teosofia e sabedoria universal',
            'conteudo'     => $conteudo,
            'tags'         => 'teosofia, blavatsky, estudo, lições',
            'nivel'        => 'intermediario',
            'duracao_min'  => 30,
            'ordem'        => 999,
            'tipo'         => 'artigo',
            'fonte'        => 'Helena Blavatsky — Estudo Web',
            'autor_id'     => $autorId,
            'publicado_em' => date('Y-m-d H:i:s'),
            'criada_em'    => date('Y-m-d H:i:s'),
        ]);
        echo "  ✓ {$l['titulo']}\n";
        $importados++;
    } catch (Exception $e) {
        echo "  ✗ {$l['titulo']}: " . $e->getMessage() . "\n";
    }
}

echo "\nImportados: $importados\n";

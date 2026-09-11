<?php
/**
 * Importador: dados-unificados.json (Saberes_Wiki) → Caminho Saberes
 * Importa saberes como licoes tipo='licao'. Categorias novas são criadas.
 *
 * Uso: php database/import_saberes_wiki.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();

$jsonPath = '/app/data/dados-unificados.json';

// Se não existir no container, tentar copiar do host
if (!file_exists($jsonPath)) {
    $hostPath = getenv('SABERES_JSON_PATH') ?: '/home/lz-ntn/Modelos/Lz_ntn/Saberes_Wiki/database/dados-unificados.json';
    if (file_exists($hostPath)) {
        $jsonPath = $hostPath;
    } else {
        fwrite(STDERR, "ERRO: dados-unificados.json não encontrado. Defina SABERES_JSON_PATH ou monte o arquivo em /app/data/\n");
        exit(1);
    }
}

echo "=== Importador Saberes Wiki → Caminho Saberes ===\n";
echo "JSON: $jsonPath\n\n";

$data = json_decode(file_get_contents($jsonPath), true);
if (!$data) {
    fwrite(STDERR, "ERRO: JSON inválido\n");
    exit(1);
}

$saberes = $data['saberes'] ?? [];
$categorias = $data['categorias'] ?? [];
echo "JSON: " . count($categorias) . " categorias, " . count($saberes) . " saberes\n\n";

// ── Categorias novas ──
$catNovas = [
    [2,  'Práticas',        'praticas',        'Técnicas de transformação interior',          'bi bi-activity',   '#3498db'],
    [4,  'Cosmologia',      'cosmologia',      'A ciência do todo — éter, iCosmica',          'bi bi-globe',      '#00bcd4'],
    [5,  'Jornada',         'jornada',         'Propósito, consciência e transformação',      'bi bi-compass',    '#e67e22'],
    [6,  'Vida Verdadeira', 'vida-verdadeira', 'Integração e presença — Tao, Pneuma, Eu Sou', 'bi bi-infinity',   '#e74c3c'],
    [7,  'Tradições',       'tradicoes',       'Sabedoria ancestral dos povos',               'bi bi-book',       '#f39c12'],
];

echo "── Categorias novas ──\n";
foreach ($catNovas as [$id, $nome, $slug, $desc, $icone, $cor]) {
    $db->getPdo()->exec(
        "INSERT IGNORE INTO categorias (nome, slug, descricao, icone, cor, ordem)
         VALUES (" . $db->getPdo()->quote($nome) . ", " . $db->getPdo()->quote($slug) . ",
                 " . $db->getPdo()->quote($desc) . ", " . $db->getPdo()->quote($icone) . ",
                 " . $db->getPdo()->quote($cor) . ", $id)"
    );
    echo "  ✓ $nome ($slug)\n";
}

// ── Mapear categorias ──
$cats = $db->select('SELECT id, slug FROM categorias ORDER BY id');
$catMap = [];
foreach ($cats as $c) {
    $catMap[$c['slug']] = (int)$c['id'];
}
echo "\n── Categorias: " . implode(', ', array_keys($catMap)) . " ──\n";

// ── Overrides de slug → cat ──
$slugCatMap = [
    'hermetismo-sete-principios' => 'hermetismo',
    'lei-correspondencia' => 'hermetismo',
    'hermetismo-caibalion-ser-todo' => 'hermetismo',
    'teosofia-o-que-e' => 'teosofia',
    'teosofia-helena-blavatsky' => 'teosofia',
    'kundalini-o-que-e' => 'kundalini',
    'kundalini-fogo-espirito-santo' => 'kundalini',
    'coracao-campo' => 'coracao',
    'coracao-coerencia' => 'coracao',
    'coracao-centro-inteligencia' => 'coracao',
    'coracao-arquiteto-realidade' => 'coracao',
    'epigenetica-o-que-e' => 'epigenetica',
    'epigenetica-mecanismos' => 'epigenetica',
    'epigenetica-influencias' => 'epigenetica',
    'epigenetica-pratica-guia' => 'epigenetica',
];

$catIdFallback = [1 => 'gnose', 3 => 'epigenetica'];

// ── Importar ──
echo "\n── Importando saberes ──\n";
$importados = 0;
$pulados = 0;
$erros = 0;

function flatten($conteudo) {
    if (is_string($conteudo)) return $conteudo;
    if (!is_array($conteudo)) return '';

    $partes = [];
    foreach ($conteudo as $key => $val) {
        if (is_string($val)) {
            $partes[] = $val;
        } elseif (is_array($val)) {
            foreach ($val as $item) {
                if (!is_array($item)) { $partes[] = (string)$item; continue; }
                if (isset($item['termo'], $item['def'])) {
                    $partes[] = "• {$item['termo']}: {$item['def']}";
                } elseif (isset($item['nome'], $item['desc'])) {
                    $emoji = $item['simbolo'] ?? '';
                    $partes[] = $emoji ? "$emoji {$item['nome']}\n{$item['desc']}" : "• {$item['nome']}\n{$item['desc']}";
                } elseif (isset($item['frase'], $item['desc'])) {
                    $num = $item['num'] ?? '';
                    $nome = $item['nome'] ?? '';
                    $partes[] = "$num. $nome — \"{$item['frase']}\"\n{$item['desc']}";
                } elseif (isset($item['titulo'], $item['instrucoes'])) {
                    $partes[] = "**{$item['titulo']}**\n{$item['instrucoes']}";
                } else {
                    $vals = array_filter($item, fn($v) => is_string($v));
                    if ($vals) $partes[] = '• ' . implode(' | ', $vals);
                }
            }
        } elseif (is_array($val)) {
            $partes[] = flatten($val);
        }
    }
    return implode("\n\n", array_filter($partes));
}

foreach ($saberes as $saber) {
    $slug = $saber['slug'];
    $titulo = $saber['titulo'];

    // Verificar duplicata
    $existe = $db->fetch('SELECT id FROM licoes WHERE slug = ?', [$slug]);
    if ($existe) {
        echo "  ⊘ $titulo\n";
        $pulados++;
        continue;
    }

    // Categoria
    if (isset($slugCatMap[$slug])) {
        $catSlug = $slugCatMap[$slug];
    } elseif (isset($catIdFallback[$saber['categoria_id'] ?? 0])) {
        $catSlug = $catIdFallback[$saber['categoria_id']];
    } else {
        // Procurar nas categorias novas
        $catSlug = 'gnose';
        foreach ($catNovas as $cn) {
            if ($cn[0] === ($saber['categoria_id'] ?? 0)) {
                $catSlug = $cn[2];
                break;
            }
        }
    }
    $catId = $catMap[$catSlug] ?? 1;

    // Conteúdo
    $conteudo = flatten($saber['conteudo'] ?? []);
    if (empty($conteudo)) $conteudo = $saber['descricao'] ?? '';

    // Práticas
    $praticas = $saber['praticas'] ?? [];
    if (!empty($praticas)) {
        $conteudo .= "\n\n--- Prática ---\n";
        foreach ($praticas as $p) {
            $conteudo .= "\n**{$p['titulo']}**\n{$p['instrucoes']}\n";
            if (!empty($p['duracao'])) $conteudo .= "Duração: {$p['duracao']} min\n";
        }
    }

    $tags = implode(', ', $saber['tags'] ?? []);
    $nivel = $saber['nivel'] ?? 'iniciante';
    $duracao = $saber['duracao'] ?? 15;
    $fonte = $saber['fonte'] ?? 'Saberes de Coração';

    try {
        $db->insert('licoes', [
            'categoria_id' => $catId,
            'titulo'       => $titulo,
            'slug'         => $slug,
            'resumo'       => $saber['descricao'] ?? null,
            'conteudo'     => $conteudo,
            'tags'         => $tags ?: null,
            'nivel'        => $nivel,
            'duracao_min'  => $duracao,
            'ordem'        => 999,
            'tipo'         => 'licao',
            'fonte'        => $fonte,
            'publicado_em' => date('Y-m-d H:i:s'),
            'criada_em'    => date('Y-m-d H:i:s'),
        ]);
        echo "  ✓ $titulo → $catSlug\n";
        $importados++;
    } catch (Exception $e) {
        echo "  ✗ $titulo: " . $e->getMessage() . "\n";
        $erros++;
    }
}

echo "\n=== Resumo ===\n";
echo "Importados: $importados\n";
echo "Pulados: $pulados\n";
echo "Erros: $erros\n";

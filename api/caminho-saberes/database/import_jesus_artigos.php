<?php
/**
 * Importador: artigos Markdown (Jesus/) → Caminho Saberes Biblioteca
 * Lê frontmatter YAML + conteúdo markdown, insere como tipo='artigo'.
 *
 * Uso: php database/import_jesus_artigos.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();

$postsDir = '/app/data/jesus-posts';

if (!file_exists($postsDir)) {
    $hostPath = getenv('JESUS_POSTS_PATH') ?: '/media/lz-ntn/5109c857-645d-40f2-a6c5-36c96cb83473/@home/lzntn/Modelos/Jesus/content/posts';
    if (file_exists($hostPath)) {
        $postsDir = $hostPath;
    } else {
        fwrite(STDERR, "ERRO: Diretório de posts não encontrado.\n");
        exit(1);
    }
}

echo "=== Importador Jesus Artigos → Caminho Biblioteca ===\n";
echo "Dir: $postsDir\n\n";

$admin = $db->fetch("SELECT id FROM usuarios ORDER BY id LIMIT 1");
$autorId = $admin ? (int)$admin['id'] : null;

// Buscar cat "tradições" (cristianismo entra lá) ou "gnose"
$cats = $db->select('SELECT id, slug FROM categorias ORDER BY id');
$catMap = [];
foreach ($cats as $c) {
    $catMap[$c['slug']] = (int)$c['id'];
}

// Mapeamento de tags → categoria
$tagCatMap = [
    'gnose' => 'gnose',
    'gnosticismo' => 'gnose',
    'hermetismo' => 'hermetismo',
    'kybalion' => 'hermetismo',
    'kundalini' => 'kundalini',
    'pneuma' => 'kundalini',
    'teosofia' => 'teosofia',
    'epigenetica' => 'epigenetica',
    'coracao' => 'coracao',
    'meditacao' => 'praticas',
    'cristianismo' => 'tradicoes',
    'didache' => 'tradicoes',
    'pistis-sophia' => 'gnose',
    'yeshua' => 'tradicoes',
    'jesus' => 'tradicoes',
    'filoxenia' => 'vida-verdadeira',
    'tao' => 'vida-verdadeira',
    'vida-sem-filtros' => 'jornada',
    'regra-de-ouro' => 'tradicoes',
    'pneuma-kundalini' => 'kundalini',
];

function parse_frontmatter($content, &$meta, &$body) {
    if (preg_match('/^---\s*\n(.+?)\n---\s*\n(.+)$/s', $content, $m)) {
        $yaml = $m[1];
        $body = $m[2];
        foreach (explode("\n", $yaml) as $line) {
            if (preg_match('/^(\w+):\s*"?(.+?)"?\s*$/', $line, $kv)) {
                $meta[$kv[1]] = trim($kv[2], '" ');
            }
        }
        return true;
    }
    $body = $content;
    return false;
}

function md_to_html($md) {
    // Headers
    $md = preg_replace('/^#### (.+)$/m', '<h5>$1</h5>', $md);
    $md = preg_replace('/^### (.+)$/m', '<h4>$1</h4>', $md);
    $md = preg_replace('/^## (.+)$/m', '<h3>$1</h3>', $md);
    $md = preg_replace('/^# (.+)$/m', '<h2>$1</h2>', $md);
    // Bold e italic
    $md = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $md);
    $md = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $md);
    // Blockquotes
    $md = preg_replace('/^>\s*"?(.+?)"?\s*$/m', '<blockquote>$1</blockquote>', $md);
    // Horizontal rules
    $md = preg_replace('/^---+$/m', '<hr>', $md);
    // Lists
    $md = preg_replace('/^- (.+)$/m', '<li>$1</li>', $md);
    $md = preg_replace('/(<li>.*<\/li>\n?)+/', '<ul>$0</ul>', $md);
    // Paragraphs (double newline)
    $md = preg_replace('/\n\n/', '</p><p>', $md);
    $md = '<p>' . $md . '</p>';
    // Clean up empty tags
    $md = str_replace('<p></p>', '', $md);
    $md = preg_replace('/<p>\s*<(h[2-5]|blockquote|ul|hr)/', '<$1', $md);
    $md = preg_replace('/<\/(h[2-5]|blockquote|ul|hr)>\s*<\/p>/', '</$1>', $md);
    return $md;
}

$files = glob("$postsDir/*.md");
echo "Encontrados " . count($files) . " artigos\n\n";

$importados = 0;
$pulados = 0;
$erros = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $meta = [];
    $body = '';
    parse_frontmatter($content, $meta, $body);

    $titulo = $meta['title'] ?? basename($file, '.md');
    $slug = $meta['slug'] ?? basename($file, '.md');
    $excerpt = $meta['excerpt'] ?? '';
    $tags = isset($meta['tags']) ? str_replace(['[', ']', '"'], '', $meta['tags']) : '';
    $date = $meta['date'] ?? date('Y-m-d');

    // Verificar duplicata
    $existe = $db->fetch('SELECT id FROM licoes WHERE slug = ?', [$slug]);
    if ($existe) {
        echo "  ⊘ $titulo\n";
        $pulados++;
        continue;
    }

    // Determinar categoria por tags
    $catSlug = 'tradicoes'; // default para artigos Jesus
    $tagList = array_map('trim', explode(',', $tags));
    foreach ($tagList as $tag) {
        $tag = strtolower($tag);
        if (isset($tagCatMap[$tag])) {
            $catSlug = $tagCatMap[$tag];
            break;
        }
    }
    $catId = $catMap[$catSlug] ?? $catMap['tradicoes'] ?? 1;

    // Converter markdown para HTML básico
    $conteudo = md_to_html(trim($body));

    try {
        $db->insert('licoes', [
            'categoria_id' => $catId,
            'titulo'       => $titulo,
            'slug'         => $slug,
            'resumo'       => $excerpt ?: null,
            'conteudo'     => $conteudo,
            'tags'         => $tags ?: null,
            'nivel'        => 'intermediario',
            'duracao_min'  => 20,
            'ordem'        => 999,
            'tipo'         => 'artigo',
            'fonte'        => 'Jesus — Sabedoria sem Filtros',
            'autor_id'     => $autorId,
            'publicado_em' => $date . ' 12:00:00',
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

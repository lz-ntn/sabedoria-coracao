<?php
/**
 * Busca no Portal - Versão Enriquecida
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$q = trim($_GET['q'] ?? '');
$categoria_filtro = (int)($_GET['categoria'] ?? 0);
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$por_pagina = 12;

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$resultados = [];
$total = 0;
$termos = [];

if (strlen($q) >= 2) {
    $termos = preg_split('/\s+/', $q);
    $termos = array_filter($termos, fn($t) => strlen($t) >= 2);

    if (!empty($termos)) {
        $query = implode(' ', array_map(fn($t) => '+' . $t . '*', $termos));
        $where = 'a.status = "publicado"';
        $where_match = 'MATCH(a.titulo, a.resumo, a.conteudo, a.tags) AGAINST(? IN BOOLEAN MODE)';

        // Query de total: placeholders na ordem — WHERE cat?, WHERE MATCH
        $cnt_params = [];
        if ($categoria_filtro) {
            $where .= ' AND a.categoria_id = ?';
            $cnt_params[] = $categoria_filtro;
        }
        $cnt_params[] = $query;

        $total = $db->fetch("SELECT COUNT(*) AS c FROM artigos a WHERE $where AND $where_match", $cnt_params)['c'];

        // Query de resultados: placeholders na ordem — MATCH SELECT, WHERE cat?, WHERE MATCH
        $offset = ($pagina - 1) * $por_pagina;
        $res_params = [$query];
        if ($categoria_filtro) {
            $res_params[] = $categoria_filtro;
        }
        $res_params[] = $query;

        $resultados = $db->select(
            "SELECT a.*, c.nome as cat_nome, c.slug as cat_slug, c.icone as cat_icone, c.cor as cat_cor,
                    $where_match as relevancia,
                    COALESCE(l.likes_count, 0) AS likes_count
             FROM artigos a
             LEFT JOIN categorias c ON c.id = a.categoria_id
             LEFT JOIN (
                SELECT artigo_id, COUNT(*) AS likes_count
                FROM artigo_likes
                GROUP BY artigo_id
             ) l ON l.artigo_id = a.id
             WHERE $where AND $where_match
             ORDER BY relevancia DESC, a.publicado_em DESC
             LIMIT $por_pagina OFFSET $offset",
            $res_params
        );

        try {
            $db->insert('historico_buscas', [
                'query' => mb_substr($q, 0, 250),
                'resultados' => $total,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Exception $e) {}
    }
}

$sugestoes_populares = $db->select('
    SELECT query, COUNT(*) AS c
    FROM historico_buscas
    WHERE criado_em > DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY query
    ORDER BY c DESC
    LIMIT 8
');

$titulo = $q ? "Busca: $q" : 'Buscar';
require_once __DIR__ . '/includes/header-novo.php';
?>

<div class="busca-page">
    <h1><i class="bi bi-search"></i> Buscar no Portal</h1>

    <form class="busca-form" method="GET" autocomplete="off">
        <input type="text" name="q" id="buscaInput" placeholder="Digite palavras-chave..." value="<?= esc($q) ?>" required minlength="2" autofocus>
        <?php if ($categoria_filtro): ?>
        <input type="hidden" name="categoria" value="<?= $categoria_filtro ?>">
        <?php endif; ?>
        <button type="submit"><i class="bi bi-search"></i> Buscar</button>
    </form>

    <?php if (!empty($sugestoes_populares) && !$q): ?>
    <div class="busca-sugestoes">
        <h4><i class="bi bi-fire"></i> Buscas Populares</h4>
        <div class="sugestoes-tags">
            <?php foreach ($sugestoes_populares as $s): ?>
            <a href="?q=<?= urlencode($s['query']) ?>" class="tag"><?= esc($s['query']) ?> (<?= $s['c'] ?>)</a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="busca-filtros">
        <span class="filtro-label"><i class="bi bi-funnel"></i> Filtrar por:</span>
        <a href="?q=<?= urlencode($q) ?>" class="filtro-chip <?= !$categoria_filtro ? 'active' : '' ?>">Todas</a>
        <?php foreach ($categorias as $cat): ?>
        <a href="?q=<?= urlencode($q) ?>&categoria=<?= $cat['id'] ?>" class="filtro-chip <?= $categoria_filtro == $cat['id'] ? 'active' : '' ?>" style="--cat-color: <?= esc($cat['cor']) ?>">
            <i class="<?= esc($cat['icone']) ?>"></i> <?= esc($cat['nome']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($q): ?>
    <div class="busca-resultados-info">
        <?php if ($total > 0): ?>
            <p><strong><?= $total ?></strong> resultado<?= $total !== 1 ? 's' : '' ?> para <em>"<?= esc($q) ?>"</em></p>
        <?php elseif ($termos): ?>
            <p>Nenhum resultado encontrado para <em>"<?= esc($q) ?>"</em></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($resultados)): ?>
    <div class="resultados-grid">
        <?php foreach ($resultados as $r): ?>
        <article class="resultado-item">
            <div class="resultado-cat" style="--cat-color: <?= esc($r['cat_cor'] ?? '#9b59b6') ?>">
                <i class="<?= esc($r['cat_icone'] ?? 'bi bi-folder') ?>"></i>
                <a href="<?= APP_URL ?>/categoria/<?= esc($r['cat_slug']) ?>"><?= esc($r['cat_nome'] ?? 'Sem categoria') ?></a>
            </div>
            <h3><a href="<?= APP_URL ?>/artigo/<?= esc($r['slug']) ?>"><?= esc($r['titulo']) ?></a></h3>
            <p class="resultado-resumo"><?= resumir($r['resumo'] ?: $r['conteudo'], 250) ?></p>
            <?php if ($r['tags']): ?>
            <div class="resultado-tags">
                <?php foreach (array_slice(explode(',', $r['tags']), 0, 5) as $tag): ?>
                <a href="?q=<?= urlencode(trim($tag)) ?>" class="tag">#<?= esc(trim($tag)) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="resultado-meta">
                <span><i class="bi bi-eye"></i> <?= $r['views'] ?> views</span>
                <?php if ($r['likes_count'] > 0): ?>
                <span><i class="bi bi-heart-fill"></i> <?= $r['likes_count'] ?></span>
                <?php endif; ?>
                <span><i class="bi bi-clock"></i> <?= tempo_relativo($r['publicado_em']) ?></span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php if ($total > $por_pagina): ?>
    <div class="paginacao">
        <?php
        $total_paginas = ceil($total / $por_pagina);
        $url_base = '?q=' . urlencode($q);
        if ($categoria_filtro) $url_base .= '&categoria=' . $categoria_filtro;
        ?>
        <?php if ($pagina > 1): ?>
        <a href="<?= $url_base ?>&pagina=<?= $pagina - 1 ?>" class="pag-btn"><i class="bi bi-chevron-left"></i> Anterior</a>
        <?php endif; ?>

        <span class="pag-info">Página <?= $pagina ?> de <?= $total_paginas ?></span>

        <?php if ($pagina < $total_paginas): ?>
        <a href="<?= $url_base ?>&pagina=<?= $pagina + 1 ?>" class="pag-btn">Próxima <i class="bi bi-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php elseif ($termos): ?>
        <div class="busca-empty">
            <p style="font-size:3rem;margin-bottom:15px">🔍</p>
            <h3>Nada encontrado</h3>
            <p>Tente:</p>
            <ul style="text-align:left;display:inline-block">
                <li>Usar termos mais genéricos</li>
                <li>Verificar a ortografia</li>
                <li>Tentar outras palavras-chave</li>
                <li>Navegar pelas <a href="<?= APP_URL ?>/biblioteca.php">categorias</a></li>
            </ul>
        </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
const buscaInput = document.getElementById('buscaInput');
let acTimeout;
buscaInput?.addEventListener('input', function() {
    clearTimeout(acTimeout);
    const q = this.value.trim();
    if (q.length < 2) return;
    acTimeout = setTimeout(async () => {
        try {
            const res = await fetch((window.SABERES_URL || '') + '/api/busca.php?q=' + encodeURIComponent(q));
            const data = await res.json();
            const existing = document.getElementById('autocomplete-box');
            if (existing) existing.remove();
            if (data.autocomplete && data.autocomplete.length > 0 && document.activeElement === buscaInput) {
                const box = document.createElement('div');
                box.id = 'autocomplete-box';
                box.style.cssText = 'position:absolute;background:var(--bg-primary,#fff);border:1px solid var(--border-color,rgba(0,0,0,0.1));border-radius:0.5rem;margin-top:0.5rem;z-index:100;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-height:300px;overflow-y:auto;width:100%;';
                buscaInput.parentElement.style.position = 'relative';
                box.innerHTML = data.autocomplete.map(item =>
                    `<a href="${window.SABERES_URL || ''}/artigo/${item.slug}" style="display:block;padding:0.6rem 1rem;color:inherit;text-decoration:none;border-bottom:1px solid rgba(0,0,0,0.05)">${item.titulo}</a>`
                ).join('');
                buscaInput.parentElement.appendChild(box);
            }
        } catch (e) {}
    }, 250);
});
buscaInput?.addEventListener('blur', function() {
    setTimeout(() => {
        const existing = document.getElementById('autocomplete-box');
        if (existing) existing.remove();
    }, 200);
});
</script>

<?php require_once __DIR__ . '/includes/footer-novo.php'; ?>

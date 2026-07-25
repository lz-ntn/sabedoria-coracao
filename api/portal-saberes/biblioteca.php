<?php
/**
 * Biblioteca — Portal Saberes Ancestrais
 * Página agregadora que exibe TODO o conteúdo por categoria real.
 * Sem filtros restritivos, com coerência visual 100% com o restante do site.
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();

// Categorias reais em ordem fixa
$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

// Buscar TODOS os artigos publicados com joins de estatísticas
$todosArtigos = $db->select(
    'SELECT a.id, a.titulo, a.slug, a.resumo, a.conteudo, a.tags, a.views, a.publicado_em,
            c.nome as cat_nome, c.slug as cat_slug, c.icone as cat_icone, c.cor as cat_cor, c.id as cat_id,
            COALESCE(l.likes_count, 0) AS likes_count,
            COALESCE(r.rating_avg, 0) AS rating_avg,
            COALESCE(cmt.cmt_count, 0) AS cmt_count
     FROM artigos a
     LEFT JOIN categorias c ON c.id = a.categoria_id
     LEFT JOIN (
        SELECT artigo_id, COUNT(*) AS likes_count
        FROM artigo_likes GROUP BY artigo_id
     ) l ON l.artigo_id = a.id
     LEFT JOIN (
        SELECT artigo_id, AVG(rating) AS rating_avg
        FROM artigo_ratings GROUP BY artigo_id
     ) r ON r.artigo_id = a.id
     LEFT JOIN (
        SELECT artigo_id, COUNT(*) AS cmt_count
        FROM comentarios WHERE status = "aprovado" GROUP BY artigo_id
     ) cmt ON cmt.artigo_id = a.id
     WHERE a.status = "publicado"
     ORDER BY c.ordem ASC, a.publicado_em DESC'
);

// Agrupar por categoria (preservando a ordem de $categorias)
$artigosPorCategoria = [];
foreach ($categorias as $cat) {
    $artigosPorCategoria[$cat['slug']] = [
        'id' => $cat['id'],
        'nome' => $cat['nome'],
        'slug' => $cat['slug'],
        'icone' => $cat['icone'],
        'cor' => $cat['cor'],
        'descricao' => $cat['descricao'] ?? '',
        'artigos' => [],
    ];
}
foreach ($todosArtigos as $a) {
    $catSlug = $a['cat_slug'] ?? null;
    if ($catSlug && isset($artigosPorCategoria[$catSlug])) {
        $artigosPorCategoria[$catSlug]['artigos'][] = $a;
    }
}

// Remover categorias vazias (defensivo) e reindexar
$artigosPorCategoria = array_values(array_filter($artigosPorCategoria, fn($c) => count($c['artigos']) > 0));

// Estatísticas globais
$totalArtigos = count($todosArtigos);
$totalCategorias = count($artigosPorCategoria);
$totalViews = array_sum(array_column($todosArtigos, 'views'));

// JSON seguro para o modal
$artigosJson = json_encode(array_map(function ($a) {
    return [
        'slug' => $a['slug'],
        'titulo' => $a['titulo'],
        'resumo' => strip_tags($a['resumo'] ?? ''),
        'cat_nome' => $a['cat_nome'] ?? '',
        'cat_slug' => $a['cat_slug'] ?? '',
        'cat_icone' => $a['cat_icone'] ?? 'bi bi-folder',
        'cat_cor' => $a['cat_cor'] ?? '#9b59b6',
        'tags' => $a['tags'] ?? '',
        'views' => (int) $a['views'],
        'date' => $a['publicado_em'] ?? '',
    ];
}, $todosArtigos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$titulo = 'Biblioteca';
$descricao = 'Biblioteca completa dos Saberes Ancestrais — todo o conhecimento organizado por temas';
require_once __DIR__ . '/includes/header-novo.php';
?>

<!-- ═══════════════════════════ HERO ═══════════════════════════ -->
<section class="biblioteca-hero">
    <h1 class="gradient-text">📚 Biblioteca de Saberes</h1>
    <p>Todo o conhecimento do Portal Saberes Ancestrais organizado por área temática. Explore, mergulhe e descubra.</p>

    <div class="biblioteca-stats">
        <div class="stat-card">
            <div class="number gradient-text" data-count="<?= $totalArtigos ?>">0</div>
            <div class="label">Artigos</div>
        </div>
        <div class="stat-card">
            <div class="number gradient-text" data-count="<?= $totalCategorias ?>">0</div>
            <div class="label">Categorias</div>
        </div>
        <div class="stat-card">
            <div class="number gradient-text" data-count="<?= $totalViews ?>">0</div>
            <div class="label">Visualizações</div>
        </div>
    </div>

    <!-- Toolbar minimalista: somente ferramentas utilitárias (não restritivas) -->
    <div class="biblioteca-toolbar">
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="bibSearch" placeholder="Filtrar visualização por palavra-chave..." autocomplete="off">
            <button class="clear-btn" id="clearSearch" aria-label="Limpar filtro" type="button">×</button>
        </div>
        <div class="toolbar-group">
            <label for="sortSelect">Ordenar</label>
            <select id="sortSelect" class="toolbar-btn">
                <option value="recent">Mais recentes</option>
                <option value="views">Mais vistos</option>
                <option value="title">A-Z</option>
            </select>
        </div>
        <div class="toolbar-group">
            <button class="toolbar-btn active" data-view="grid" id="viewGrid" type="button" title="Grade">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>
            <button class="toolbar-btn" data-view="list" id="viewList" type="button" title="Lista">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>
        <a href="<?= APP_URL ?>/busca.php" class="toolbar-btn toolbar-btn-search" title="Busca avançada">
            <i class="bi bi-search-heart"></i> Busca avançada
        </a>
        <button class="toolbar-btn toolbar-btn-random" id="randomBtn" type="button">
            🎲 Artigo aleatório
        </button>
    </div>
</section>

<!-- ═══════════════════════════ NAVEGAÇÃO POR CATEGORIA ═══════════════════════════ -->
<nav class="biblioteca-nav" aria-label="Navegação por categoria">
    <a href="#todas" class="bib-nav-pill active" data-target="todas">Todas</a>
    <?php foreach ($artigosPorCategoria as $cat): ?>
        <a href="#cat-<?= esc($cat['slug']) ?>" class="bib-nav-pill" data-target="cat-<?= esc($cat['slug']) ?>" style="--cat-color: <?= esc($cat['cor']) ?>">
            <i class="<?= esc($cat['icone']) ?>"></i> <?= esc($cat['nome']) ?>
            <span class="pill-count"><?= count($cat['artigos']) ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<!-- ═══════════════════════════ CATÁLOGO COMPLETO ═══════════════════════════ -->
<div id="catalogContainer">

    <?php if (empty($todosArtigos)): ?>
        <div class="no-results">
            <div class="icon">📚</div>
            <p>A biblioteca ainda está sendo construída. Volte em breve!</p>
        </div>
    <?php else: ?>

        <?php foreach ($artigosPorCategoria as $cat): ?>
            <section class="biblioteca-categoria" id="cat-<?= esc($cat['slug']) ?>" data-category="<?= esc($cat['slug']) ?>" style="--cat-color: <?= esc($cat['cor']) ?>">
                <header class="cat-header">
                    <div class="cat-icon">
                        <i class="<?= esc($cat['icone']) ?>"></i>
                    </div>
                    <div class="cat-info">
                        <h2><?= esc($cat['nome']) ?></h2>
                        <?php if (!empty($cat['descricao'])): ?>
                            <p class="cat-descricao"><?= esc($cat['descricao']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="cat-count-badge"><?= count($cat['artigos']) ?> artigo<?= count($cat['artigos']) !== 1 ? 's' : '' ?></div>
                </header>

                <div class="biblioteca-grid">
                    <?php foreach ($cat['artigos'] as $a):
                        $tagsArr = $a['tags'] ? array_filter(array_map('trim', explode(',', $a['tags']))) : [];
                        $resumo = $a['resumo'] ?: strip_tags($a['conteudo'] ?? '');
                    ?>
                        <article class="artigo-card bib-item"
                                 data-slug="<?= esc($a['slug']) ?>"
                                 data-title="<?= esc(mb_strtolower($a['titulo'])) ?>"
                                 data-views="<?= (int) $a['views'] ?>"
                                 data-date="<?= esc($a['publicado_em']) ?>"
                                 data-resumo="<?= esc(mb_strtolower(strip_tags($resumo))) ?>"
                                 data-tags="<?= esc(mb_strtolower($a['tags'] ?? '')) ?>"
                                 onclick="openPreview(event, '<?= esc($a['slug']) ?>')">

                            <div class="card-cat">
                                <i class="<?= esc($cat['icone']) ?>"></i>
                                <?= esc($cat['nome']) ?>
                            </div>

                            <h3>
                                <a href="<?= APP_URL ?>/artigo/<?= esc($a['slug']) ?>" onclick="event.stopPropagation()">
                                    <?= esc($a['titulo']) ?>
                                </a>
                            </h3>

                            <p class="card-resumo"><?= resumir($resumo, 200) ?></p>

                            <?php if (!empty($tagsArr)): ?>
                                <div class="card-tags">
                                    <?php foreach (array_slice($tagsArr, 0, 4) as $tag): ?>
                                        <span class="tag">#<?= esc($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="card-meta">
                                <span><i class="bi bi-eye"></i> <?= number_format((int) $a['views'], 0, ',', '.') ?></span>
                                <?php if ((int) $a['likes_count'] > 0): ?>
                                    <span><i class="bi bi-heart-fill"></i> <?= (int) $a['likes_count'] ?></span>
                                <?php endif; ?>
                                <?php if ((float) $a['rating_avg'] > 0): ?>
                                    <span><i class="bi bi-star-fill"></i> <?= number_format((float) $a['rating_avg'], 1) ?></span>
                                <?php endif; ?>
                                <?php if ((int) $a['cmt_count'] > 0): ?>
                                    <span><i class="bi bi-chat-dots"></i> <?= (int) $a['cmt_count'] ?></span>
                                <?php endif; ?>
                                <span><i class="bi bi-clock"></i> <?= tempo_relativo($a['publicado_em']) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<!-- ═══════════════════════════ MODAL DE PREVIEW ═══════════════════════════ -->
<div id="previewModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closePreview()" role="dialog" aria-modal="true">
    <div class="modal-content">
        <button class="modal-close" onclick="closePreview()" type="button" aria-label="Fechar">×</button>
        <div id="previewBody">
            <div class="modal-cat" id="previewCat" style="display:none"></div>
            <h2 id="previewTitle">Carregando...</h2>
            <div class="modal-body" id="previewContent">
                <p style="opacity:.5">Carregando conteúdo...</p>
            </div>
            <div class="modal-footer">
                <a href="#" id="previewLink" class="btn">📖 Ler artigo completo</a>
                <button class="btn btn-secondary" onclick="closePreview()" type="button">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════ SCRIPTS ═══════════════════════════ -->
<script>
const ALL_ARTICLES = <?= $artigosJson ?>;

// ── Stats counter animation ───────────────────────────────────
document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10) || 0;
    const duration = 900;
    const start = performance.now();
    function update(now) {
        const pct = Math.min((now - start) / duration, 1);
        el.textContent = Math.floor(pct * target).toLocaleString('pt-BR');
        if (pct < 1) requestAnimationFrame(update);
        else el.textContent = target.toLocaleString('pt-BR');
    }
    requestAnimationFrame(update);
});

// ── Navegação por categoria (scroll suave) ─────────────────────
document.querySelectorAll('.bib-nav-pill').forEach(pill => {
    pill.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.bib-nav-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        const targetId = pill.dataset.target;
        if (targetId === 'todas') {
            window.scrollTo({ top: document.querySelector('.biblioteca-nav').offsetTop - 20, behavior: 'smooth' });
        } else {
            const target = document.getElementById(targetId);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ── Search (filtro visual não-restritivo) ─────────────────────
const searchInput = document.getElementById('bibSearch');
const clearBtn = document.getElementById('clearSearch');
let searchTimeout;

function applyVisualFilter() {
    const q = searchInput.value.toLowerCase().trim();
    clearBtn.style.display = q ? 'block' : 'none';

    let visibleCount = 0;
    document.querySelectorAll('.bib-item').forEach(item => {
        const title = item.dataset.title || '';
        const tags = item.dataset.tags || '';
        const resumo = item.dataset.resumo || '';
        const match = !q || title.includes(q) || tags.includes(q) || resumo.includes(q);
        item.classList.toggle('hidden', !match);
        if (match) visibleCount++;
    });

    // Esconder seção de categoria se nenhum item visível
    document.querySelectorAll('.biblioteca-categoria').forEach(sec => {
        const visItems = sec.querySelectorAll('.bib-item:not(.hidden)').length;
        sec.classList.toggle('hidden-section', visItems === 0);
    });
}

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyVisualFilter, 150);
});
clearBtn.addEventListener('click', () => {
    searchInput.value = '';
    applyVisualFilter();
    searchInput.focus();
});

// Atalho "/" para focar busca
document.addEventListener('keydown', e => {
    if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
        e.preventDefault();
        searchInput.focus();
    }
    if (e.key === 'Escape') {
        if (document.getElementById('previewModal').style.display === 'flex') {
            closePreview();
        } else if (document.activeElement === searchInput && searchInput.value) {
            searchInput.value = '';
            applyVisualFilter();
        }
    }
});

// ── Sort (reordena dentro de cada seção) ──────────────────────
function applySort() {
    const sort = document.getElementById('sortSelect').value;
    document.querySelectorAll('.biblioteca-grid').forEach(grid => {
        const items = Array.from(grid.querySelectorAll('.bib-item'));
        items.sort((a, b) => {
            if (sort === 'title') return a.dataset.title.localeCompare(b.dataset.title, 'pt-BR');
            if (sort === 'views') return parseInt(b.dataset.views, 10) - parseInt(a.dataset.views, 10);
            return new Date(b.dataset.date) - new Date(a.dataset.date);
        });
        items.forEach(item => grid.appendChild(item));
    });
}
document.getElementById('sortSelect').addEventListener('change', applySort);

// ── View toggle (grid / list) ────────────────────────────────
function setView(view) {
    document.querySelectorAll('.toolbar-btn[data-view]').forEach(b => b.classList.remove('active'));
    const btn = document.querySelector(`.toolbar-btn[data-view="${view}"]`);
    if (btn) btn.classList.add('active');
    document.querySelectorAll('.biblioteca-grid').forEach(g => {
        g.classList.toggle('list-view', view === 'list');
    });
    try { localStorage.setItem('bib-view', view); } catch (e) {}
}
document.getElementById('viewGrid').addEventListener('click', () => setView('grid'));
document.getElementById('viewList').addEventListener('click', () => setView('list'));

// ── Artigo aleatório ──────────────────────────────────────────
document.getElementById('randomBtn').addEventListener('click', () => {
    if (!ALL_ARTICLES.length) return;
    const pick = ALL_ARTICLES[Math.floor(Math.random() * ALL_ARTICLES.length)];
    window.location.href = '<?= APP_URL ?>/artigo/' + pick.slug;
});

// ── Modal de preview ──────────────────────────────────────────
const previewCache = {};

function openPreview(event, slug) {
    // Se o clique foi em um link, deixa o link agir
    if (event.target.closest('a')) return;

    const modal = document.getElementById('previewModal');
    const title = document.getElementById('previewTitle');
    const content = document.getElementById('previewContent');
    const link = document.getElementById('previewLink');
    const catBox = document.getElementById('previewCat');

    title.textContent = 'Carregando...';
    content.innerHTML = '<p style="opacity:.5">Carregando conteúdo...</p>';
    catBox.style.display = 'none';
    link.href = '<?= APP_URL ?>/artigo/' + slug;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (previewCache[slug]) {
        renderPreview(previewCache[slug]);
        return;
    }

    fetch('<?= APP_URL ?>/api/artigo-preview.php?slug=' + encodeURIComponent(slug))
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = '<p style="opacity:.5">' + data.error + '</p>';
                return;
            }
            previewCache[slug] = data;
            renderPreview(data);
        })
        .catch(() => {
            content.innerHTML = '<p style="opacity:.5">Erro ao carregar.</p>';
        });
}

function renderPreview(data) {
    const title = document.getElementById('previewTitle');
    const content = document.getElementById('previewContent');
    const catBox = document.getElementById('previewCat');

    title.textContent = data.titulo;
    content.innerHTML = data.html + (data.has_more ? '<p class="preview-more">…</p>' : '');

    if (data.categoria) {
        catBox.innerHTML = '<i class="' + (data.cat_icone || 'bi bi-folder') + '"></i> ' + data.categoria;
        catBox.style.background = (data.cat_cor || '#9b59b6') + '22';
        catBox.style.color = data.cat_cor || '#9b59b6';
        catBox.style.borderColor = (data.cat_cor || '#9b59b6') + '55';
        catBox.style.display = 'inline-flex';
    }
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
    document.body.style.overflow = '';
}

// ── Init: restaurar view preferida ────────────────────────────
try {
    const savedView = localStorage.getItem('bib-view');
    if (savedView === 'list') setView('list');
} catch (e) {}
</script>

<?php require_once __DIR__ . '/includes/footer-novo.php'; ?>

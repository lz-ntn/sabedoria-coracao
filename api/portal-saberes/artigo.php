<?php
/**
 * Página de Artigo - Com funcionalidades interativas
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: index.php');
    exit;
}

$artigo = $db->fetch(
    'SELECT a.*, c.nome as cat_nome, c.slug as cat_slug, c.icone as cat_icone, c.cor as cat_cor,
            u.nome as autor_nome
     FROM artigos a
     LEFT JOIN categorias c ON c.id = a.categoria_id
     LEFT JOIN usuarios u ON u.id = a.autor_id
     WHERE a.slug = ? AND a.status = "publicado"',
    [$slug]
);

if (!$artigo) {
    header('HTTP/1.0 404 Not Found');
    $titulo = 'Artigo não encontrado';
    require_once __DIR__ . '/includes/header-novo.php';
    echo '<div style="text-align:center;padding:80px 0"><h2>Artigo não encontrado</h2><p style="opacity:0.6">O artigo que você procura não existe ou foi removido.</p><a href="index.php" class="btn btn-gold" style="display:inline-block;margin-top:20px">Voltar ao Início</a></div>';
    require_once __DIR__ . '/includes/footer-novo.php';
    exit;
}

if (!isset($_SESSION['views_' . $artigo['id']])) {
    $db->update('artigos', ['views' => $artigo['views'] + 1], 'id = ?', [$artigo['id']]);
    $_SESSION['views_' . $artigo['id']] = true;
}

$likes_total = $db->fetch('SELECT COUNT(*) AS c FROM artigo_likes WHERE artigo_id = ?', [$artigo['id']])['c'];
$user_liked = false;
if (esta_logado()) {
    $user_liked = (bool)$db->fetch('SELECT id FROM artigo_likes WHERE artigo_id = ? AND usuario_id = ?', [$artigo['id'], $_SESSION['usuario_id']]);
} else {
    $user_liked = (bool)$db->fetch('SELECT id FROM artigo_likes WHERE artigo_id = ? AND ip = ? AND usuario_id IS NULL', [$artigo['id'], $_SERVER['REMOTE_ADDR']]);
}

$rating_stats = $db->fetch('SELECT COUNT(*) AS total, AVG(rating) AS media FROM artigo_ratings WHERE artigo_id = ?', [$artigo['id']]);
$user_rating = null;
if (esta_logado()) {
    $ur = $db->fetch('SELECT rating FROM artigo_ratings WHERE artigo_id = ? AND usuario_id = ?', [$artigo['id'], $_SESSION['usuario_id']]);
    $user_rating = $ur['rating'] ?? null;
}
$rating_media = $rating_stats['media'] ? round((float)$rating_stats['media'], 1) : 0;
$rating_count = (int)$rating_stats['total'];

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$titulo = $artigo['titulo'];
$descricao = $artigo['resumo'] ?? mb_substr(strip_tags($artigo['conteudo']), 0, 160);
$url_artigo = APP_URL . '/artigo/' . $artigo['slug'];
$imagem_artigo = $artigo['imagem'] ? APP_URL . '/uploads/' . $artigo['imagem'] : APP_URL . '/assets/img/og-default.jpg';

$og_tags = [
    '<meta property="og:type" content="article">',
    '<meta property="og:title" content="' . esc($artigo['titulo']) . '">',
    '<meta property="og:description" content="' . esc($descricao) . '">',
    '<meta property="og:url" content="' . esc($url_artigo) . '">',
    '<meta property="og:image" content="' . esc($imagem_artigo) . '">',
    '<meta property="og:site_name" content="Saberes">',
    '<meta property="article:published_time" content="' . esc($artigo['publicado_em']) . '">',
    '<meta property="article:section" content="' . esc($artigo['cat_nome']) . '">',
];
if ($artigo['tags']) {
    foreach (explode(',', $artigo['tags']) as $tag) {
        $og_tags[] = '<meta property="article:tag" content="' . esc(trim($tag)) . '">';
    }
}
$og_tags[] = '<meta name="twitter:card" content="summary_large_image">';
$og_tags[] = '<meta name="twitter:title" content="' . esc($artigo['titulo']) . '">';
$og_tags[] = '<meta name="twitter:description" content="' . esc($descricao) . '">';
$og_tags[] = '<meta name="twitter:image" content="' . esc($imagem_artigo) . '">';
$headExtra = implode("\n    ", $og_tags);

require_once __DIR__ . '/includes/header-novo.php';
?>

<article itemscope itemtype="https://schema.org/Article">
    <header class="artigo-header">
        <div class="artigo-cat">
            <i class="<?= esc($artigo['cat_icone'] ?? 'bi bi-folder') ?>"></i>
            <a href="<?= APP_URL ?>/categoria/<?= esc($artigo['cat_slug']) ?>"><?= esc($artigo['cat_nome'] ?? 'Sem categoria') ?></a>
        </div>
        <h1 itemprop="headline"><?= esc($artigo['titulo']) ?></h1>
        <div class="artigo-meta">
            <span><i class="bi bi-person"></i> <span itemprop="author"><?= esc($artigo['autor_nome'] ?? 'Admin') ?></span></span>
            <span><i class="bi bi-calendar"></i> <time itemprop="datePublished" datetime="<?= esc($artigo['publicado_em']) ?>"><?= data_br($artigo['publicado_em']) ?></time></span>
            <span><i class="bi bi-eye"></i> <?= number_format($artigo['views'], 0, ',', '.') ?> views</span>
            <span><i class="bi bi-clock"></i> <?= max(1, round(str_word_count(strip_tags($artigo['conteudo'])) / 200)) ?> min de leitura</span>
        </div>

        <!-- Barra de Ações Interativas -->
        <div class="artigo-actions-bar">
            <button class="action-btn like-btn <?= $user_liked ? 'active' : '' ?>"
                    data-artigo="<?= $artigo['id'] ?>"
                    title="<?= $user_liked ? 'Remover curtida' : 'Curtir' ?>">
                <i class="bi bi-heart<?= $user_liked ? '-fill' : '' ?>"></i>
                <span class="like-count"><?= $likes_total ?></span>
            </button>

            <div class="rating-widget" data-artigo="<?= $artigo['id'] ?>">
                <div class="rating-stars" data-current="<?= $user_rating ?? 0 ?>">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= $i <= ($user_rating ?? 0) ? 'active' : '' ?>"
                          data-value="<?= $i ?>"
                          title="<?= $i ?> estrela<?= $i > 1 ? 's' : '' ?>">
                        <i class="bi bi-star<?= $i <= ($user_rating ?? 0) ? '-fill' : '' ?>"></i>
                    </span>
                    <?php endfor; ?>
                </div>
                <div class="rating-info">
                    <strong><?= $rating_media ?: '0.0' ?></strong>
                    <span class="rating-count">(<?= $rating_count ?>)</span>
                </div>
            </div>

            <button class="action-btn favorite-btn <?= esta_logado() && $db->fetch('SELECT id FROM favoritos WHERE artigo_id = ? AND usuario_id = ?', [$artigo['id'], $_SESSION['usuario_id']]) ? 'active' : '' ?>"
                    data-artigo="<?= $artigo['id'] ?>"
                    title="Favoritar">
                <i class="bi bi-bookmark<?= esta_logado() && $db->fetch('SELECT id FROM favoritos WHERE artigo_id = ? AND usuario_id = ?', [$artigo['id'], $_SESSION['usuario_id']]) ? '-fill' : '' ?>"></i>
                <span class="fav-count"><?= $db->fetch('SELECT COUNT(*) AS c FROM favoritos WHERE artigo_id = ?', [$artigo['id']])['c'] ?></span>
            </button>

            <button class="action-btn share-btn" data-artigo="<?= $artigo['id'] ?>" title="Compartilhar">
                <i class="bi bi-share"></i>
                <span>Compartilhar</span>
            </button>

            <a href="#comentarios" class="action-btn comment-btn" title="Ver comentários">
                <i class="bi bi-chat-dots"></i>
                <span>Comentar</span>
            </a>
        </div>
    </header>

    <div class="artigo-conteudo" itemprop="articleBody">
        <?= $artigo['conteudo'] ?>
    </div>

    <?php if ($artigo['tags']): ?>
    <div class="artigo-tags">
        <i class="bi bi-tags"></i>
        <?php foreach (explode(',', $artigo['tags']) as $tag): ?>
        <a href="<?= APP_URL ?>/busca?q=<?= urlencode(trim($tag)) ?>" class="tag">#<?= esc(trim($tag)) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($artigo['fonte']): ?>
    <div class="artigo-fonte">
        <i class="bi bi-box"></i> Conteúdo original de: <strong><?= esc($artigo['fonte']) ?></strong>
    </div>
    <?php endif; ?>

    <!-- Compartilhamento Social -->
    <div class="share-section" id="shareModal" style="display:none">
        <h4><i class="bi bi-share"></i> Compartilhar este artigo</h4>
        <div class="share-buttons">
            <a href="#" class="share-fb" data-platform="facebook" target="_blank" rel="noopener">
                <i class="bi bi-facebook"></i> Facebook
            </a>
            <a href="#" class="share-tw" data-platform="twitter" target="_blank" rel="noopener">
                <i class="bi bi-twitter-x"></i> Twitter
            </a>
            <a href="#" class="share-wa" data-platform="whatsapp" target="_blank" rel="noopener">
                <i class="bi bi-whatsapp"></i> WhatsApp
            </a>
            <a href="#" class="share-tg" data-platform="telegram" target="_blank" rel="noopener">
                <i class="bi bi-telegram"></i> Telegram
            </a>
            <a href="#" class="share-li" data-platform="linkedin" target="_blank" rel="noopener">
                <i class="bi bi-linkedin"></i> LinkedIn
            </a>
            <a href="#" class="share-em" data-platform="email">
                <i class="bi bi-envelope"></i> Email
            </a>
            <button class="share-cp" data-platform="copy">
                <i class="bi bi-clipboard"></i> Copiar Link
            </button>
        </div>
    </div>
</article>

<!-- SEÇÃO DE COMENTÁRIOS DINÂMICA -->
<section class="comentarios-section" id="comentarios">
    <h3><i class="bi bi-chat-dots"></i> Comentários <span id="comentarios-count">0</span></h3>

    <div id="comentarios-lista" class="comentarios-lista">
        <div class="comentarios-loading"><i class="bi bi-arrow-clockwise"></i> Carregando comentários...</div>
    </div>

    <div class="comentario-form">
        <h4>Deixe seu comentário</h4>
        <form id="form-comentario">
            <input type="hidden" name="artigo_id" value="<?= $artigo['id'] ?>">
            <input type="hidden" name="parent_id" id="parent_id" value="">
            <div class="form-info" id="form-info"></div>
            <textarea name="conteudo" id="comentario-texto" placeholder="Compartilhe seus pensamentos..." required minlength="3" maxlength="2000"></textarea>
            <?php if (!esta_logado()): ?>
            <div class="form-row">
                <input type="text" name="autor_nome" id="autor_nome" placeholder="Seu nome *" required minlength="2" maxlength="100">
                <input type="email" name="autor_email" id="autor_email" placeholder="Seu email *" required>
            </div>
            <?php endif; ?>
            <div class="form-buttons">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-send"></i> <span id="btn-texto">Enviar Comentário</span>
                </button>
                <button type="button" class="btn-cancel-reply" id="btn-cancel-reply" style="display:none">
                    <i class="bi bi-x"></i> Cancelar Resposta
                </button>
            </div>
        </form>
        <p class="form-note">Os comentários são publicados imediatamente e podem ser gerenciados pela comunidade.</p>
    </div>
</section>

<!-- JSON-LD Schema.org -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": <?= json_encode($artigo['titulo']) ?>,
  "description": <?= json_encode($descricao) ?>,
  "datePublished": <?= json_encode($artigo['publicado_em']) ?>,
  "author": {
    "@type": "Person",
    "name": <?= json_encode($artigo['autor_nome'] ?? 'Admin') ?>
  },
  "publisher": {
    "@type": "Organization",
    "name": "Saberes",
    "logo": {
      "@type": "ImageObject",
      "url": "<?= APP_URL ?>/assets/img/logo.png"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "<?= esc($url_artigo) ?>"
  },
  "image": "<?= esc($imagem_artigo) ?>"
}
</script>

<script src="<?= APP_URL ?>/assets/js/artigo.js"></script>
<?php require_once __DIR__ . '/includes/footer-novo.php'; ?>

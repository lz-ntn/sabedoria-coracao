<?php
/**
 * Página Inicial do Portal - Design Novo com Integração PHP
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$artigos_recentes = $db->select(
    'SELECT a.*, c.nome as cat_nome, c.slug as cat_slug, c.icone as cat_icone, c.cor as cat_cor,
            u.nome as autor_nome
     FROM artigos a
     LEFT JOIN categorias c ON c.id = a.categoria_id
     LEFT JOIN usuarios u ON u.id = a.autor_id
     WHERE a.status = "publicado"
     ORDER BY a.publicado_em DESC
     LIMIT 6'
);

$stats = $db->fetch(
    'SELECT
        (SELECT COUNT(*) FROM artigos WHERE status = "publicado") AS total_artigos,
        (SELECT COUNT(*) FROM categorias) AS total_categorias,
        (SELECT COUNT(DISTINCT autor_id) FROM artigos WHERE status = "publicado") AS total_autores,
        (SELECT SUM(views) FROM artigos) AS total_views
     FROM DUAL'
);

$titulo = 'Início';
$descricao = APP_DESC;
require_once __DIR__ . '/includes/header-novo.php';
?>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
      <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4" style="background:rgba(212,162,78,0.1);border:1px solid rgba(212,162,78,0.2);color:var(--gold);font-size:0.8rem;animation:fadeDown 0.8s var(--ease);">
        <i class="fa-solid fa-star"></i> Sabedoria Ancestral
      </div>
      <h1>
        Onde a <span class="gradient-text">Ciência</span>,<br>
        a <span class="gradient-text">Espiritualidade</span> e a<br>
        <span class="gradient-text">Filosofia</span> se encontram
      </h1>
      <p>Três vozes de uma mesma canção. A sabedoria não mora em gavetas separadas.</p>
      <div class="hero-cta d-flex flex-wrap justify-content-center gap-3">
        <a href="<?= APP_URL ?>/biblioteca.php" class="btn btn-gold">
          <i class="fa-solid fa-compass me-1"></i> Explorar Conhecimento <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
        <a href="<?= APP_URL ?>/pagina/sobre" class="btn btn-outline-gold">
          <i class="fa-solid fa-feather me-1"></i> Nossa Missão
        </a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <span class="num" data-target="<?= $stats['total_artigos'] ?? 0 ?>">0</span>
          <span class="label"><i class="fa-regular fa-file-lines me-1"></i>Artigos</span>
        </div>
        <div class="hero-stat">
          <span class="num" data-target="<?= $stats['total_categorias'] ?? 0 ?>">0</span>
          <span class="label"><i class="fa-regular fa-folder me-1"></i>Categorias</span>
        </div>
        <div class="hero-stat">
          <span class="num" data-target="<?= $stats['total_autores'] ?? 0 ?>">0</span>
          <span class="label"><i class="fa-solid fa-spa me-1"></i>Autores</span>
        </div>
        <div class="hero-stat">
          <span class="num" data-target="<?= number_format($stats['total_views'] ?? 0) ?>">0</span>
          <span class="label"><i class="fa-regular fa-eye me-1"></i>Visualizações</span>
        </div>
      </div>
    </div>
    <div class="scroll-indicator">
      <span>Explore</span>
      <div class="line"></div>
    </div>
  </section>

  <!-- FEATURED: TRES PILARES -->
  <section class="section">
    <div class="container">
      <div class="section-header reveal">
        <div class="section-tag"><i class="fa-regular fa-gem"></i> TRÊS PILARES</div>
        <h2 class="section-title">A Tríade do Saber</h2>
        <p class="section-sub">Ciência que observa, espírito que sente, pensamento que interroga. A mesma busca em três linguagens.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-4 reveal reveal-delay-1">
          <div class="featured-card h-100">
            <div class="card-icon-feat"><i class="fa-solid fa-globe"></i></div>
            <h3>Ciência</h3>
            <p>De Göbekli Tepe ao HeartMath, a ciência valida cada vez mais o que os ancestrais sempre disseram. O método moderno encontrando a sabedoria antiga.</p>
            <span class="card-link">Explorar artigos <i class="fa-solid fa-arrow-right fa-xs"></i></span>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-delay-2">
          <div class="featured-card h-100">
            <div class="card-icon-feat"><i class="fa-solid fa-hands-pray"></i></div>
            <h3>Espiritualidade</h3>
            <p>Não é crença. É experiência direta. Pneuma, Kundalini, Gnose — o sopro, o fogo, o conhecimento que não se aprende, se vive.</p>
            <span class="card-link">Explorar saberes <i class="fa-solid fa-arrow-right fa-xs"></i></span>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-delay-3">
          <div class="featured-card h-100">
            <div class="card-icon-feat"><i class="fa-solid fa-brain"></i></div>
            <h3>Filosofia</h3>
            <p>Ser ou Ter? Felicidade é verbo ou substantivo? O Tao que pode ser dito não é o Tao eterno. Perguntas que importam, sem respostas prontas.</p>
            <span class="card-link">Explorar reflexões <i class="fa-solid fa-arrow-right fa-xs"></i></span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CATEGORIAS -->
  <section class="section">
    <div class="container">
      <div class="section-header reveal">
        <div class="section-tag"><i class="fa-solid fa-layer-group"></i> CATEGORIAS</div>
        <h2 class="section-title">Campos do Conhecimento</h2>
        <p class="section-sub">Explore os diferentes saberes através de suas categorias.</p>
      </div>

      <div class="row g-4">
        <?php foreach ($categorias as $cat):
            $total = $db->contar('artigos', 'categoria_id = ? AND status = "publicado"', [$cat['id']]);
        ?>
        <div class="col-6 col-md-4 col-lg-3 reveal">
          <a href="<?= APP_URL ?>/categoria/<?= esc($cat['slug']) ?>" class="category-card">
            <div class="cat-icon" style="background: <?= esc($cat['cor']) ?>20; color: <?= esc($cat['cor']) ?>">
              <i class="<?= esc($cat['icone']) ?>"></i>
            </div>
            <h4><?= esc($cat['nome']) ?></h4>
            <p><?= $total ?> artigos</p>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ARTIGOS RECENTES -->
  <?php if (!empty($artigos_recentes)): ?>
  <section class="section">
    <div class="container">
      <div class="section-header reveal">
        <div class="section-tag"><i class="fa-solid fa-clock-rotate-left"></i> RECENTES</div>
        <h2 class="section-title">Últimas Publicações</h2>
        <p class="section-sub">Conteúdo fresco adicionado recentemente ao portal.</p>
      </div>

      <div class="row g-4">
        <?php foreach ($artigos_recentes as $artigo): ?>
        <div class="col-md-6 col-lg-4 reveal">
          <article class="article-card">
            <div class="article-cat" style="color: <?= esc($artigo['cat_cor'] ?? '#d4a24e') ?>">
              <i class="<?= esc($artigo['cat_icone'] ?? 'fa-solid fa-folder') ?> me-1"></i>
              <?= esc($artigo['cat_nome'] ?? 'Sem categoria') ?>
            </div>
            <h3><a href="<?= APP_URL ?>/artigo/<?= esc($artigo['slug']) ?>"><?= esc($artigo['titulo']) ?></a></h3>
            <p class="article-excerpt"><?= resumir($artigo['resumo'] ?: $artigo['conteudo'], 150) ?></p>
            <div class="article-meta">
              <span><i class="fa-regular fa-eye me-1"></i><?= $artigo['views'] ?></span>
              <span><i class="fa-regular fa-clock me-1"></i><?= tempo_relativo($artigo['publicado_em']) ?></span>
              <?php if ($artigo['autor_nome']): ?>
              <span><i class="fa-regular fa-user me-1"></i><?= esc($artigo['autor_nome']) ?></span>
              <?php endif; ?>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-5 reveal">
        <a href="<?= APP_URL ?>/biblioteca.php" class="btn btn-gold">
          Ver Todos os Artigos <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- QUOTES (Bootstrap Carousel) -->
  <section class="quote-section">
    <div class="container">
      <div class="section-header reveal">
        <div class="section-tag"><i class="fa-solid fa-quote-left"></i> SABEDORIA MILENAR</div>
        <h2 class="section-title">Vozes que Inspiram</h2>
      </div>

      <div id="quoteCarousel" class="carousel slide quote-carousel reveal" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <blockquote>Não mude para ser amado. Cresça a partir do que é.</blockquote>
            <cite>— Ser-Ter</cite>
          </div>
          <div class="carousel-item">
            <blockquote>A felicidade não é algo que somos, é algo que fazemos. Não é um substantivo, é um verbo.</blockquote>
            <cite>— A Felicidade é um Verbo</cite>
          </div>
          <div class="carousel-item">
            <blockquote>O Tao que pode ser dito não é o Tao eterno. O nome que pode ser nomeado não é o nome eterno.</blockquote>
            <cite>— Tao Te Ching</cite>
          </div>
          <div class="carousel-item">
            <blockquote>"O conhecimento sem prática é vazio. A prática sem conhecimento é cega."</blockquote>
            <cite>— Portal Saberes</cite>
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#quoteCarousel" data-bs-slide="prev">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#quoteCarousel" data-bs-slide="next">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </section>

  <!-- NEWSLETTER -->
  <section class="section">
    <div class="container">
      <div class="newsletter-box reveal">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <h3><i class="fa-regular fa-envelope me-2"></i>Newsletter Saberes</h3>
            <p>Receba artigos selecionados diretamente no seu email. Sem spam, apenas sabedoria.</p>
          </div>
          <div class="col-lg-6">
            <form class="newsletter-form" onsubmit="event.preventDefault(); showToast('Inscrito com sucesso!');">
              <div class="input-group">
                <input type="email" class="form-control" placeholder="Seu melhor email" required>
                <button type="submit" class="btn btn-gold">Inscrever</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php require_once __DIR__ . '/includes/footer-novo.php'; ?>
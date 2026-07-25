<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

http_response_code(404);
$titulo = 'Página não encontrada';
$descricao = 'O conteúdo que você procura não existe.';
require_once __DIR__ . '/includes/header-novo.php';
?>
<section class="section" style="min-height:60vh;display:flex;align-items:center;">
  <div class="container text-center">
    <div style="font-size:8rem;font-weight:900;background:var(--grad-hero);-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1;margin-bottom:10px;opacity:0.4;">404</div>
    <h1 class="mb-3" style="font-size:1.8rem;">Página não encontrada</h1>
    <p class="text-secondary mb-4" style="font-size:1.1rem;max-width:500px;margin:0 auto;">O conteúdo que você procura foi removido, renomeado ou está temporariamente indisponível.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap mb-5">
      <a href="<?= APP_URL ?>/index.php" class="btn btn-gold"><i class="fa-solid fa-house me-1"></i> Ir para o Início</a>
      <a href="<?= APP_URL ?>/busca.php" class="btn btn-outline-gold"><i class="fa-solid fa-search me-1"></i> Buscar no Portal</a>
    </div>
    <div>
      <h5 class="text-secondary mb-3" style="opacity:0.5;">Talvez você esteja procurando:</h5>
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <?php foreach ($categorias as $cat): ?>
        <a href="<?= APP_URL ?>/categoria/<?= esc($cat['slug']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill" style="border-color:<?= esc($cat['cor']) ?>55;color:<?= esc($cat['cor']) ?>;">
          <i class="<?= esc($cat['icone']) ?> me-1"></i><?= esc($cat['nome']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer-novo.php'; ?>

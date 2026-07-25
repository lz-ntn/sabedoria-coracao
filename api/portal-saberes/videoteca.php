<?php
/**
 * Página Videoteca - Portal Saberes Ancestrais
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$titulo = 'Videoteca';
$descricao = 'Vídeos e palestras sobre saberes ancestrais.';
require_once __DIR__ . '/includes/header-novo.php';
?>

  <!-- PAGE HEADER -->
  <section class="page-header">
    <div class="container">
      <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background:rgba(212,162,78,0.1);border:1px solid rgba(212,162,78,0.2);color:var(--gold);font-size:0.8rem;">
        <i class="fa-solid fa-video"></i> VIDEOTECA
      </div>
      <h1>Sabedoria para assistir</h1>
      <p>Vídeos e palestras sobre os saberes que unem ciência, espiritualidade e filosofia.</p>
    </div>
  </section>

  <!-- CONTENT -->
  <section class="section">
    <div class="container">
      <div class="section-header reveal">
        <h2 class="section-title">Em breve</h2>
        <p class="section-sub">Estamos preparando conteúdo em vídeo para você.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-6 mx-auto text-center reveal">
          <div class="coming-soon-card">
            <i class="fa-solid fa-video fa-4x mb-4" style="color:var(--gold)"></i>
            <h3>Videoteca em desenvolvimento</h3>
            <p class="text-secondary">
              Em breve traremos palestras, documentários e conteúdos especiais em formato de vídeo.
            </p>
            <a href="<?= APP_URL ?>/index.php" class="btn btn-outline-gold mt-3">
              <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao início
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php require_once __DIR__ . '/includes/footer-novo.php'; ?>
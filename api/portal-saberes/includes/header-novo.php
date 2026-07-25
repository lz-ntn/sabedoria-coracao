<?php
/**
 * Header do Portal Saberes Ancestrais
 */
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $titulo ?? 'Início' ?> — Portal Saberes Ancestrais</title>
  <meta name="description" content="<?= $descricao ?? 'Wiki colaborativa sobre saberes ancestrais.' ?>">
  <meta name="theme-color" content="#0a0a12">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/estilo-novo.css">
  <script>window.SABERES_URL = '<?= APP_URL ?>';</script>
  <?= $headExtra ?? '' ?>
</head>
<body>

<div class="toast-container"></div>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="<?= APP_URL ?>/index.php">
      <span class="brand-star"><i class="fa-solid fa-star"></i></span>
      Saberes Ancestrais
    </a>

    <div class="d-flex align-items-center gap-2">
      <button class="theme-btn" id="themeToggle" aria-label="Alternar tema">
        <i class="fa-regular fa-sun"></i>
      </button>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/index.php">Início</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/biblioteca.php">Biblioteca</a></li>
        <?php if (isset($categorias) && count($categorias) > 0): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            Temas
          </a>
          <ul class="dropdown-menu">
            <?php foreach ($categorias as $cat): ?>
            <li><a class="dropdown-item" href="<?= APP_URL ?>/categoria/<?= esc($cat['slug']) ?>">
              <?= esc($cat['nome']) ?>
              <?php if (isset($db)): ?>
              <span class="cat-badge"><?= $db->contar('artigos', 'categoria_id = ? AND status = "publicado"', [$cat['id']]) ?></span>
              <?php endif; ?>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/busca.php">Buscar</a></li>
        <li class="nav-item ms-lg-2">
          <?php if (esta_logado()): ?>
            <div class="d-flex gap-1">
              <a class="btn btn-sm btn-outline-light" href="<?= APP_URL ?>/admin/index.php"><i class="fa-solid fa-user-gear"></i></a>
              <a class="btn btn-sm btn-outline-light" href="<?= APP_URL ?>/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
          <?php else: ?>
            <a class="btn btn-sm btn-gold" href="<?= APP_URL ?>/auth/login.php">Entrar</a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="subnav">
  <div class="container d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-3">
    <a href="<?= APP_URL ?>/pagina/sobre"><i class="fa-regular fa-circle-question"></i> Sobre</a>
    <a href="<?= APP_URL ?>/pagina/politica-edicao"><i class="fa-regular fa-file-lines"></i> Política de Edição</a>
    <a href="<?= APP_URL ?>/pagina/faq"><i class="fa-regular fa-comments"></i> FAQ</a>
    <a href="<?= APP_URL ?>/pagina/comunidade"><i class="fa-regular fa-users"></i> Comunidade</a>
    <a href="<?= APP_URL ?>/pagina/licenca"><i class="fa-regular fa-scale-balanced"></i> Licença de Uso</a>
    <a href="<?= APP_URL ?>/pagina/contato"><i class="fa-regular fa-envelope"></i> Contato</a>
  </div>
</div>

<main>

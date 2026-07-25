<?php
/**
 * Página Estática
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$slug = $_GET['slug'] ?? '';

$pagina = $db->fetch('SELECT * FROM paginas WHERE slug = ? AND status = "publicado"', [$slug]);
if (!$pagina) {
    header('Location: index.php');
    exit;
}

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$titulo = $pagina['titulo'];
require_once __DIR__ . '/includes/header-novo.php';
?>

<div class="page-header">
    <div class="container">
        <h1><?= esc($pagina['titulo']) ?></h1>
    </div>
</div>
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="artigo-conteudo" style="max-width:800px;margin:0 auto">
            <?= $pagina['conteudo'] ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-novo.php'; ?>

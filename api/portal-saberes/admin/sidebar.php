<div class="sidebar-header">
    <span class="sidebar-logo">🕉️</span>
    <span class="sidebar-title">Admin</span>
</div>
<nav class="sidebar-nav">
    <a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../artigos/index.php"><i class="bi bi-file-text"></i> Artigos</a>
    <a href="../categorias/index.php"><i class="bi bi-folder"></i> Categorias</a>
    <a href="../comentarios/index.php"><i class="bi bi-chat-dots"></i> Comentários</a>
    <a href="../usuarios/index.php"><i class="bi bi-people"></i> Usuários</a>
    <a href="../paginas/index.php"><i class="bi bi-file-earmark"></i> Páginas</a>
    <a href="../midia/index.php"><i class="bi bi-images"></i> Mídia</a>
    <hr>
    <a href="../../index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver Site</a>
    <a href="../../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
</nav>
<div class="sidebar-footer"><?= esc($_SESSION['usuario_nome']) ?></div>

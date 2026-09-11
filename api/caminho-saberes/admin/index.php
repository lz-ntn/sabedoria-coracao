<?php
/**
 * Admin - Painel de Controle do Caminho Saberes
 * 
 * Gerencia: Lições, Artigos (Biblioteca), Categorias, Usuários, Newsletter
 * Acesso: http://localhost:8081/admin/
 * Login: admin (senha via php seed-admin.php)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance();
$erro_login = '';
$logado = false;
$acao = $_GET['acao'] ?? 'dashboard';
$mensagem = '';

// ══════════════════════════════════════════
// Login com CSRF + Rate Limiting
// ══════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    \Core\Csrf::validateOrFail();

    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $rateLimiter = new \Core\RateLimiter(
        $db->getPdo(),
        'login_attempts',
        LOGIN_MAX_ATTEMPTS,
        LOGIN_BLOCK_MINUTES
    );

    if ($rateLimiter->isBlocked($ip)) {
        $erro_login = $_SESSION['_rate_limit_message'] ?? 'Muitas tentativas. Aguarde e tente novamente.';
        unset($_SESSION['_rate_limit_message']);
    } else {
        $admin = $db->fetch('SELECT * FROM admin WHERE usuario = ?', [$usuario]);
        if ($admin && password_verify($senha, $admin['senha'])) {
            $rateLimiter->clearAttempts($ip);
            session_regenerate_id(true);
            \Core\Csrf::rotate();

            $_SESSION['admin_logado'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
        } else {
            $rateLimiter->recordAttempt($ip);
            $restantes = $rateLimiter->getRemainingAttempts($ip);
            $erro_login = "Usuário ou senha inválidos. Tentativas restantes: {$restantes}.";
        }
    }
}

if (!empty($_SESSION['admin_logado'])) {
    $logado = true;
}

if (isset($_GET['logout'])) {
    session_destroy();
    redirecionar('index.php');
}

// ══════════════════════════════════════════
// AÇÕES CRUD (requer login)
// ══════════════════════════════════════════
if ($logado && $_SERVER['REQUEST_METHOD'] === 'POST') {
    \Core\Csrf::validateOrFail();

    // --- CATEGORIAS ---
    if (isset($_POST['salvar_categoria'])) {
        $id = $_POST['id'] ?? null;
        $dados = [
            'nome' => trim($_POST['nome']),
            'slug' => trim($_POST['slug']) ?: slugify($_POST['nome']),
            'descricao' => trim($_POST['descricao']),
            'icone' => trim($_POST['icone']) ?: 'bi bi-book',
            'cor' => trim($_POST['cor']) ?: '#9b59b6',
            'ordem' => (int)($_POST['ordem'] ?? 0),
        ];
        if ($id) {
            $db->update('categorias', $dados, 'id = ?', [$id]);
            $mensagem = 'Categoria atualizada!';
        } else {
            $db->insert('categorias', $dados);
            $mensagem = 'Categoria criada!';
        }
        redirecionar('index.php?acao=categorias');
    }

    if (isset($_POST['excluir_categoria'])) {
        $id = (int)$_POST['id'];
        // Verificar se tem lições
        $count = $db->fetch('SELECT COUNT(*) as c FROM licoes WHERE categoria_id = ?', [$id])['c'];
        if ($count > 0) {
            $mensagem = 'Erro: Categoria tem lições/artigos. Remova-os primeiro.';
        } else {
            $db->delete('DELETE FROM categorias WHERE id = ?', [$id]);
            $mensagem = 'Categoria excluída!';
        }
        redirecionar('index.php?acao=categorias');
    }

    // --- LIÇÕES / ARTIGOS ---
    if (isset($_POST['salvar_licao'])) {
        $id = $_POST['id'] ?? null;
        $tipo = $_POST['tipo'] ?? 'licao';
        $dados = [
            'categoria_id' => (int)$_POST['categoria_id'],
            'tipo' => $tipo,
            'titulo' => trim($_POST['titulo']),
            'slug' => trim($_POST['slug']) ?: slugify($_POST['titulo']),
            'resumo' => $tipo === 'artigo' ? trim($_POST['resumo']) : null,
            'conteudo' => $_POST['conteudo'],
            'tags' => $tipo === 'artigo' ? trim($_POST['tags']) : null,
            'imagem' => $tipo === 'artigo' ? trim($_POST['imagem']) : null,
            'fonte' => $tipo === 'artigo' ? trim($_POST['fonte']) : null,
            'nivel' => $tipo === 'licao' ? $_POST['nivel'] : 'avancado',
            'duracao_min' => $tipo === 'licao' ? (int)$_POST['duracao_min'] : null,
            'ordem' => (int)($_POST['ordem'] ?? 0),
            'autor_id' => $tipo === 'artigo' && !empty($_POST['autor_id']) ? (int)$_POST['autor_id'] : null,
            'publicado_em' => $tipo === 'artigo' && !empty($_POST['publicado_em']) ? $_POST['publicado_em'] : null,
        ];
        if ($id) {
            $db->update('licoes', $dados, 'id = ?', [$id]);
            $mensagem = ($tipo === 'artigo' ? 'Artigo' : 'Lição') . ' atualizado!';
        } else {
            $dados['criada_em'] = date('Y-m-d H:i:s');
            $db->insert('licoes', $dados);
            $mensagem = ($tipo === 'artigo' ? 'Artigo' : 'Lição') . ' criado!';
        }
        redirecionar('index.php?acao=licoes');
    }

    if (isset($_POST['excluir_licao'])) {
        $id = (int)$_POST['id'];
        $db->delete('DELETE FROM licoes WHERE id = ?', [$id]);
        $mensagem = 'Item excluído!';
        redirecionar('index.php?acao=licoes');
    }

    if (isset($_POST['toggle_publicar'])) {
        $id = (int)$_POST['id'];
        $item = $db->fetch('SELECT publicado_em FROM licoes WHERE id = ?', [$id]);
        if ($item['publicado_em']) {
            $db->update('licoes', ['publicado_em' => null], 'id = ?', [$id]);
            $mensagem = 'Despublicado!';
        } else {
            $db->update('licoes', ['publicado_em' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
            $mensagem = 'Publicado!';
        }
        redirecionar('index.php?acao=licoes');
    }

    // --- IMPORTAR DO PORTAL ---
    if (isset($_POST['importar_portal'])) {
        // Executa o script de importação via include
        require_once __DIR__ . '/../database/import_portal.php';
        $mensagem = 'Importação executada! Verifique logs acima.';
    }
}

// ══════════════════════════════════════════
// DADOS PARA VIEWS
// ══════════════════════════════════════════
$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$admins = $db->select('SELECT id, usuario, email FROM admin');

$licoes = [];
$licoes_tipo = $_GET['tipo'] ?? 'licao'; // licao ou artigo
if ($logado) {
    $where = 'tipo = ?';
    $params = [$licoes_tipo];
    if (!empty($_GET['categoria'])) {
        $where .= ' AND categoria_id = ?';
        $params[] = (int)$_GET['categoria'];
    }
    $licoes = $db->select(
        "SELECT l.*, c.nome as categoria_nome, c.cor as categoria_cor, u.nome as autor_nome
         FROM licoes l
         LEFT JOIN categorias c ON c.id = l.categoria_id
         LEFT JOIN usuarios u ON u.id = l.autor_id
         WHERE $where
         ORDER BY l.ordem, l.titulo",
        $params
    );
}

$stats = [];
if ($logado) {
    $stats['usuarios'] = $db->fetch('SELECT COUNT(*) as total FROM usuarios')['total'];
    $stats['licoes'] = $db->fetch('SELECT COUNT(*) as total FROM licoes WHERE tipo="licao"')['total'];
    $stats['artigos'] = $db->fetch('SELECT COUNT(*) as total FROM licoes WHERE tipo="artigo"')['total'];
    $stats['categorias'] = count($categorias);
    $stats['progresso'] = $db->fetch('SELECT COUNT(*) as total FROM progresso')['total'];
    $stats['newsletter'] = $db->fetch('SELECT COUNT(*) as total FROM newsletter WHERE ativo = 1')['total'];
    $stats['favoritos'] = $db->fetch('SELECT COUNT(*) as total FROM favoritos')['total'];
    $stats['quiz'] = $db->fetch('SELECT COUNT(*) as total FROM quiz_resultados')['total'];
    $stats['discussoes'] = $db->fetch('SELECT COUNT(*) as total FROM discussoes WHERE status="aprovado"')['total'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a1a;
            color: #e0e0e0;
            min-height: 100vh;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }
        .login-box {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .login-box h1 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 1.5rem;
            background: linear-gradient(90deg, #e2b714, #f39c12);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .login-box p.subtitle {
            text-align: center;
            opacity: 0.5;
            margin-bottom: 30px;
            font-size: 0.85rem;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; opacity: 0.8; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px 16px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            color: #fff; font-size: 1rem;
            transition: border 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #f39c12;
        }
        .form-group textarea { min-height: 200px; resize: vertical; font-family: monospace; }
        .btn {
            width: 100%; padding: 12px; border: none; border-radius: 8px;
            font-size: 1rem; font-weight: bold; cursor: pointer; transition: transform 0.2s;
        }
        .btn-primary { background: linear-gradient(90deg, #e2b714, #f39c12); color: #1a1a2e; }
        .btn-secondary { background: rgba(255,255,255,0.1); color: #fff; }
        .btn-danger { background: linear-gradient(90deg, #e74c3c, #c0392b); color: #fff; }
        .btn:hover { transform: translateY(-2px); }
        .btn-sm { width: auto; padding: 8px 16px; font-size: 0.85rem; }
        .erro { background: rgba(231,76,60,0.2); color: #e74c3c; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .sucesso { background: rgba(46,204,113,0.2); color: #2ecc71; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .top-bar { background: rgba(255,255,255,0.03); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); flex-wrap: wrap; gap: 10px; }
        .top-bar h2 { font-size: 1.2rem; }
        .top-bar .nav { display: flex; gap: 15px; flex-wrap: wrap; }
        .top-bar a { color: #f39c12; text-decoration: none; font-size: 0.9rem; padding: 8px 12px; border-radius: 6px; transition: background 0.2s; }
        .top-bar a:hover, .top-bar a.active { background: rgba(243,156,18,0.2); }
        .dashboard { padding: 30px; max-width: 1200px; margin: 0 auto; }
        .dashboard h3 { margin: 30px 0 20px; font-size: 1.3rem; color: #f39c12; }
        .dashboard h3:first-child { margin-top: 0; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: rgba(255,255,255,0.05); border-radius: 12px; padding: 20px; border: 1px solid rgba(255,255,255,0.08); text-align: center; }
        .card .numero { font-size: 2.5rem; font-weight: bold; color: #f39c12; margin-bottom: 5px; }
        .card .rotulo { font-size: 0.85rem; opacity: 0.6; }
        .card .icone { font-size: 1.5rem; margin-bottom: 10px; }
        .cat-bar { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .cat-bar .cat-nome { min-width: 140px; font-size: 0.9rem; }
        .cat-bar .barra { flex: 1; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; }
        .cat-bar .fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
        .cat-bar .pct { min-width: 50px; text-align: right; font-size: 0.85rem; opacity: 0.7; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: rgba(255,255,255,0.03); border-radius: 8px; overflow: hidden; }
        table th { text-align: left; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem; opacity: 0.6; background: rgba(255,255,255,0.02); }
        table td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
        table tr:hover td { background: rgba(255,255,255,0.02); }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; }
        .badge-licao { background: #3498db; color: #fff; }
        .badge-artigo { background: #9b59b6; color: #fff; }
        .badge-pub { background: #2ecc71; color: #fff; }
        .badge-rasc { background: #95a5a6; color: #fff; }
        .actions { display: flex; gap: 5px; }
        .actions form { display: inline; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-grid .full { grid-column: 1 / -1; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .top-bar { flex-direction: column; align-items: flex-start; } }
        .tipo-tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .tipo-tab { padding: 10px 20px; border-radius: 6px; background: rgba(255,255,255,0.05); color: #fff; text-decoration: none; font-size: 0.9rem; border: 1px solid transparent; }
        .tipo-tab.active { background: #f39c12; color: #1a1a2e; font-weight: bold; }
    </style>
</head>
<body>

<?php if (!$logado): ?>
    <!-- TELA DE LOGIN -->
    <div class="login-container">
        <div class="login-box">
            <h1>🕉️ Admin</h1>
            <p class="subtitle"><?= APP_NAME ?></p>

            <?php if ($erro_login): ?>
                <div class="erro"><?= esc_html($erro_login) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= \Core\Csrf::field() ?>
                <div class="form-group">
                    <label>Usuário</label>
                    <input type="text" name="usuario" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" required autocomplete="current-password">
                </div>
                <button type="submit" name="login" value="1" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- DASHBOARD / CRUD -->
    <div class="top-bar">
        <h2>🕉️ Painel Admin</h2>
        <div class="nav">
            <a href="?acao=dashboard" class="<?= $acao==='dashboard'?'active':'' ?>">📊 Dashboard</a>
            <a href="?acao=licoes&tipo=licao" class="<?= $acao==='licoes' && $licoes_tipo==='licao'?'active':'' ?>">📚 Lições</a>
            <a href="?acao=licoes&tipo=artigo" class="<?= $acao==='licoes' && $licoes_tipo==='artigo'?'active':'' ?>">📖 Artigos</a>
            <a href="?acao=categorias" class="<?= $acao==='categorias'?'active':'' ?>">📂 Categorias</a>
            <a href="?acao=importar" class="<?= $acao==='importar'?'active':'' ?>">📥 Importar Portal</a>
            <a href="../index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver Site</a>
            <a href="?logout=1"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </div>
    </div>

    <div class="dashboard">

        <?php if ($mensagem): ?>
            <div class="sucesso"><?= esc_html($mensagem) ?></div>
        <?php endif; ?>

        <!-- DASHBOARD -->
        <?php if ($acao === 'dashboard'): ?>
            <div class="cards">
                <div class="card"><div class="icone">👥</div><div class="numero"><?= $stats['usuarios'] ?></div><div class="rotulo">Usuários</div></div>
                <div class="card"><div class="icone">📚</div><div class="numero"><?= $stats['licoes'] ?></div><div class="rotulo">Lições</div></div>
                <div class="card"><div class="icone">📖</div><div class="numero"><?= $stats['artigos'] ?></div><div class="rotulo">Artigos</div></div>
                <div class="card"><div class="icone">📂</div><div class="numero"><?= $stats['categorias'] ?></div><div class="rotulo">Categorias</div></div>
                <div class="card"><div class="icone">✅</div><div class="numero"><?= $stats['progresso'] ?></div><div class="rotulo">Progressos</div></div>
                <div class="card"><div class="icone">📧</div><div class="numero"><?= $stats['newsletter'] ?></div><div class="rotulo">Newsletter</div></div>
                <div class="card"><div class="icone">⭐</div><div class="numero"><?= $stats['favoritos'] ?></div><div class="rotulo">Favoritos</div></div>
                <div class="card"><div class="icone">❓</div><div class="numero"><?= $stats['quiz'] ?></div><div class="rotulo">Quizzes</div></div>
                <div class="card"><div class="icone">💬</div><div class="numero"><?= $stats['discussoes'] ?></div><div class="rotulo">Discussões</div></div>
            </div>

            <h3>📊 Progresso por Categoria (Lições)</h3>
            <?php foreach ($categorias as $cat):
                $pct = $cat['total'] > 0 ? round(($cat['concluidas'] / $cat['total']) * 100) : 0;
            ?>
                <div class="cat-bar">
                    <div class="cat-nome"><?= esc_html($cat['nome']) ?></div>
                    <div class="barra"><div class="fill" style="width: <?= $pct ?>%; background: <?= esc_html($cat['cor']) ?>"></div></div>
                    <div class="pct"><?= $pct ?>%</div>
                </div>
            <?php endforeach; ?>

        <!-- CATEGORIAS -->
        <?php elseif ($acao === 'categorias'): ?>
            <h3>📂 Categorias</h3>

            <table>
                <thead><tr><th>Ordem</th><th>Ícone</th><th>Nome</th><th>Slug</th><th>Cor</th><th>Descrição</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?= $cat['ordem'] ?></td>
                        <td><i class="<?= esc_html($cat['icone']) ?>" style="color:<?= esc_html($cat['cor']) ?>"></i></td>
                        <td><?= esc_html($cat['nome']) ?></td>
                        <td><?= esc_html($cat['slug']) ?></td>
                        <td><span style="color:<?= esc_html($cat['cor']) ?>">████</span> <?= esc_html($cat['cor']) ?></td>
                        <td><?= esc_html($cat['descricao'] ?? '-') ?></td>
                        <td class="actions">
                            <button class="btn btn-sm btn-secondary" onclick="abrirModalCategoria(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES) ?>)">✏️</button>
                            <form method="POST" onsubmit="return confirm('Excluir categoria?')">
                                <?= \Core\Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="submit" name="excluir_categoria" class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3 style="margin-top:40px">➕ Nova Categoria / ✏️ Editar</h3>
            <form method="POST" class="form-grid" id="formCategoria">
                <?= \Core\Csrf::field() ?>
                <input type="hidden" name="id" id="cat_id">
                <div class="form-group"><label>Nome *</label><input type="text" name="nome" id="cat_nome" required></div>
                <div class="form-group"><label>Slug</label><input type="text" name="slug" id="cat_slug" placeholder="auto"></div>
                <div class="form-group"><label>Ícone (Bootstrap Icons)</label><input type="text" name="icone" id="cat_icone" value="bi bi-book"></div>
                <div class="form-group"><label>Cor (Hex)</label><input type="color" name="cor" id="cat_cor" value="#9b59b6"></div>
                <div class="form-group"><label>Ordem</label><input type="number" name="ordem" id="cat_ordem" value="0" min="0"></div>
                <div class="form-group full"><label>Descrição</label><textarea name="descricao" id="cat_descricao" rows="3"></textarea></div>
                <div class="form-group full">
                    <button type="submit" name="salvar_categoria" class="btn btn-primary">💾 Salvar Categoria</button>
                    <button type="button" class="btn btn-secondary" onclick="limparFormCategoria()" style="margin-left:10px">Limpar</button>
                </div>
            </form>

        <!-- LIÇÕES / ARTIGOS -->
        <?php elseif ($acao === 'licoes'): ?>
            <div class="tipo-tabs">
                <a href="?acao=licoes&tipo=licao" class="tipo-tab <?= $licoes_tipo==='licao'?'active':'' ?>">📚 Lições (<?= $stats['licoes'] ?>)</a>
                <a href="?acao=licoes&tipo=artigo" class="tipo-tab <?= $licoes_tipo==='artigo'?'active':'' ?>">📖 Artigos (<?= $stats['artigos'] ?>)</a>
            </div>

            <div style="margin-bottom:15px">
                <a href="?acao=licoes&tipo=<?= $licoes_tipo ?>" class="btn btn-sm btn-secondary" style="width:auto;text-decoration:none">
                    <i class="bi bi-filter"></i> Todas Categorias
                </a>
                <?php foreach ($categorias as $cat): ?>
                    <a href="?acao=licoes&tipo=<?= $licoes_tipo ?>&categoria=<?= $cat['id'] ?>" class="btn btn-sm" style="width:auto;text-decoration:none;background:<?= esc_html($cat['cor']) ?>20;color:<?= esc_html($cat['cor']) ?>;border:1px solid <?= esc_html($cat['cor']) ?>40">
                        <i class="<?= esc_html($cat['icone']) ?>"></i> <?= esc_html($cat['nome']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Tipo</th><th>Título</th><th>Categoria</th><th>Nível</th>
                        <th>Ordem</th><th>Status</th><th>Autor</th><th>Publicado</th><th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($licoes as $l): ?>
                    <tr>
                        <td><?= $l['id'] ?></td>
                        <td><span class="badge badge-<?= $l['tipo'] ?>"><?= $l['tipo'] ?></span></td>
                        <td style="max-width:300px"><?= esc_html($l['titulo']) ?></td>
                        <td><span style="color:<?= esc_html($l['categoria_cor']) ?>"><i class="<?= esc_html($l['categoria_icone'] ?? 'bi bi-folder') ?>"></i> <?= esc_html($l['categoria_nome']) ?></span></td>
                        <td><?= $l['tipo']==='licao' ? ucfirst($l['nivel']) : '-' ?></td>
                        <td><?= $l['ordem'] ?></td>
                        <td><?= $l['publicado_em'] ? '<span class="badge badge-pub">Publicado</span>' : '<span class="badge badge-rasc">Rascunho</span>' ?></td>
                        <td><?= esc_html($l['autor_nome'] ?? '-') ?></td>
                        <td><?= $l['publicado_em'] ? date('d/m/Y', strtotime($l['publicado_em'])) : '-' ?></td>
                        <td class="actions">
                            <button class="btn btn-sm btn-secondary" onclick="abrirModalLicao(<?= htmlspecialchars(json_encode($l), ENT_QUOTES) ?>)">✏️</button>
                            <form method="POST" style="display:inline">
                                <?= \Core\Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <button type="submit" name="toggle_publicar" class="btn btn-sm" style="background:<?= $l['publicado_em'] ? '#e74c3c40' : '#2ecc7140' ?>;color:<?= $l['publicado_em'] ? '#e74c3c' : '#2ecc71' ?>;border:1px solid <?= $l['publicado_em'] ? '#e74c3c' : '#2ecc71' ?>">
                                    <?= $l['publicado_em'] ? '🔒' : '🔓' ?>
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Excluir permanentemente?')" style="display:inline">
                                <?= \Core\Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <button type="submit" name="excluir_licao" class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($licoes)): ?>
                    <tr><td colspan="10" style="text-align:center;opacity:0.5;padding:40px">Nenhum <?= $licoes_tipo === 'licao' ? 'lição' : 'artigo' ?> encontrado</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3 style="margin-top:40px">➕ Novo <?= $licoes_tipo === 'licao' ? 'Lição' : 'Artigo' ?> / ✏️ Editar</h3>
            <form method="POST" class="form-grid" id="formLicao">
                <?= \Core\Csrf::field() ?>
                <input type="hidden" name="id" id="lic_id">
                <input type="hidden" name="tipo" id="lic_tipo" value="<?= esc_html($licoes_tipo) ?>">
                
                <div class="form-group"><label>Categoria *</label>
                    <select name="categoria_id" id="lic_categoria" required>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" style="color:<?= esc_html($cat['cor']) ?>"><i class="<?= esc_html($cat['icone']) ?>"></i> <?= esc_html($cat['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Tipo</label>
                    <select name="tipo" id="lic_tipo_select" disabled>
                        <option value="licao">📚 Lição</option>
                        <option value="artigo">📖 Artigo</option>
                    </select>
                </div>
                <div class="form-group"><label>Título *</label><input type="text" name="titulo" id="lic_titulo" required></div>
                <div class="form-group"><label>Slug</label><input type="text" name="slug" id="lic_slug" placeholder="auto"></div>

                <!-- Campos específicos de Lição -->
                <div class="form-group lic-field"><label>Nível</label>
                    <select name="nivel" id="lic_nivel">
                        <option value="iniciante">Iniciante</option>
                        <option value="intermediario">Intermediário</option>
                        <option value="avancado">Avançado</option>
                    </select>
                </div>
                <div class="form-group lic-field"><label>Duração (min)</label><input type="number" name="duracao_min" id="lic_duracao" value="15" min="1"></div>

                <!-- Campos específicos de Artigo -->
                <div class="form-group art-field"><label>Resumo</label><input type="text" name="resumo" id="lic_resumo" placeholder="Resumo curto para listagens"></div>
                <div class="form-group art-field"><label>Tags (separadas por vírgula)</label><input type="text" name="tags" id="lic_tags" placeholder="gnose, hermetismo, prática"></div>
                <div class="form-group art-field"><label>Imagem (URL)</label><input type="url" name="imagem" id="lic_imagem" placeholder="https://..."></div>
                <div class="form-group art-field"><label>Fonte/Origem</label><input type="text" name="fonte" id="lic_fonte" placeholder="Portal Saberes Ancestrais (importado)"></div>
                <div class="form-group art-field"><label>Autor (admin)</label>
                    <select name="autor_id" id="lic_autor">
                        <option value="">-- Nenhum --</option>
                        <?php foreach ($admins as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= esc_html($a['usuario']) ?> (<?= esc_html($a['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group art-field"><label>Data Publicação</label><input type="datetime-local" name="publicado_em" id="lic_publicado"></div>

                <div class="form-group full"><label>Conteúdo (HTML permitido: h2-h6, p, ul, ol, li, blockquote, strong, em, a, img)</label>
                    <textarea name="conteudo" id="lic_conteudo" rows="15"></textarea>
                </div>
                <div class="form-group full"><label>Ordem</label><input type="number" name="ordem" id="lic_ordem" value="0" min="0"></div>
                <div class="form-group full">
                    <button type="submit" name="salvar_licao" class="btn btn-primary">💾 Salvar</button>
                    <button type="button" class="btn btn-secondary" onclick="limparFormLicao()" style="margin-left:10px">Limpar</button>
                </div>
            </form>

        <!-- IMPORTAR PORTAL -->
        <?php elseif ($acao === 'importar'): ?>
            <h3>📥 Importar Artigos do Portal Saberes</h3>
            <p style="opacity:0.7;margin-bottom:20px">Este processo conecta no banco <code>portal_saberes</code> e importa todos os artigos com <strong>status = 'publicado'</strong> como <code>tipo='artigo'</code> no Caminho.</p>
            <p style="opacity:0.5;font-size:0.9rem">⚠ Requer que o MySQL esteja rodando e o banco <code>portal_saberes</code> exista.</p>

            <form method="POST" style="max-width:400px">
                <?= \Core\Csrf::field() ?>
                <button type="submit" name="importar_portal" class="btn btn-primary">🚀 Executar Importação</button>
            </form>

            <h4 style="margin-top:30px">O que será importado:</h4>
            <ul style="opacity:0.8;line-height:2">
                <li>Título, slug, resumo, conteúdo completo (HTML)</li>
                <li>Tags, imagem, fonte (origin: "Portal Saberes Ancestrais")</li>
                <li>Categoria mapeada por slug/nome (fallback: primeira categoria)</li>
                <li>Autor → admin padrão do Caminho</li>
                <li>Data de publicação preservada</li>
                <li>Nível = 'avancado', ordem = 999 (ficam no final da categoria)</li>
                <li>Duração estimada baseada no tamanho do texto</li>
            </ul>
            <p style="margin-top:15px;opacity:0.6">Artigos com mesmo slug já existentes serão pulados.</p>
        <?php endif; ?>
    </div>

    <!-- MODAL CATEGORIA (simples, via JS) -->
    <script>
    function abrirModalCategoria(cat) {
        document.getElementById('cat_id').value = cat.id || '';
        document.getElementById('cat_nome').value = cat.nome || '';
        document.getElementById('cat_slug').value = cat.slug || '';
        document.getElementById('cat_icone').value = cat.icone || 'bi bi-book';
        document.getElementById('cat_cor').value = cat.cor || '#9b59b6';
        document.getElementById('cat_ordem').value = cat.ordem || 0;
        document.getElementById('cat_descricao').value = cat.descricao || '';
        window.scrollTo({top: document.querySelector('form#formCategoria').offsetTop - 100, behavior: 'smooth'});
    }
    function limparFormCategoria() {
        ['cat_id','cat_nome','cat_slug','cat_icone','cat_cor','cat_ordem','cat_descricao'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = id === 'cat_icone' ? 'bi bi-book' : (id === 'cat_cor' ? '#9b59b6' : (id === 'cat_ordem' ? '0' : ''));
        });
    }

    function abrirModalLicao(l) {
        document.getElementById('lic_id').value = l.id || '';
        document.getElementById('lic_tipo').value = l.tipo || 'licao';
        document.getElementById('lic_tipo_select').value = l.tipo || 'licao';
        document.getElementById('lic_categoria').value = l.categoria_id || '';
        document.getElementById('lic_titulo').value = l.titulo || '';
        document.getElementById('lic_slug').value = l.slug || '';
        document.getElementById('lic_nivel').value = l.nivel || 'iniciante';
        document.getElementById('lic_duracao').value = l.duracao_min || 15;
        document.getElementById('lic_resumo').value = l.resumo || '';
        document.getElementById('lic_tags').value = l.tags || '';
        document.getElementById('lic_imagem').value = l.imagem || '';
        document.getElementById('lic_fonte').value = l.fonte || '';
        document.getElementById('lic_autor').value = l.autor_id || '';
        document.getElementById('lic_publicado').value = l.publicado_em ? l.publicado_em.slice(0,16) : '';
        document.getElementById('lic_conteudo').value = l.conteudo || '';
        document.getElementById('lic_ordem').value = l.ordem || 0;
        
        // Mostrar/ocultar campos por tipo
        toggleCamposTipo(l.tipo || 'licao');
        window.scrollTo({top: document.querySelector('form#formLicao').offsetTop - 100, behavior: 'smooth'});
    }
    function limparFormLicao() {
        ['lic_id','lic_titulo','lic_slug','lic_nivel','lic_duracao','lic_resumo','lic_tags','lic_imagem','lic_fonte','lic_autor','lic_publicado','lic_conteudo','lic_ordem'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = id === 'lic_nivel' ? 'iniciante' : (id === 'lic_duracao' ? '15' : (id === 'lic_ordem' ? '0' : ''));
        });
        document.getElementById('lic_categoria').selectedIndex = 0;
        toggleCamposTipo('licao');
    }
    function toggleCamposTipo(tipo) {
        document.querySelectorAll('.lic-field').forEach(el => el.style.display = tipo === 'licao' ? 'block' : 'none');
        document.querySelectorAll('.art-field').forEach(el => el.style.display = tipo === 'artigo' ? 'block' : 'none');
        document.getElementById('lic_tipo_select').value = tipo;
    }
    // Inicial
    document.addEventListener('DOMContentLoaded', () => {
        const tipo = '<?= esc_html($licoes_tipo) ?>';
        toggleCamposTipo(tipo);
    });
    </script>
<?php endif; ?>

</body>
</html>
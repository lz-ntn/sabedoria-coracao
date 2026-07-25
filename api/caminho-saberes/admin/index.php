<?php
/**
 * Admin - Painel de Controle
 * 
 * Acesso: http://localhost/caminho-saberes/admin/
 * Usuário: admin
 * Senha: admin123
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();
$erro_login = '';
$logado = false;

// ══════════════════════════════════════════
// Login com CSRF + Rate Limiting
// ══════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Valida token CSRF
    \Core\Csrf::validateOrFail();

    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Rate limiting por IP
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

            // Previne session fixation: regenera ID da sessão
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

// Verificar se já está logado
if (!empty($_SESSION['admin_logado'])) {
    $logado = true;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirecionar('index.php');
}

// ============================================
// DADOS DO DASHBOARD
// ============================================
if ($logado) {
    $stats = [];

    // Totais
    $stats['usuarios'] = $db->fetch('SELECT COUNT(*) as total FROM usuarios')['total'];
    $stats['licoes'] = $db->fetch('SELECT COUNT(*) as total FROM licoes')['total'];
    $stats['categorias'] = $db->fetch('SELECT COUNT(*) as total FROM categorias')['total'];
    $stats['progresso'] = $db->fetch('SELECT COUNT(*) as total FROM progresso')['total'];
    $stats['newsletter'] = $db->fetch('SELECT COUNT(*) as total FROM newsletter WHERE ativo = 1')['total'];
    $stats['favoritos'] = $db->fetch('SELECT COUNT(*) as total FROM favoritos')['total'];
    $stats['quiz'] = $db->fetch('SELECT COUNT(*) as total FROM quiz_resultados')['total'];

    // Progresso por categoria
    $categorias = $db->select(
        'SELECT c.nome, c.slug, c.cor,
                COUNT(DISTINCT p.id) as concluidas,
                (SELECT COUNT(*) FROM licoes WHERE categoria_id = c.id) as total
         FROM categorias c
         LEFT JOIN licoes l ON l.categoria_id = c.id
         LEFT JOIN progresso p ON p.licao_id = l.id
         GROUP BY c.id, c.nome, c.slug, c.cor
         ORDER BY c.ordem'
    );
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            color: #fff;
            font-size: 1rem;
            transition: border 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #f39c12;
        }
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-primary {
            background: linear-gradient(90deg, #e2b714, #f39c12);
            color: #1a1a2e;
        }
        .btn-primary:hover { transform: translateY(-2px); }
        .erro {
            background: rgba(231,76,60,0.2);
            color: #e74c3c;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }
        /* Dashboard */
        .top-bar {
            background: rgba(255,255,255,0.03);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .top-bar h2 { font-size: 1.2rem; }
        .top-bar a { color: #f39c12; text-decoration: none; font-size: 0.9rem; }
        .dashboard { padding: 30px; max-width: 1200px; margin: 0 auto; }
        .dashboard h3 { margin-bottom: 20px; font-size: 1.3rem; color: #f39c12; }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .card {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.08);
            text-align: center;
        }
        .card .numero {
            font-size: 2.5rem;
            font-weight: bold;
            color: #f39c12;
            margin-bottom: 5px;
        }
        .card .rotulo {
            font-size: 0.85rem;
            opacity: 0.6;
        }
        .card .icone { font-size: 1.5rem; margin-bottom: 10px; }
        .cat-bar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .cat-bar .cat-nome { min-width: 120px; font-size: 0.9rem; }
        .cat-bar .barra {
            flex: 1;
            height: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            overflow: hidden;
        }
        .cat-bar .fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        .cat-bar .pct { min-width: 50px; text-align: right; font-size: 0.85rem; opacity: 0.7; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 0.85rem;
            opacity: 0.6;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 0.9rem;
        }
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
    <!-- DASHBOARD -->
    <div class="top-bar">
        <h2>🕉️ Painel Admin</h2>
        <div>
            <a href="../index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver Site</a>
            &nbsp;&nbsp;
            <a href="?logout=1"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </div>
    </div>

    <div class="dashboard">
        <div class="cards">
            <div class="card">
                <div class="icone">👥</div>
                <div class="numero"><?= $stats['usuarios'] ?></div>
                <div class="rotulo">Usuários</div>
            </div>
            <div class="card">
                <div class="icone">📚</div>
                <div class="numero"><?= $stats['licoes'] ?></div>
                <div class="rotulo">Lições</div>
            </div>
            <div class="card">
                <div class="icone">📂</div>
                <div class="numero"><?= $stats['categorias'] ?></div>
                <div class="rotulo">Categorias</div>
            </div>
            <div class="card">
                <div class="icone">✅</div>
                <div class="numero"><?= $stats['progresso'] ?></div>
                <div class="rotulo">Progressos</div>
            </div>
            <div class="card">
                <div class="icone">📧</div>
                <div class="numero"><?= $stats['newsletter'] ?></div>
                <div class="rotulo">Newsletter</div>
            </div>
            <div class="card">
                <div class="icone">⭐</div>
                <div class="numero"><?= $stats['favoritos'] ?></div>
                <div class="rotulo">Favoritos</div>
            </div>
            <div class="card">
                <div class="icone">❓</div>
                <div class="numero"><?= $stats['quiz'] ?></div>
                <div class="rotulo">Quizzes</div>
            </div>
        </div>

        <h3>📊 Progresso por Categoria</h3>
        <?php foreach ($categorias as $cat): 
            $pct = $cat['total'] > 0 ? round(($cat['concluidas'] / $cat['total']) * 100) : 0;
        ?>
            <div class="cat-bar">
                <div class="cat-nome"><?= esc_html($cat['nome']) ?></div>
                <div class="barra">
                    <div class="fill" style="width: <?= $pct ?>%; background: <?= esc_html($cat['cor']) ?>"></div>
                </div>
                <div class="pct"><?= $pct ?>%</div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>

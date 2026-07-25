<?php
/**
 * Página Principal - O Caminho Saberes Ancestrais
 * 
 * Single Page Application (SPA)
 * Todo o conteúdo é carregado via API (PHP + MySQL)
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();

// Carregar categorias e lições do banco
$categorias = $db->select(
    'SELECT * FROM categorias ORDER BY ordem'
);

$licoes = $db->select(
    'SELECT l.*, c.nome as categoria_nome, c.slug as categoria_slug, c.cor, c.icone
     FROM licoes l
     JOIN categorias c ON c.id = l.categoria_id
     ORDER BY c.ordem, l.ordem'
);

// Organizar lições por categoria
$licoes_por_categoria = [];
foreach ($licoes as $l) {
    $licoes_por_categoria[$l['categoria_slug']][] = $l;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= APP_NAME ?></title>
    <meta name="description" content="<?= APP_DESC ?>" />
    <meta name="keywords" content="gnose, hermetismo, epigenética, kundalini, teosofia, espiritualidade, sabedoria ancestral" />
    <meta name="author" content="O Caminho" />
    <meta name="robots" content="index, follow" />
    <!-- Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= APP_URL ?>" />
    <meta property="og:title" content="<?= APP_NAME ?>" />
    <meta property="og:description" content="<?= APP_DESC ?>" />
    <meta property="og:image" content="<?= APP_URL ?>/assets/og-image.jpg" />
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="<?= APP_URL ?>" />
    <meta property="twitter:title" content="<?= APP_NAME ?>" />
    <meta property="twitter:description" content="<?= APP_DESC ?>" />
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🕉️</text></svg>" />
    <link rel="canonical" href="<?= APP_URL ?>" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/estilo.css?v=<?= APP_VERSION ?>" />
</head>
<body>
    <!-- ============================================
         HEADER
         ============================================ -->
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="bi bi-universal-access logo-icon"></i>
                <div>
                    <h1>O Caminho</h1>
                    <span class="logo-subtitle">Saberes Ancestrais</span>
                </div>
            </div>
            <div class="controles-tema">
                <button type="button" class="btn-tema" id="search-toggle" aria-label="Buscar" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>
                <button type="button" class="btn-tema" id="favorites-toggle" aria-label="Favoritos" title="Favoritos">
                    <i class="bi bi-bookmark"></i>
                </button>
                <button type="button" class="btn-tema" id="theme-toggle" aria-label="Alternar tema" title="Alternar tema">
                    <i class="bi bi-moon-stars" id="theme-icon"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item">
                <a href="#inicio"><i class="bi bi-house"></i> Início</a>
            </li>
            <li class="breadcrumb-item" id="breadcrumb-current">Conhecimento</li>
        </ol>
    </nav>

    <!-- ============================================
         MODAIS
         ============================================ -->

    <!-- Busca -->
    <div id="search-modal" class="modal" role="dialog" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="bi bi-search"></i> Buscar no Site</h3>
                <button type="button" class="modal-close" aria-label="Fechar">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" id="search-input" class="search-input"
                       placeholder="Digite para buscar..." aria-label="Campo de busca" />
                <div id="search-results" class="search-results"></div>
            </div>
        </div>
    </div>

    <!-- Favoritos -->
    <div id="favorites-panel" class="favorites-panel" role="dialog" aria-hidden="true">
        <div class="panel-content">
            <div class="panel-header">
                <h3><i class="bi bi-bookmark-heart"></i> Meus Favoritos</h3>
                <button type="button" class="panel-close" aria-label="Fechar">&times;</button>
            </div>
            <div class="panel-body">
                <div id="favorites-list" class="favorites-list">
                    <p class="empty-message">Nenhum favorito salvo ainda.</p>
                </div>
                <button type="button" id="export-favorites" class="btn btn-secondary" style="margin-top:15px">
                    <i class="bi bi-download"></i> Exportar Favoritos
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================
         NAVEGAÇÃO
         ============================================ -->
    <nav id="main-nav">
        <div class="nav-container">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#inicio" class="nav-link active" data-section="inicio">
                        <i class="bi bi-house"></i> <span>Início</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#cronograma" class="nav-link" data-section="cronograma">
                        <i class="bi bi-calendar3"></i> <span>Cronograma</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#conhecimento" class="nav-link" data-section="conhecimento">
                        <i class="bi bi-book"></i> <span>Conhecimento</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#praticas" class="nav-link" data-section="praticas">
                        <i class="bi bi-person-bounding-box"></i> <span>Práticas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#meditacao" class="nav-link" data-section="meditacao">
                        <i class="bi bi-snow"></i> <span>Meditação</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#jornada" class="nav-link" data-section="jornada">
                        <i class="bi bi-stars"></i> <span>Jornada</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#quiz" class="nav-link" data-section="quiz">
                        <i class="bi bi-question-circle"></i> <span>Quiz</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#newsletter" class="nav-link" data-section="newsletter">
                        <i class="bi bi-envelope"></i> <span>Newsletter</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- ============================================
         CONTEÚDO PRINCIPAL
         ============================================ -->
    <main>
        <!-- Introdução -->
        <section id="inicio" class="introducao animacao-entrada">
            <h1>O Caminho do Conhecimento</h1>
            <p>Uma jornada através dos saberes que unem ciência, espiritualidade e sabedoria prática.</p>
            <div class="destaque-texto">
                <p style="font-style:italic;font-size:1.2rem;margin-top:15px">
                    "O conhecimento sem prática é vazio. A prática sem conhecimento é cega."
                </p>
                <span style="font-size:0.9rem;opacity:0.7">— Tradição Hermética</span>
            </div>
            <a href="#conhecimento" class="btn btn-primary" style="margin-top:20px">
                <i class="bi bi-arrow-down-circle"></i> Começar a Jornada
            </a>
        </section>

        <!-- Cronograma -->
        <section id="cronograma">
            <h2><i class="bi bi-calendar3"></i> Cronograma de Estudos</h2>
            <p class="section-subtitle">Um caminho estruturado para sua jornada de conhecimento</p>
            <div class="tabs-container">
                <div class="tabs-buttons">
                    <button type="button" class="tab-btn active" data-tab-target="iniciante">
                        <i class="bi bi-star"></i> Iniciante
                    </button>
                    <button type="button" class="tab-btn" data-tab-target="intermediario">
                        <i class="bi bi-star-half"></i> Intermediário
                    </button>
                    <button type="button" class="tab-btn" data-tab-target="avancado">
                        <i class="bi bi-star-fill"></i> Avançado
                    </button>
                </div>
                <div class="tab-content" data-tab="iniciante">
                    <div class="study-path">
                        <?php
                        $iniciante = array_filter($licoes, fn($l) => $l['nivel'] === 'iniciante');
                        foreach (array_slice($iniciante, 0, 6) as $i => $l):
                            $semanas = 'Semana ' . ($i * 2 + 1) . '-' . ($i * 2 + 2);
                        ?>
                        <div class="path-card">
                            <div class="path-week"><?= $semanas ?></div>
                            <h4><?= esc_html($l['titulo']) ?></h4>
                            <p class="path-cat"><?= esc_html($l['categoria_nome']) ?></p>
                            <p class="path-time"><i class="bi bi-clock"></i> <?= $l['duracao_min'] ?> min/dia</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="tab-content" data-tab="intermediario">
                    <div class="study-path">
                        <?php
                        $intermediario = array_filter($licoes, fn($l) => $l['nivel'] === 'intermediario');
                        foreach (array_slice($intermediario, 0, 6) as $i => $l):
                            $semanas = 'Semana ' . ($i * 2 + 1) . '-' . ($i * 2 + 2);
                        ?>
                        <div class="path-card">
                            <div class="path-week"><?= $semanas ?></div>
                            <h4><?= esc_html($l['titulo']) ?></h4>
                            <p class="path-cat"><?= esc_html($l['categoria_nome']) ?></p>
                            <p class="path-time"><i class="bi bi-clock"></i> <?= $l['duracao_min'] ?> min/dia</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="tab-content" data-tab="avancado">
                    <div class="study-path">
                        <?php
                        $avancado = array_filter($licoes, fn($l) => $l['nivel'] === 'avancado');
                        foreach (array_slice($avancado, 0, 6) as $i => $l):
                            $semanas = 'Semana ' . ($i * 2 + 1) . '-' . ($i * 2 + 2);
                        ?>
                        <div class="path-card">
                            <div class="path-week"><?= $semanas ?></div>
                            <h4><?= esc_html($l['titulo']) ?></h4>
                            <p class="path-cat"><?= esc_html($l['categoria_nome']) ?></p>
                            <p class="path-time"><i class="bi bi-clock"></i> <?= $l['duracao_min'] ?> min/dia</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Conhecimento -->
        <section id="conhecimento">
            <h2><i class="bi bi-book"></i> Tradições de Conhecimento</h2>
            <p class="section-subtitle">Explore saberes ancestrais e científicos que transformam a consciência</p>

            <!-- Barras de Progresso -->
            <div class="progress-categories">
                <?php foreach ($categorias as $cat): ?>
                <div class="progress-item" data-category="<?= esc_html($cat['slug']) ?>">
                    <span class="progress-label"><?= esc_html($cat['nome']) ?></span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:0%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Abas de Categorias -->
            <div class="tabs-container">
                <div class="tabs-buttons">
                    <?php foreach ($categorias as $i => $cat): ?>
                    <button type="button" class="tab-btn <?= $i === 0 ? 'active' : '' ?>"
                            data-tab-target="<?= esc_html($cat['slug']) ?>">
                        <i class="<?= esc_html($cat['icone']) ?>"></i>
                        <span><?= esc_html($cat['nome']) ?></span>
                        <span class="tab-status" id="status-<?= esc_html($cat['slug']) ?>">0%</span>
                    </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($categorias as $i => $cat): ?>
                <div class="tab-content <?= $i === 0 ? 'active' : '' ?>"
                     data-tab="<?= esc_html($cat['slug']) ?>">
                    <div class="tab-intro">
                        <p><?= esc_html($cat['descricao']) ?></p>
                    </div>
                    <div class="accordion">
                        <?php if (isset($licoes_por_categoria[$cat['slug']])): ?>
                            <?php foreach ($licoes_por_categoria[$cat['slug']] as $j => $l): ?>
                            <div class="accordion-item <?= $j === 0 ? 'active' : '' ?>"
                                 data-lesson="<?= esc_html($l['id']) ?>"
                                 data-category="<?= esc_html($cat['slug']) ?>">
                                <div class="accordion-header">
                                    <div class="lesson-indicator">
                                        <i class="bi <?= $j === 0 ? 'bi-check-circle' : 'bi-circle' ?>"></i>
                                    </div>
                                    <h3><?= esc_html($l['titulo']) ?></h3>
                                    <span class="accordion-badge"><?= $l['duracao_min'] ?> min</span>
                                    <span class="accordion-icon">+</span>
                                </div>
                                <div class="accordion-content">
                                    <div class="lesson-body"><?= render_conteudo($l['conteudo']) ?></div>
                                    <div class="lesson-meta">
                                        <span class="meta-item">
                                            <i class="bi bi-clock"></i> <?= $l['duracao_min'] ?> minutos
                                        </span>
                                        <span class="meta-item">
                                            <i class="bi bi-<?= $l['nivel'] === 'iniciante' ? 'star' : ($l['nivel'] === 'intermediario' ? 'star-half' : 'star-fill') ?>"></i>
                                            <?= ucfirst($l['nivel']) ?>
                                        </span>
                                    </div>
                                    <button type="button" class="btn-marcar"
                                            data-lesson="<?= esc_html($l['id']) ?>"
                                            data-categoria="<?= esc_html($cat['slug']) ?>">
                                        <i class="bi bi-check2-square"></i> Marcar como estudado
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Práticas -->
        <section id="praticas">
            <h2><i class="bi bi-person-bounding-box"></i> Práticas Diárias</h2>
            <p class="section-subtitle">O caminho prático da transformação</p>
            <div class="praticas-grid" id="praticas-grid">
                <div class="pratica-card">
                    <span class="pratica-icon">🧘</span>
                    <h4>Autoobservação</h4>
                    <p>Observe seus pensamentos sem julgamento por 10 minutos</p>
                    <span class="pratica-duracao">10 min</span>
                </div>
                <div class="pratica-card">
                    <span class="pratica-icon">🌬️</span>
                    <h4>Respiração Quadrada</h4>
                    <p>Inspire 4s, segure 4s, expire 4s, segure 4s</p>
                    <span class="pratica-duracao">5 min</span>
                </div>
                <div class="pratica-card">
                    <span class="pratica-icon">💓</span>
                    <h4>Coerência Cardíaca</h4>
                    <p>Respire com foco no coração, 6 respirações por minuto</p>
                    <span class="pratica-duracao">5 min</span>
                </div>
                <div class="pratica-card">
                    <span class="pratica-icon">🔆</span>
                    <h4>Meditação dos Chakras</h4>
                    <p>Visualize cada chakra se abrindo ao longo da coluna</p>
                    <span class="pratica-duracao">15 min</span>
                </div>
                <div class="pratica-card">
                    <span class="pratica-icon">📖</span>
                    <h4>Estudo Diário</h4>
                    <p>Leia uma lição e reflita sobre sua aplicação prática</p>
                    <span class="pratica-duracao">20 min</span>
                </div>
                <div class="pratica-card">
                    <span class="pratica-icon">🙏</span>
                    <h4>Gratidão</h4>
                    <p>Escreva 3 coisas pelas quais é grato hoje</p>
                    <span class="pratica-duracao">5 min</span>
                </div>
            </div>
        </section>

        <!-- Meditação -->
        <section id="meditacao">
            <h2><i class="bi bi-snow"></i> Meditação Guiada</h2>
            <p class="section-subtitle">Técnicas de meditação e respiração para o autoconhecimento</p>
            <div class="meditacao-conteudo">
                <div class="meditacao-texto">
                    <h3>Meditação da Autoobservação</h3>
                    <ol>
                        <li><strong>Sente-se</strong> confortavelmente com a coluna ereta</li>
                        <li><strong>Feche os olhos</strong> e respire profundamente 3 vezes</li>
                        <li><strong>Observe</strong> seus pensamentos sem julgamento</li>
                        <li><strong>Identifique</strong> quem é o observador interno</li>
                        <li><strong>Permaneça</strong> por 10 minutos na testemunha silenciosa</li>
                    </ol>
                    <div class="destaque-texto" style="margin-top:20px">
                        "A mente é como um céu. Os pensamentos são nuvens. Você é o céu, não as nuvens."
                    </div>
                </div>
                <div class="meditacao-timer" id="meditacao-timer">
                    <div class="timer-display" id="timer-display">10:00</div>
                    <div class="timer-controls">
                        <button type="button" id="timer-start" class="btn btn-primary">
                            <i class="bi bi-play-fill"></i> Iniciar
                        </button>
                        <button type="button" id="timer-reset" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Resetar
                        </button>
                    </div>
                    <div class="timer-presets">
                        <button type="button" class="timer-preset active" data-minutes="10">10 min</button>
                        <button type="button" class="timer-preset" data-minutes="15">15 min</button>
                        <button type="button" class="timer-preset" data-minutes="20">20 min</button>
                        <button type="button" class="timer-preset" data-minutes="30">30 min</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Jornada -->
        <section id="jornada">
            <h2><i class="bi bi-stars"></i> A Jornada da Consciência</h2>
            <p class="section-subtitle">Três visões sobre a natureza da consciência humana</p>
            <div class="jornada-grid">
                <div class="jornada-card">
                    <div class="jornada-numero">1</div>
                    <h3>Visão Materialista</h3>
                    <p>A consciência é um subproduto da atividade cerebral. Quando o cérebro morre, a consciência se extingue.</p>
                    <span class="jornada-ref">"A mente é o que o cérebro faz."</span>
                </div>
                <div class="jornada-card destaque">
                    <div class="jornada-numero">2</div>
                    <h3>Visão Não-Dual</h3>
                    <p>A consciência é a realidade fundamental. O mundo material é uma manifestação da consciência una.</p>
                    <span class="jornada-ref">"Tudo é Um. O Um é Tudo."</span>
                </div>
                <div class="jornada-card">
                    <div class="jornada-numero">3</div>
                    <h3>Visão Experiencial</h3>
                    <p>A verdade não está em teorias, mas na experiência direta. O caminho é a prática, não a crença.</p>
                    <span class="jornada-ref">"Conhece-te a ti mesmo."</span>
                </div>
            </div>
        </section>

        <!-- Quiz -->
        <section id="quiz">
            <h2><i class="bi bi-question-circle"></i> Teste seu Conhecimento</h2>
            <p class="section-subtitle">Quiz completo com perguntas sobre todas as tradições</p>
            <div id="quiz-container"></div>
        </section>

        <!-- Newsletter -->
        <section id="newsletter">
            <h2><i class="bi bi-envelope"></i> Newsletter</h2>
            <p class="section-subtitle">Receba conteúdos exclusivos sobre saberes ancestrais</p>
            <form id="newsletter-form" class="newsletter-form">
                <div class="form-group">
                    <input type="email" id="newsletter-email"
                           placeholder="Seu melhor e-mail" required />
                    <button type="submit" class="btn btn-primary">Inscrever-se</button>
                </div>
                <p class="form-note">Sem spam. Cancele quando quiser.</p>
            </form>
            <div id="newsletter-message" class="newsletter-message"></div>
        </section>
    </main>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>🕉️ O Caminho</h4>
                <p>Saberes Ancestrais — uma jornada de autoconhecimento</p>
            </div>
            <div class="footer-section">
                <h4>Navegação</h4>
                <ul>
                    <li><a href="#inicio">Início</a></li>
                    <li><a href="#conhecimento">Conhecimento</a></li>
                    <li><a href="#praticas">Práticas</a></li>
                    <li><a href="#quiz">Quiz</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Filosofia</h4>
                <p>"O conhecimento sem prática é vazio. A prática sem conhecimento é cega."</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> O Caminho — Saberes Ancestrais. v<?= APP_VERSION ?></p>
        </div>
    </footer>

    <!-- Botão voltar ao topo -->
    <button type="button" id="back-to-top" class="back-to-top" aria-label="Voltar ao topo">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- ============================================
         SCRIPTS
         ============================================ -->
    <script>
    // Dados PHP disponíveis para o JS
    window.APP_DATA = {
        categorias: <?= json_encode($categorias, JSON_UNESCAPED_UNICODE) ?>,
        licoes: <?= json_encode($licoes, JSON_UNESCAPED_UNICODE) ?>,
        url_api: '<?= APP_URL ?>/api',
        csrf_token: '<?= \Core\Csrf::token() ?>'
    };
    </script>
    <script src="assets/js/api.js?v=<?= APP_VERSION ?>"></script>
    <script src="assets/js/app.js?v=<?= APP_VERSION ?>"></script>
</body>
</html>

-- =============================================
-- Banco: caminho_saberes
-- Projeto: O Caminho - Saberes Ancestrais
-- Versão: 2.0 (PHP + MySQL)
-- =============================================

CREATE DATABASE IF NOT EXISTS `caminho_saberes`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `caminho_saberes`;

-- =============================================
-- CATEGORIAS (ex: Gnose, Epigenética, Hermetismo)
-- =============================================
CREATE TABLE IF NOT EXISTS `categorias` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `nome`       VARCHAR(50)   NOT NULL,
  `slug`       VARCHAR(50)   NOT NULL,
  `descricao`  VARCHAR(200)  DEFAULT NULL,
  `icone`      VARCHAR(30)   DEFAULT 'bi bi-book',
  `cor`        VARCHAR(7)    DEFAULT '#9b59b6',
  `ordem`      INT           DEFAULT 0,
  `criada_em`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categoria_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- LICOES (cada lição dentro das categorias)
-- =============================================
CREATE TABLE IF NOT EXISTS `licoes` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `categoria_id` INT           NOT NULL,
  `titulo`       VARCHAR(200)  NOT NULL,
  `slug`         VARCHAR(200)  NOT NULL,
  `conteudo`     TEXT          DEFAULT NULL,
  `nivel`        ENUM('iniciante','intermediario','avancado') DEFAULT 'iniciante',
  `duracao_min`  INT           DEFAULT 15,
  `ordem`        INT           DEFAULT 0,
  `criada_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_licao_slug` (`slug`),
  KEY `fk_licao_categoria` (`categoria_id`),
  CONSTRAINT `fk_licao_categoria` FOREIGN KEY (`categoria_id`)
    REFERENCES `categorias`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- USUARIOS (identificados por dispositivo)
-- =============================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `uuid`         VARCHAR(36)   NOT NULL COMMENT 'Identificador único do dispositivo',
  `nome`         VARCHAR(100)  DEFAULT NULL,
  `email`        VARCHAR(255)  DEFAULT NULL,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acesso` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- PROGRESSO (lições concluídas por usuário)
-- =============================================
CREATE TABLE IF NOT EXISTS `progresso` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT           NOT NULL,
  `licao_id`     INT           NOT NULL,
  `concluida`    TINYINT(1)    DEFAULT 1,
  `concluida_em` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_progresso_usuario_licao` (`usuario_id`, `licao_id`),
  KEY `fk_progresso_licao` (`licao_id`),
  CONSTRAINT `fk_progresso_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_progresso_licao` FOREIGN KEY (`licao_id`)
    REFERENCES `licoes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- NEWSLETTER (assinantes)
-- =============================================
CREATE TABLE IF NOT EXISTS `newsletter` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(255)  NOT NULL,
  `ativo`        TINYINT(1)    DEFAULT 1,
  `inscrito_em`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- FAVORITOS (lições favoritadas por usuário)
-- =============================================
CREATE TABLE IF NOT EXISTS `favoritos` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT           NOT NULL,
  `licao_id`     INT           NOT NULL,
  `adicionado_em` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_favorito_usuario_licao` (`usuario_id`, `licao_id`),
  KEY `fk_favorito_licao` (`licao_id`),
  CONSTRAINT `fk_favorito_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorito_licao` FOREIGN KEY (`licao_id`)
    REFERENCES `licoes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- QUIZ_RESULTADOS (histórico de quizzes)
-- =============================================
CREATE TABLE IF NOT EXISTS `quiz_resultados` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT           NOT NULL,
  `categoria`    VARCHAR(50)   DEFAULT NULL COMMENT 'categoria do quiz ou null para geral',
  `acertos`      INT           DEFAULT 0,
  `total`        INT           DEFAULT 0,
  `pontuacao`    INT           DEFAULT 0,
  `respostas`    JSON          DEFAULT NULL COMMENT 'Detalhes das respostas em JSON',
  `realizado_em` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_quiz_usuario` (`usuario_id`),
  CONSTRAINT `fk_quiz_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- LOGIN_ATTEMPTS (rate limiting)
-- =============================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `identifier`   VARCHAR(255)  NOT NULL COMMENT 'IP ou email',
  `ip`           VARCHAR(45)   DEFAULT NULL,
  `attempted_at` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_identifier` (`identifier`),
  KEY `idx_login_attempted` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- ADMIN (controle de acesso simples)
-- =============================================
CREATE TABLE IF NOT EXISTS `admin` (
  `id`       INT           NOT NULL AUTO_INCREMENT,
  `usuario`  VARCHAR(50)   NOT NULL,
  `senha`    VARCHAR(255)  NOT NULL COMMENT 'Hash bcrypt',
  `email`    VARCHAR(255)  DEFAULT NULL,
  `criado_em` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DADOS INICIAIS (seeds)
-- =============================================

-- Admin padrão (senha: admin123)
INSERT INTO `admin` (`usuario`, `senha`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@saberes.com')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Categorias
INSERT INTO `categorias` (`nome`, `slug`, `descricao`, `icone`, `cor`, `ordem`) VALUES
('Gnose',         'gnose',        'Conhecimento direto e experiencial da natureza divina',          'bi bi-fire',            '#9b59b6', 1),
('Epigenética',   'epigenetica',  'A ciência que prova: você não é vítima dos seus genes',         'bi bi-dna',             '#2ecc71', 2),
('Hermetismo',    'hermetismo',   'Filosofia que une ciência, misticismo e sabedoria prática',      'bi bi-gem',             '#f39c12', 3),
('Kundalini',     'kundalini',    'A energia serpentina que ascende pela coluna vertebral',         'bi bi-lightning-charge','#e74c3c', 4),
('Teosofia',      'teosofia',     'Sabedoria Divina — síntese de todas as tradições',               'bi bi-eye',             '#3498db', 5),
('Coração',       'coracao',      'O coração como centro de inteligência e consciência',           'bi bi-heart-pulse',     '#e91e63', 6)
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

-- Lições
INSERT INTO `licoes` (`categoria_id`, `titulo`, `slug`, `conteudo`, `nivel`, `duracao_min`, `ordem`) VALUES
-- Gnose (categoria_id = 1)
(1, 'O Que é Gnose?',                'gnose-o-que-e',         'Conhecimento direto e experiencial da natureza divina — não crença, mas experiência.', 'iniciante', 15, 1),
(1, 'Os Três Mundos',               'gnose-tres-mundos',     'Plerooma, Kenoma e Sarak — os mundos da tradição gnóstica.',                            'intermediario', 20, 2),
(1, 'Textos Gnósticos',             'gnose-textos',          'Biblioteca de Nag Hammadi e os evangelhos gnósticos.',                                   'intermediario', 30, 3),
(1, 'Prática: Autoobservação',       'gnose-pratica',         'Exercício gnóstico de observação interna sem julgamento.',                               'iniciante', 10, 4),
-- Epigenética (categoria_id = 2)
(2, 'O Que é Epigenética?',          'epigenetica-o-que-e',   'Estudo das mudanças na atividade dos genes que não alteram a sequência do DNA.',         'iniciante', 20, 1),
(2, 'Os 3 Mecanismos',              'epigenetica-mecanismos', 'Metilação do DNA, modificação de histonas e microRNA.',                                   'intermediario', 25, 2),
(2, 'O Que Influencia?',            'epigenetica-influencias','Alimentação, estresse, exercício, sono e conexões sociais.',                              'iniciante', 20, 3),
-- Hermetismo (categoria_id = 3)
(3, 'Os 7 Princípios',              'hermetismo-principios', 'Mentalismo, Correspondência, Vibração, Polaridade, Ritmo, Causa e Efeito, Gênero.',     'iniciante', 30, 1),
(3, 'Lei da Correspondência',       'lei-correspondencia',   'O que está em cima é como o que está embaixo.',                                           'iniciante', 20, 2),
(3, 'Tábua de Esmeralda',          'tabua-esmeralda',       'O texto hermético mais antigo e seus ensinamentos.',                                      'avancado', 25, 3),
-- Kundalini (categoria_id = 4)
(4, 'O Que é Kundalini?',           'kundalini-o-que-e',     'Energia espiritual latente na base da coluna vertebral.',                                 'intermediario', 25, 1),
(4, 'Os 33 Vértebras',             'kundalini-vertebras',   'O canal Sushumna e a ascensão da energia pelos chakras.',                                  'intermediario', 20, 2),
(4, 'Práticas de Despertar',       'kundalini-praticas',    'Técnicas seguras para o despertar da Kundalini.',                                         'avancado', 30, 3),
-- Teosofia (categoria_id = 5)
(5, 'O Que é Teosofia?',            'teosofia-o-que-e',      'Sophia = sabedoria + Theos = Deus. Síntese de todas as tradições.',                      'iniciante', 20, 1),
(5, 'Helena Blavatsky',            'teosofia-blavatsky',    'A fundadora da Sociedade Teosófica e sua obra.',                                          'intermediario', 25, 2),
(5, 'Os Três Princípios',          'teosofia-principios',   'Reencarnação, Karma e a Unidade fundamental de toda vida.',                               'iniciante', 20, 3),
-- Coração (categoria_id = 6)
(6, 'Campo Eletromagnético',        'coracao-campo',         'O coração gera campo 60x maior que o cérebro.',                                          'iniciante', 15, 1),
(6, 'Coerência Cardíaca',          'coracao-coerencia',     'Estado de harmonia entre coração, mente e emoção.',                                      'iniciante', 15, 2),
(6, 'Respiração Coerente',         'coracao-respiracao',    'Técnica de respiração para equilibrar o sistema nervoso.',                                'iniciante', 10, 3)
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`);

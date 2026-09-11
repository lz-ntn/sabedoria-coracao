-- =============================================
-- Migração 003: Fundir Portal Saberes no Caminho
-- Adiciona suporte a artigos (biblioteca) e discussões
-- =============================================

USE `caminho_saberes`;

-- 1. Adicionar colunas em licoes para distinguir lição vs artigo (tudo em um ALTER TABLE)
ALTER TABLE `licoes` 
ADD COLUMN `tipo` ENUM('licao','artigo') NOT NULL DEFAULT 'licao' AFTER `nivel`,
ADD COLUMN `resumo` VARCHAR(500) DEFAULT NULL AFTER `conteudo`,
ADD COLUMN `tags` VARCHAR(500) DEFAULT NULL AFTER `resumo`,
ADD COLUMN `imagem` VARCHAR(255) DEFAULT NULL AFTER `tags`,
ADD COLUMN `fonte` VARCHAR(500) DEFAULT NULL COMMENT 'Origem do conteúdo' AFTER `imagem`,
ADD COLUMN `autor_id` INT DEFAULT NULL AFTER `fonte`,
ADD COLUMN `publicado_em` TIMESTAMP NULL DEFAULT NULL AFTER `autor_id`,
ADD COLUMN `views` INT DEFAULT 0 AFTER `publicado_em`,
ADD FULLTEXT KEY `ft_conteudo` (`titulo`, `conteudo`, `tags`);

-- FK para autor (usuarios do admin/portal)
ALTER TABLE `licoes`
ADD CONSTRAINT `fk_licao_autor` FOREIGN KEY (`autor_id`)
REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 2. Tabela de discussões/comentários por lição/artigo
CREATE TABLE IF NOT EXISTS `discussoes` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `licao_id`     INT           NOT NULL,
  `usuario_id`   INT           DEFAULT NULL,
  `autor_nome`   VARCHAR(100)  DEFAULT NULL COMMENT 'Para visitantes não logados',
  `autor_email`  VARCHAR(255)  DEFAULT NULL,
  `conteudo`     TEXT          NOT NULL,
  `status`       ENUM('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `parent_id`    INT           DEFAULT NULL COMMENT 'Resposta a outro comentário',
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_discussao_licao` (`licao_id`),
  KEY `fk_discussao_usuario` (`usuario_id`),
  KEY `fk_discussao_parent` (`parent_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_discussao_licao` FOREIGN KEY (`licao_id`)
    REFERENCES `licoes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_discussao_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_discussao_parent` FOREIGN KEY (`parent_id`)
    REFERENCES `discussoes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Índices para performance
CREATE INDEX `idx_licao_tipo` ON `licoes` (`tipo`);
CREATE INDEX `idx_licao_publicado` ON `licoes` (`publicado_em`);
CREATE INDEX `idx_licao_categoria_tipo` ON `licoes` (`categoria_id`, `tipo`);

-- 4. View para compatibilidade: artigos públicos com info de categoria
CREATE OR REPLACE VIEW `vw_artigos_publicos` AS
SELECT 
    l.*,
    c.nome as categoria_nome,
    c.slug as categoria_slug,
    c.cor as categoria_cor,
    c.icone as categoria_icone,
    u.nome as autor_nome
FROM `licoes` l
LEFT JOIN `categorias` c ON c.id = l.categoria_id
LEFT JOIN `usuarios` u ON u.id = l.autor_id
WHERE l.tipo = 'artigo' AND l.publicado_em IS NOT NULL;

-- 5. View para lições do cronograma (apenas tipo='licao')
CREATE OR REPLACE VIEW `vw_licoes_cronograma` AS
SELECT 
    l.*,
    c.nome as categoria_nome,
    c.slug as categoria_slug,
    c.cor as categoria_cor,
    c.icone as categoria_icone
FROM `licoes` l
JOIN `categorias` c ON c.id = l.categoria_id
WHERE l.tipo = 'licao'
ORDER BY c.ordem, l.ordem;
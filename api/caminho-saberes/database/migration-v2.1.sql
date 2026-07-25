-- =============================================
-- Migração 2.1: login_attempts (rate limiting)
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

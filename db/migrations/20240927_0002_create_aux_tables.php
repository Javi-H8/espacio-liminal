<?php
// db/migrations/20240927_0002_create_aux_tables.php
return [
  'up' => <<<SQL
CREATE TABLE IF NOT EXISTS password_resets (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  token      CHAR(64) NOT NULL,                 -- token seguro (hex)
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pr_user (user_id),
  UNIQUE KEY uniq_pr_token (token),
  CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auth_tokens (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  selector   CHAR(24) NOT NULL,                 -- parte pública (cookie1)
  validator  CHAR(64) NOT NULL,                 -- hash de la parte secreta (cookie2)
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_at_user (user_id),
  UNIQUE KEY uniq_at_selector (selector),
  CONSTRAINT fk_at_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
  'down' => <<<SQL
DROP TABLE IF EXISTS auth_tokens;
DROP TABLE IF EXISTS password_resets;
SQL
];

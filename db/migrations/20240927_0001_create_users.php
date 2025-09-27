<?php
// db/migrations/20240927_0001_create_users.php
return [
  'up' => <<<SQL
CREATE TABLE IF NOT EXISTS users (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(120) NOT NULL,
  email            VARCHAR(190) NOT NULL UNIQUE,
  password_hash    VARCHAR(255) NOT NULL,
  phone            VARCHAR(30)  NULL,
  gender           ENUM('male','female','unspecified','other') NOT NULL DEFAULT 'unspecified',
  locale           ENUM('es','en','fr','it') NOT NULL DEFAULT 'es',
  preferred_category ENUM('naturaleza','ocio','aventura','cultural','todos','custom') NOT NULL DEFAULT 'todos',
  avatar_url       VARCHAR(255) NULL,
  email_verified_at DATETIME NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
  'down' => <<<SQL
DROP TABLE IF EXISTS users;
SQL
];

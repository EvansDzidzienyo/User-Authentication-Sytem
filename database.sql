CREATE TABLE IF NOT EXISTS `register` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(30) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `firstname` VARCHAR(40) NOT NULL,
  `surname` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_register_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Existing installations: widen Password to VARCHAR(255) before deploying
-- password_hash(). Legacy 32-character MD5 hashes are upgraded at next login.
-- No sample accounts or password hashes are included in this setup script.

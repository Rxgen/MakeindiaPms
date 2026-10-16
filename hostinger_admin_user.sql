-- Run this in Hostinger phpMyAdmin on your live database.
-- Replaces simple admin / admin123 with the new admin email login.

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELETE FROM `users` WHERE `username` IN ('admin', 'ashishu1809@gmail.com');

INSERT INTO `users` (`username`, `password`)
VALUES ('ashishu1809@gmail.com', MD5('Pms@2026'));

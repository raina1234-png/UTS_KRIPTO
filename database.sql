CREATE DATABASE IF NOT EXISTS db_crypto_admin_pink;
USE db_crypto_admin_pink;

CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `rsa_private_key` TEXT NOT NULL,
  `rsa_public_key` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `secure_contents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `symmetric_data` TEXT NOT NULL,    
  `asymmetric_data` TEXT NOT NULL,   
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
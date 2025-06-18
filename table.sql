CREATE DATABASE IF NOT EXISTS EnergieTransitie;

USE EnergieTransitie;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    huis INT NOT NULL,
    role ENUM('admin', 'superadmin', 'klant') NOT NULL DEFAULT 'klant',
);
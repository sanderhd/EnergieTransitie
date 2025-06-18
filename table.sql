CREATE DATABASE IF NOT EXISTS EnergieTransitie;
USE EnergieTransitie;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'superadmin', 'klant') NOT NULL DEFAULT 'klant'
);

CREATE TABLE IF NOT EXISTS huizen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bewoner1 INT NOT NULL,
    bewoner2 INT NOT NULL,
    CONSTRAINT fk_bewoner1 FOREIGN KEY (bewoner1) REFERENCES users(id),
    CONSTRAINT fk_bewoner2 FOREIGN KEY (bewoner2) REFERENCES users(id),
    CONSTRAINT chk_bewoners CHECK (bewoner1 <> bewoner2)
);

ALTER TABLE users ADD COLUMN huis INT NULL;


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

CREATE TABLE IF NOT EXISTS energietransitie_data (
    huis_id INT NOT NULL,
    Tijdstip TIMESTAMP PRIMARY KEY,
    Zonnepaneelspanning_V FLOAT NOT NULL DEFAULT 0,
    Zonnepaneelstroom_A FLOAT NOT NULL DEFAULT 0,
    Waterstofproductie_Lu FLOAT NOT NULL DEFAULT 0,
    Stroomverbruik_woning_kW FLOAT NOT NULL DEFAULT 0,
    Waterstofverbruik_auto_Lu FLOAT NOT NULL DEFAULT 0,
    Buitentemperatuur_C FLOAT NOT NULL DEFAULT 0,
    Binnentemperatuur_C FLOAT NOT NULL DEFAULT 0,
    Luchtdruk_hPa FLOAT NOT NULL DEFAULT 1013.25,
    Luchtvochtigheid_percent FLOAT NOT NULL DEFAULT 50,
    Accuniveau_percent FLOAT NOT NULL DEFAULT 100,
    CO2_concentratie_binnen_ppm FLOAT NOT NULL DEFAULT 400,
    Waterstofopslag_woning_percent FLOAT NOT NULL DEFAULT 0,
    Waterstofopslag_auto_percent FLOAT NOT NULL DEFAULT 0
);
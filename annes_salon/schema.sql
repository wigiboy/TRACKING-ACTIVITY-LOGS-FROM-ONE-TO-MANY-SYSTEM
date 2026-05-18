-- ============================================================
-- schema.sql — Anne's Salon Management System
-- Run this in phpMyAdmin or via MySQL CLI:
--   mysql -u root -p annes_salon < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS annes_salon
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE annes_salon;

-- ---- USERS (for login/registration) ----
CREATE TABLE IF NOT EXISTS users (
    id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(60)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,          -- bcrypt hash
    full_name  VARCHAR(120) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---- STYLISTS (parent / "one" side) ----
CREATE TABLE IF NOT EXISTS stylists (
    id           INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(120) NOT NULL,
    specialty    VARCHAR(120),
    phone        VARCHAR(30),
    email        VARCHAR(120),
    hired_date   DATE,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---- CLIENTS (child / "many" side — many clients per stylist) ----
CREATE TABLE IF NOT EXISTS clients (
    id           INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    stylist_id   INT          NOT NULL,
    full_name    VARCHAR(120) NOT NULL,
    phone        VARCHAR(30),
    email        VARCHAR(120),
    notes        TEXT,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_clients_stylist
        FOREIGN KEY (stylist_id) REFERENCES stylists(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- ---- ACTIVITY LOGS ----
CREATE TABLE IF NOT EXISTS activity_logs (
    id           INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(60)  NOT NULL,                    -- who did it
    action       ENUM('CREATE','READ','UPDATE','DELETE') NOT NULL,
    entity_type  VARCHAR(60)  NOT NULL,                    -- 'stylist' or 'client'
    entity_id    INT          NOT NULL,                    -- which record
    description  TEXT         NOT NULL,                    -- human-readable summary
    performed_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

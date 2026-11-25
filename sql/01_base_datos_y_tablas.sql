-- ============================================================================
-- BASE DE DATOS Y TABLAS COMPATIBLES CON EjemploLogin-main + 2FA
-- ============================================================================
CREATE DATABASE IF NOT EXISTS company_info CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE company_info;

-- Tabla usuarios (compatible con las columnas exactas del repo de la profesora + secret_2fa)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    apellido VARCHAR(150) NOT NULL,
    Usuario VARCHAR(150) NOT NULL UNIQUE,
    correo VARCHAR(150) NOT NULL UNIQUE,
    sexo CHAR(1) NOT NULL DEFAULT 'M',
    HashMagic VARCHAR(255) NOT NULL,
    secret_2fa VARCHAR(255) NULL,
    fecha_sistema TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla intentos_login (compatible con los campos exactos usados en objLoginAdmin.php)
CREATE TABLE IF NOT EXISTS intentos_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Usuario VARCHAR(150) NOT NULL,
    ipRemoto VARCHAR(45) NOT NULL,
    deteccion_anomalia TINYINT(1) NOT NULL DEFAULT 0,
    fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla trazabilidad_acciones (bitácora de auditoría mencionada en el video)
CREATE TABLE IF NOT EXISTS trazabilidad_acciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla_afectada VARCHAR(100) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    codigo_registro VARCHAR(100) NOT NULL,
    usuario_sistema VARCHAR(150) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

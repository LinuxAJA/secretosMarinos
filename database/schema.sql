-- ============================================================================
-- schema.sql — Estructura física de la BD Misterios Del Mar (V1.0)
-- ============================================================================
-- Cómo importarlo en XAMPP:
--   1. Abre phpMyAdmin → http://localhost/phpmyadmin
--   2. Pestaña "SQL" o "Importar"
--   3. Ejecuta este archivo completo
--
-- Convenciones:
--   - utf8mb4 + InnoDB
--   - PKs: id (INT UNSIGNED AUTO_INCREMENT)
--   - Tablas en plural, snake_case
--   - FKs con ON DELETE / ON UPDATE definidos
-- ============================================================================

CREATE DATABASE IF NOT EXISTS misterios_del_mar
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE misterios_del_mar;

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- ROLES
-- Un rol agrupa permisos lógicos (admin, docente, estudiante)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  descripcion VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- USUARIOS
-- password_hash guarda el resultado de password_hash() de PHP (nunca texto plano)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  rol_id TINYINT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) NULL,
  puntos INT UNSIGNED NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_usuarios_correo (correo),
  KEY idx_usuarios_rol (rol_id),
  CONSTRAINT fk_usuarios_rol
    FOREIGN KEY (rol_id) REFERENCES roles (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- CATEGORÍAS DE CONTENIDO EDUCATIVO
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS categorias_contenido;
CREATE TABLE categorias_contenido (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  descripcion TEXT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_categorias_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- CONTENIDOS EDUCATIVOS (artículos, guías, glosarios...)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS contenidos;
CREATE TABLE contenidos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  categoria_id INT UNSIGNED NULL,
  autor_id INT UNSIGNED NULL,
  titulo VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  resumen VARCHAR(500) NULL,
  cuerpo MEDIUMTEXT NOT NULL,
  nivel ENUM('basico','intermedio','avanzado') NOT NULL DEFAULT 'basico',
  publicado TINYINT(1) NOT NULL DEFAULT 0,
  visitas INT UNSIGNED NOT NULL DEFAULT 0,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_contenidos_slug (slug),
  KEY idx_contenidos_categoria (categoria_id),
  KEY idx_contenidos_autor (autor_id),
  KEY idx_contenidos_publicado (publicado),
  CONSTRAINT fk_contenidos_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias_contenido (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  CONSTRAINT fk_contenidos_autor
    FOREIGN KEY (autor_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- ECOSISTEMAS
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS ecosistemas;
CREATE TABLE ecosistemas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  descripcion TEXT NOT NULL,
  funcion_ecologica TEXT NULL,
  amenazas TEXT NULL,
  buenas_practicas TEXT NULL,
  imagen VARCHAR(255) NULL,
  publicado TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_ecosistemas_slug (slug),
  KEY idx_ecosistemas_publicado (publicado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- ESPECIES MARINAS
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS especies;
CREATE TABLE especies (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ecosistema_id INT UNSIGNED NULL,
  autor_id INT UNSIGNED NULL,
  nombre_comun VARCHAR(150) NOT NULL,
  nombre_cientifico VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  clasificacion VARCHAR(255) NULL COMMENT 'taxonomía resumida',
  habitat TEXT NULL,
  distribucion TEXT NULL,
  amenazas TEXT NULL,
  estado_conservacion VARCHAR(100) NULL,
  descripcion TEXT NOT NULL,
  imagen VARCHAR(255) NULL,
  publicado TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_especies_slug (slug),
  KEY idx_especies_ecosistema (ecosistema_id),
  KEY idx_especies_autor (autor_id),
  KEY idx_especies_publicado (publicado),
  KEY idx_especies_nombre (nombre_comun),
  CONSTRAINT fk_especies_ecosistema
    FOREIGN KEY (ecosistema_id) REFERENCES ecosistemas (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  CONSTRAINT fk_especies_autor
    FOREIGN KEY (autor_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- NOTICIAS
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS noticias;
CREATE TABLE noticias (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  autor_id INT UNSIGNED NULL,
  titulo VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  resumen VARCHAR(500) NULL,
  cuerpo MEDIUMTEXT NOT NULL,
  categoria VARCHAR(80) NULL,
  destacada TINYINT(1) NOT NULL DEFAULT 0,
  publicada TINYINT(1) NOT NULL DEFAULT 0,
  imagen VARCHAR(255) NULL,
  publicado_en DATETIME NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_noticias_slug (slug),
  KEY idx_noticias_autor (autor_id),
  KEY idx_noticias_publicada (publicada),
  CONSTRAINT fk_noticias_autor
    FOREIGN KEY (autor_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- CAMPAÑAS AMBIENTALES
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS campanias;
CREATE TABLE campanias (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  responsable_id INT UNSIGNED NULL,
  titulo VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  descripcion TEXT NOT NULL,
  objetivo VARCHAR(500) NULL,
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  estado ENUM('borrador','activa','finalizada','cancelada') NOT NULL DEFAULT 'borrador',
  imagen VARCHAR(255) NULL,
  motivo_cancelacion TEXT NULL COMMENT 'Obligatorio al cancelar; se conserva como historial',
  cancelada_en DATETIME NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_campanias_slug (slug),
  KEY idx_campanias_responsable (responsable_id),
  KEY idx_campanias_estado (estado),
  CONSTRAINT fk_campanias_responsable
    FOREIGN KEY (responsable_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- REPORTES AMBIENTALES (participación ciudadana)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS reportes_ambientales;
CREATE TABLE reportes_ambientales (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NULL,
  revisor_id INT UNSIGNED NULL,
  titulo VARCHAR(180) NOT NULL,
  descripcion TEXT NOT NULL,
  ubicacion VARCHAR(255) NULL,
  tipo ENUM('contaminacion','residuos','fauna_afectada','deterioro','otro') NOT NULL DEFAULT 'otro',
  estado ENUM('pendiente','en_revision','resuelto') NOT NULL DEFAULT 'pendiente',
  imagen VARCHAR(255) NULL,
  notas_revision TEXT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reportes_usuario (usuario_id),
  KEY idx_reportes_revisor (revisor_id),
  KEY idx_reportes_estado (estado),
  CONSTRAINT fk_reportes_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  CONSTRAINT fk_reportes_revisor
    FOREIGN KEY (revisor_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- INSIGNIAS (gamificación básica)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS insignias;
CREATE TABLE insignias (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(60) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  icono VARCHAR(120) NULL,
  puntos_requeridos INT UNSIGNED NOT NULL DEFAULT 0,
  activa TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_insignias_codigo (codigo),
  KEY idx_insignias_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relación N:M usuario ↔ insignia
DROP TABLE IF EXISTS usuario_insignia;
CREATE TABLE usuario_insignia (
  usuario_id INT UNSIGNED NOT NULL,
  insignia_id INT UNSIGNED NOT NULL,
  otorgada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, insignia_id),
  CONSTRAINT fk_ui_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_ui_insignia
    FOREIGN KEY (insignia_id) REFERENCES insignias (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historial de puntos (auditoría ligera de gamificación)
DROP TABLE IF EXISTS puntos_usuario;
CREATE TABLE puntos_usuario (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  puntos INT NOT NULL,
  motivo VARCHAR(255) NOT NULL,
  referencia_tipo VARCHAR(50) NULL COMMENT 'contenido,reporte,campania...',
  referencia_id INT UNSIGNED NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_puntos_usuario (usuario_id),
  UNIQUE KEY uk_puntos_referencia (usuario_id, referencia_tipo, referencia_id),
  CONSTRAINT fk_puntos_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- AUDITORÍA (acciones relevantes del sistema)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS auditoria;
CREATE TABLE auditoria (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NULL,
  accion VARCHAR(100) NOT NULL,
  detalle TEXT NULL,
  ip VARCHAR(45) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_auditoria_usuario (usuario_id),
  CONSTRAINT fk_auditoria_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 006_gamification.sql — Migración Paso 6
-- ============================================================================
-- Ejecutar UNA VEZ sobre una BD existente creada antes del Paso 6.
-- En instalaciones nuevas, schema.sql ya contiene estos cambios.
-- ============================================================================

USE misterios_del_mar;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE insignias
  ADD COLUMN activa TINYINT(1) NOT NULL DEFAULT 1 AFTER puntos_requeridos,
  ADD KEY idx_insignias_activa (activa);

-- Evita doble cobro del mismo evento (usuario + tipo + id de referencia)
ALTER TABLE puntos_usuario
  ADD UNIQUE KEY uk_puntos_referencia (usuario_id, referencia_tipo, referencia_id);

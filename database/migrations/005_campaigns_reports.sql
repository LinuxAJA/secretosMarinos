-- ============================================================================
-- 005_campaigns_reports.sql — Migración Paso 5
-- ============================================================================
-- Ejecutar UNA VEZ sobre una BD existente creada antes del Paso 5.
-- En instalaciones nuevas, schema.sql ya contiene estos cambios.
-- ============================================================================

USE secretos_marinos;
SET NAMES utf8mb4;

-- Campañas: trazabilidad de edición y cancelación justificada
ALTER TABLE campanias
  ADD COLUMN motivo_cancelacion TEXT NULL AFTER imagen,
  ADD COLUMN cancelada_en DATETIME NULL AFTER motivo_cancelacion,
  ADD COLUMN actualizado_en DATETIME NULL DEFAULT NULL
    ON UPDATE CURRENT_TIMESTAMP AFTER creado_en;

-- Reportes: revisor y nota de seguimiento
ALTER TABLE reportes_ambientales
  ADD COLUMN revisor_id INT UNSIGNED NULL AFTER usuario_id,
  ADD COLUMN notas_revision TEXT NULL AFTER imagen,
  ADD KEY idx_reportes_revisor (revisor_id),
  ADD CONSTRAINT fk_reportes_revisor
    FOREIGN KEY (revisor_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL;

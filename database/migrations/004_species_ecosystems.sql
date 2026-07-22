-- ============================================================================
-- 004_species_ecosystems.sql — Migración Paso 4
-- ============================================================================
-- Ejecutar UNA VEZ sobre una BD existente creada antes del Paso 4.
-- En instalaciones nuevas, schema.sql ya contiene estos cambios.
-- ============================================================================

USE secretos_marinos;
SET NAMES utf8mb4;

ALTER TABLE ecosistemas
  ADD COLUMN publicado TINYINT(1) NOT NULL DEFAULT 1 AFTER imagen,
  ADD COLUMN actualizado_en DATETIME NULL DEFAULT NULL
    ON UPDATE CURRENT_TIMESTAMP AFTER creado_en,
  ADD KEY idx_ecosistemas_publicado (publicado);

ALTER TABLE especies
  ADD COLUMN autor_id INT UNSIGNED NULL AFTER ecosistema_id,
  ADD KEY idx_especies_autor (autor_id),
  ADD KEY idx_especies_publicado (publicado),
  ADD CONSTRAINT fk_especies_autor
    FOREIGN KEY (autor_id) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL;

-- Asigna las especies preexistentes al docente demo si está disponible.
UPDATE especies
SET autor_id = (
  SELECT id
  FROM usuarios
  WHERE correo = 'docente@secretosmarinos.local'
  LIMIT 1
)
WHERE autor_id IS NULL
  AND EXISTS (
    SELECT 1
    FROM usuarios
    WHERE correo = 'docente@secretosmarinos.local'
  );

-- Normaliza valores demo anteriores al catálogo controlado del Paso 4.
UPDATE especies
SET estado_conservacion = 'Preocupación menor'
WHERE estado_conservacion = 'Preocupación menor / variable según especie';

UPDATE especies
SET estado_conservacion = 'No evaluado'
WHERE estado_conservacion = 'No evaluado de forma uniforme';

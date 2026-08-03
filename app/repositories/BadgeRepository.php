<?php
/**
 * ============================================================================
 * BadgeRepository.php — Persistencia de insignias y asignaciones
 * ============================================================================
 * Gestiona el catálogo de insignias y la relación N:M usuario_insignia.
 * No otorga puntos: eso vive en GamificationService + PointsRepository.
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class BadgeRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Catálogo público / admin: insignias activas por umbral de puntos.
     *
     * @param bool $onlyActive Si false, lista todas (admin)
     * @return list<array<string,mixed>>
     */
    public function listAll(bool $onlyActive = true): array
    {
        $sql = 'SELECT * FROM insignias';
        if ($onlyActive) {
            $sql .= ' WHERE activa = 1';
        }
        $sql .= ' ORDER BY puntos_requeridos ASC, nombre ASC';

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM insignias WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM insignias WHERE codigo = :codigo LIMIT 1'
        );
        $stmt->execute(['codigo' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function codigoExists(string $codigo, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM insignias WHERE codigo = :codigo';
        $params = ['codigo' => $codigo];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Insignias ya otorgadas a un usuario.
     *
     * @return list<array<string,mixed>>
     */
    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, ui.otorgada_en
             FROM usuario_insignia ui
             INNER JOIN insignias i ON i.id = ui.insignia_id
             WHERE ui.usuario_id = :usuario_id
             ORDER BY i.puntos_requeridos ASC, i.nombre ASC'
        );
        $stmt->execute(['usuario_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * IDs de insignias que el usuario ya posee.
     *
     * @return list<int>
     */
    public function ownedIds(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT insignia_id FROM usuario_insignia WHERE usuario_id = :usuario_id'
        );
        $stmt->execute(['usuario_id' => $userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Insignias activas cuyo umbral el usuario ya alcanza y aún no tiene.
     *
     * @return list<array<string,mixed>>
     */
    public function eligibleForUser(int $userId, int $points): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*
             FROM insignias i
             WHERE i.activa = 1
               AND i.puntos_requeridos <= :puntos
               AND i.id NOT IN (
                   SELECT ui.insignia_id
                   FROM usuario_insignia ui
                   WHERE ui.usuario_id = :usuario_id
               )
             ORDER BY i.puntos_requeridos ASC'
        );
        $stmt->execute([
            'puntos' => $points,
            'usuario_id' => $userId,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Otorga una insignia (ignora duplicados por PK compuesta).
     */
    public function grant(int $userId, int $badgeId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO usuario_insignia (usuario_id, insignia_id)
             VALUES (:usuario_id, :insignia_id)'
        );
        return $stmt->execute([
            'usuario_id' => $userId,
            'insignia_id' => $badgeId,
        ]);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO insignias
                (codigo, nombre, descripcion, icono, puntos_requeridos, activa)
             VALUES
                (:codigo, :nombre, :descripcion, :icono, :puntos_requeridos, :activa)'
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE insignias SET
                codigo = :codigo,
                nombre = :nombre,
                descripcion = :descripcion,
                icono = :icono,
                puntos_requeridos = :puntos_requeridos,
                activa = :activa
             WHERE id = :id'
        );
        return $stmt->execute(['id' => $id] + $data);
    }

    /**
     * Elimina insignia; CASCADE limpia usuario_insignia.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM insignias WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM insignias')->fetchColumn();
    }

    /**
     * Próxima insignia activa aún no obtenida (para barra de progreso).
     *
     * @return array<string,mixed>|null
     */
    public function nextForUser(int $userId, int $points): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*
             FROM insignias i
             WHERE i.activa = 1
               AND i.puntos_requeridos > :puntos
               AND i.id NOT IN (
                   SELECT ui.insignia_id
                   FROM usuario_insignia ui
                   WHERE ui.usuario_id = :usuario_id
               )
             ORDER BY i.puntos_requeridos ASC
             LIMIT 1'
        );
        $stmt->execute([
            'puntos' => $points,
            'usuario_id' => $userId,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

<?php
/**
 * ============================================================================
 * PointsRepository.php — Historial de puntos y ranking
 * ============================================================================
 * Registra movimientos en puntos_usuario y actualiza el saldo en usuarios.
 * La lógica de negocio (montos, badges) vive en GamificationService.
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PointsRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Expone la conexión para transacciones del GamificationService.
     */
    public function getConnection(): PDO
    {
        return $this->db;
    }

    /**
     * ¿Ya existe un movimiento con la misma referencia? (idempotencia)
     */
    public function referenceExists(int $userId, string $refType, int $refId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM puntos_usuario
             WHERE usuario_id = :usuario_id
               AND referencia_tipo = :referencia_tipo
               AND referencia_id = :referencia_id
             LIMIT 1'
        );
        $stmt->execute([
            'usuario_id' => $userId,
            'referencia_tipo' => $refType,
            'referencia_id' => $refId,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Inserta un movimiento de puntos (positivo o negativo).
     *
     * @return int ID del movimiento
     */
    public function insertMovement(
        int $userId,
        int $points,
        string $motivo,
        ?string $refType = null,
        ?int $refId = null
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO puntos_usuario
                (usuario_id, puntos, motivo, referencia_tipo, referencia_id)
             VALUES
                (:usuario_id, :puntos, :motivo, :referencia_tipo, :referencia_id)'
        );
        $stmt->execute([
            'usuario_id' => $userId,
            'puntos' => $points,
            'motivo' => $motivo,
            'referencia_tipo' => $refType,
            'referencia_id' => $refId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Ajusta el saldo del usuario de forma atómica (delta).
     */
    public function applyDelta(int $userId, int $delta): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET puntos = puntos + :delta
             WHERE id = :id'
        );
        return $stmt->execute([
            'delta' => $delta,
            'id' => $userId,
        ]);
    }

    /**
     * Saldo actual de puntos del usuario.
     */
    public function getBalance(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT puntos FROM usuarios WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Historial reciente de un usuario.
     *
     * @return list<array<string,mixed>>
     */
    public function listByUser(int $userId, int $limit = 15): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM puntos_usuario
             WHERE usuario_id = :usuario_id
             ORDER BY creado_en DESC
             LIMIT ' . (int) $limit
        );
        $stmt->execute(['usuario_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Ranking público: usuarios activos ordenados por puntos.
     *
     * @return list<array<string,mixed>>
     */
    public function ranking(int $limit = 10): array
    {
        $stmt = $this->db->query(
            'SELECT u.id, u.nombre, u.puntos, r.nombre AS rol
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.activo = 1 AND u.puntos > 0
             ORDER BY u.puntos DESC, u.nombre ASC
             LIMIT ' . (int) $limit
        );
        return $stmt->fetchAll();
    }
}

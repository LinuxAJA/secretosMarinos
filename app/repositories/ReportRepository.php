<?php
/**
 * ============================================================================
 * ReportRepository.php — Persistencia de reportes ambientales
 * ============================================================================
 * Soporta listados públicos (solo resueltos), cola admin, “mis reportes”
 * y operaciones CRUD básicas.
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ReportRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Catálogo público: únicamente reportes resueltos (transparencia).
     *
     * @return list<array<string,mixed>>
     */
    public function listResolved(?string $q = null, ?string $tipo = null, int $limit = 9, int $offset = 0): array
    {
        [$where, $params] = $this->resolvedFilters($q, $tipo);
        $sql = $this->baseSelect() . $where
             . ' ORDER BY r.actualizado_en DESC, r.creado_en DESC
                 LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta reportes resueltos visibles al público.
     */
    public function countResolved(?string $q = null, ?string $tipo = null): int
    {
        [$where, $params] = $this->resolvedFilters($q, $tipo);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM reportes_ambientales r' . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Reportes de un usuario (panel / mis reportes).
     *
     * @return list<array<string,mixed>>
     */
    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect()
            . ' WHERE r.usuario_id = :usuario_id
                ORDER BY r.creado_en DESC'
        );
        $stmt->execute(['usuario_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Cola administrativa con filtros opcionales.
     *
     * @return list<array<string,mixed>>
     */
    public function listAll(?string $estado = null, ?string $tipo = null): array
    {
        $where = ' WHERE 1=1';
        $params = [];

        if ($estado) {
            $where .= ' AND r.estado = :estado';
            $params['estado'] = $estado;
        }
        if ($tipo) {
            $where .= ' AND r.tipo = :tipo';
            $params['tipo'] = $tipo;
        }

        $sql = $this->baseSelect() . $where
             . ' ORDER BY
                    FIELD(r.estado, \'pendiente\', \'en_revision\', \'resuelto\'),
                    r.creado_en DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE r.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO reportes_ambientales
                (usuario_id, titulo, descripcion, ubicacion, tipo, estado, imagen)
             VALUES
                (:usuario_id, :titulo, :descripcion, :ubicacion, :tipo, :estado, :imagen)'
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza campos editables por el autor (solo mientras pendiente).
     *
     * @param array<string,mixed> $data
     */
    public function updateByOwner(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE reportes_ambientales SET
                titulo = :titulo,
                descripcion = :descripcion,
                ubicacion = :ubicacion,
                tipo = :tipo,
                imagen = :imagen
             WHERE id = :id AND estado = \'pendiente\''
        );
        return $stmt->execute(['id' => $id] + $data);
    }

    /**
     * Actualización de revisión (estado, nota, revisor).
     *
     * @param array<string,mixed> $data
     */
    public function updateReview(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE reportes_ambientales SET
                estado = :estado,
                notas_revision = :notas_revision,
                revisor_id = :revisor_id
             WHERE id = :id'
        );
        return $stmt->execute(['id' => $id] + $data);
    }

    /**
     * Elimina un reporte por ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM reportes_ambientales WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Total de reportes (dashboard).
     */
    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM reportes_ambientales')->fetchColumn();
    }

    /**
     * Cuenta por estado (útil para badges del dashboard).
     */
    public function countByEstado(string $estado): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM reportes_ambientales WHERE estado = :estado'
        );
        $stmt->execute(['estado' => $estado]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Desglose de reportes por estado (KPIs Paso 7).
     *
     * @return array<string,int>
     */
    public function countGroupedByEstado(): array
    {
        $rows = $this->db
            ->query(
                'SELECT estado, COUNT(*) AS total
                 FROM reportes_ambientales
                 GROUP BY estado'
            )
            ->fetchAll();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['estado']] = (int) $row['total'];
        }

        return $out;
    }

    private function baseSelect(): string
    {
        return 'SELECT r.*,
                       u.nombre AS autor_nombre,
                       rev.nombre AS revisor_nombre
                FROM reportes_ambientales r
                LEFT JOIN usuarios u ON u.id = r.usuario_id
                LEFT JOIN usuarios rev ON rev.id = r.revisor_id';
    }

    /**
     * @return array{0:string,1:array<string,mixed>}
     */
    private function resolvedFilters(?string $q, ?string $tipo): array
    {
        $where = ' WHERE r.estado = \'resuelto\'';
        $params = [];

        if ($q) {
            $like = '%' . $q . '%';
            $where .= ' AND (r.titulo LIKE :q_titulo
                         OR r.descripcion LIKE :q_descripcion
                         OR r.ubicacion LIKE :q_ubicacion)';
            $params += [
                'q_titulo' => $like,
                'q_descripcion' => $like,
                'q_ubicacion' => $like,
            ];
        }

        if ($tipo) {
            $where .= ' AND r.tipo = :tipo';
            $params['tipo'] = $tipo;
        }

        return [$where, $params];
    }
}

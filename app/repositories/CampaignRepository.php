<?php
/**
 * ============================================================================
 * CampaignRepository.php — Persistencia de campañas ambientales
 * ============================================================================
 * Encapsula el acceso SQL a la tabla campanias.
 * No contiene reglas de negocio: solo consultas y mutaciones.
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class CampaignRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Listado público: solo campañas activas o finalizadas.
     *
     * @param string|null $q      Búsqueda libre (título/descripción/objetivo)
     * @param string|null $estado Filtro opcional: activa|finalizada
     * @return list<array<string,mixed>>
     */
    public function listPublic(?string $q = null, ?string $estado = null, int $limit = 9, int $offset = 0): array
    {
        [$where, $params] = $this->publicFilters($q, $estado);
        $sql = $this->baseSelect() . $where
             . ' ORDER BY
                    CASE c.estado WHEN \'activa\' THEN 0 ELSE 1 END,
                    c.fecha_inicio DESC,
                    c.creado_en DESC
                 LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta campañas visibles en el catálogo público.
     */
    public function countPublic(?string $q = null, ?string $estado = null): int
    {
        [$where, $params] = $this->publicFilters($q, $estado);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM campanias c' . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Listado administrativo completo (incluye borradores y canceladas).
     *
     * @return list<array<string,mixed>>
     */
    public function listAll(): array
    {
        return $this->db
            ->query($this->baseSelect() . ' ORDER BY c.creado_en DESC')
            ->fetchAll();
    }

    /**
     * Busca una campaña por ID (admin / ownership).
     *
     * @return array<string,mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE c.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ficha pública por slug (solo activa/finalizada).
     *
     * @return array<string,mixed>|null
     */
    public function findPublicBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect()
            . ' WHERE c.slug = :slug
                  AND c.estado IN (\'activa\', \'finalizada\')
                LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * ¿Existe ya el slug? Útil para generar slugs únicos.
     *
     * @param int|null $exceptId Excluye el propio registro en ediciones
     */
    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM campanias WHERE slug = :slug';
        $params = ['slug' => $slug];
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
     * Inserta una campaña y devuelve el ID generado.
     *
     * @param array<string,mixed> $data Columnas alineadas a placeholders nombrados
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO campanias
                (responsable_id, titulo, slug, descripcion, objetivo,
                 fecha_inicio, fecha_fin, estado, imagen,
                 motivo_cancelacion, cancelada_en)
             VALUES
                (:responsable_id, :titulo, :slug, :descripcion, :objetivo,
                 :fecha_inicio, :fecha_fin, :estado, :imagen,
                 :motivo_cancelacion, :cancelada_en)'
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza una campaña existente.
     *
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE campanias SET
                titulo = :titulo,
                slug = :slug,
                descripcion = :descripcion,
                objetivo = :objetivo,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin,
                estado = :estado,
                imagen = :imagen,
                motivo_cancelacion = :motivo_cancelacion,
                cancelada_en = :cancelada_en
             WHERE id = :id'
        );
        return $stmt->execute(['id' => $id] + $data);
    }

    /**
     * Elimina una campaña por ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM campanias WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Total de campañas (dashboard admin).
     */
    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM campanias')->fetchColumn();
    }

    /**
     * Cuenta campañas en un estado concreto.
     */
    public function countByEstado(string $estado): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM campanias WHERE estado = :estado'
        );
        $stmt->execute(['estado' => $estado]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Desglose de campañas por estado (KPIs Paso 7).
     *
     * @return array<string,int>
     */
    public function countGroupedByEstado(): array
    {
        $rows = $this->db
            ->query(
                'SELECT estado, COUNT(*) AS total
                 FROM campanias
                 GROUP BY estado'
            )
            ->fetchAll();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['estado']] = (int) $row['total'];
        }

        return $out;
    }

    /**
     * SELECT base con nombre del responsable.
     */
    private function baseSelect(): string
    {
        return 'SELECT c.*, u.nombre AS responsable_nombre
                FROM campanias c
                LEFT JOIN usuarios u ON u.id = c.responsable_id';
    }

    /**
     * Construye WHERE del catálogo público.
     *
     * @return array{0:string,1:array<string,mixed>}
     */
    private function publicFilters(?string $q, ?string $estado): array
    {
        $where = ' WHERE c.estado IN (\'activa\', \'finalizada\')';
        $params = [];

        if ($q) {
            $like = '%' . $q . '%';
            // Placeholders únicos: PDO nativo no reutiliza el mismo nombre
            $where .= ' AND (c.titulo LIKE :q_titulo
                         OR c.descripcion LIKE :q_descripcion
                         OR c.objetivo LIKE :q_objetivo)';
            $params += [
                'q_titulo' => $like,
                'q_descripcion' => $like,
                'q_objetivo' => $like,
            ];
        }

        if ($estado === 'activa' || $estado === 'finalizada') {
            $where .= ' AND c.estado = :estado';
            $params['estado'] = $estado;
        }

        return [$where, $params];
    }
}

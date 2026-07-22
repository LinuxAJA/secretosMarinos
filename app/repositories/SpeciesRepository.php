<?php
/**
 * ============================================================================
 * SpeciesRepository.php — Persistencia de especies marinas
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class SpeciesRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** @return list<array<string,mixed>> */
    public function listPublished(
        ?string $q = null,
        ?int $ecosystemId = null,
        ?string $conservation = null,
        int $limit = 9,
        int $offset = 0
    ): array {
        [$where, $params] = $this->publicFilters($q, $ecosystemId, $conservation);
        $sql = $this->baseSelect() . $where
             . ' ORDER BY s.nombre_comun ASC
                 LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPublished(
        ?string $q = null,
        ?int $ecosystemId = null,
        ?string $conservation = null
    ): int {
        [$where, $params] = $this->publicFilters($q, $ecosystemId, $conservation);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM especies s' . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string,mixed>> */
    public function listPublishedByEcosystem(int $ecosystemId, int $limit = 12): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect()
            . ' WHERE s.publicado = 1 AND s.ecosistema_id = :ecosistema_id
                ORDER BY s.nombre_comun ASC LIMIT ' . (int) $limit
        );
        $stmt->execute(['ecosistema_id' => $ecosystemId]);
        return $stmt->fetchAll();
    }

    /** @return list<array<string,mixed>> */
    public function listAll(): array
    {
        return $this->db
            ->query($this->baseSelect() . ' ORDER BY s.creado_en DESC')
            ->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE s.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect()
            . ' WHERE s.slug = :slug AND s.publicado = 1 LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM especies WHERE slug = :slug';
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

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO especies
                (ecosistema_id, autor_id, nombre_comun, nombre_cientifico, slug,
                 clasificacion, habitat, distribucion, amenazas,
                 estado_conservacion, descripcion, imagen, publicado)
             VALUES
                (:ecosistema_id, :autor_id, :nombre_comun, :nombre_cientifico, :slug,
                 :clasificacion, :habitat, :distribucion, :amenazas,
                 :estado_conservacion, :descripcion, :imagen, :publicado)'
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE especies SET
                ecosistema_id = :ecosistema_id,
                nombre_comun = :nombre_comun,
                nombre_cientifico = :nombre_cientifico,
                slug = :slug,
                clasificacion = :clasificacion,
                habitat = :habitat,
                distribucion = :distribucion,
                amenazas = :amenazas,
                estado_conservacion = :estado_conservacion,
                descripcion = :descripcion,
                imagen = :imagen,
                publicado = :publicado
             WHERE id = :id'
        );
        return $stmt->execute(['id' => $id] + $data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM especies WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM especies')->fetchColumn();
    }

    private function baseSelect(): string
    {
        return 'SELECT s.*, e.nombre AS ecosistema_nombre, e.slug AS ecosistema_slug,
                       u.nombre AS autor_nombre
                FROM especies s
                LEFT JOIN ecosistemas e ON e.id = s.ecosistema_id
                LEFT JOIN usuarios u ON u.id = s.autor_id';
    }

    /**
     * @return array{0:string,1:array<string,mixed>}
     */
    private function publicFilters(
        ?string $q,
        ?int $ecosystemId,
        ?string $conservation
    ): array {
        $where = ' WHERE s.publicado = 1';
        $params = [];

        if ($q) {
            $like = '%' . $q . '%';
            $where .= ' AND (s.nombre_comun LIKE :q_comun
                         OR s.nombre_cientifico LIKE :q_cientifico
                         OR s.clasificacion LIKE :q_clasificacion
                         OR s.descripcion LIKE :q_descripcion)';
            $params += [
                'q_comun' => $like,
                'q_cientifico' => $like,
                'q_clasificacion' => $like,
                'q_descripcion' => $like,
            ];
        }

        if ($ecosystemId !== null) {
            $where .= ' AND s.ecosistema_id = :ecosistema_id';
            $params['ecosistema_id'] = $ecosystemId;
        }

        if ($conservation) {
            $where .= ' AND s.estado_conservacion = :conservacion';
            $params['conservacion'] = $conservation;
        }

        return [$where, $params];
    }
}

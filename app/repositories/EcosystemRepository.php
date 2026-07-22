<?php
/**
 * ============================================================================
 * EcosystemRepository.php — Persistencia de ecosistemas
 * ============================================================================
 * Centraliza consultas públicas, filtros y CRUD administrativo.
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class EcosystemRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** @return list<array<string,mixed>> */
    public function listPublished(?string $q = null, int $limit = 9, int $offset = 0): array
    {
        $sql = 'SELECT e.*,
                       (SELECT COUNT(*) FROM especies s
                        WHERE s.ecosistema_id = e.id AND s.publicado = 1) AS total_especies
                FROM ecosistemas e
                WHERE e.publicado = 1';
        $params = [];

        if ($q) {
            $like = '%' . $q . '%';
            $sql .= ' AND (e.nombre LIKE :q_nombre
                       OR e.descripcion LIKE :q_descripcion
                       OR e.amenazas LIKE :q_amenazas)';
            $params = [
                'q_nombre' => $like,
                'q_descripcion' => $like,
                'q_amenazas' => $like,
            ];
        }

        $sql .= ' ORDER BY e.nombre ASC
                  LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPublished(?string $q = null): int
    {
        $sql = 'SELECT COUNT(*) FROM ecosistemas e WHERE e.publicado = 1';
        $params = [];

        if ($q) {
            $like = '%' . $q . '%';
            $sql .= ' AND (e.nombre LIKE :q_nombre
                       OR e.descripcion LIKE :q_descripcion
                       OR e.amenazas LIKE :q_amenazas)';
            $params = [
                'q_nombre' => $like,
                'q_descripcion' => $like,
                'q_amenazas' => $like,
            ];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string,mixed>> */
    public function listAll(): array
    {
        $sql = 'SELECT e.*,
                       (SELECT COUNT(*) FROM especies s WHERE s.ecosistema_id = e.id) AS total_especies
                FROM ecosistemas e
                ORDER BY e.nombre ASC';
        return $this->db->query($sql)->fetchAll();
    }

    /** @return list<array<string,mixed>> */
    public function listPublishedOptions(): array
    {
        return $this->db
            ->query('SELECT id, nombre FROM ecosistemas WHERE publicado = 1 ORDER BY nombre ASC')
            ->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*,
                    (SELECT COUNT(*) FROM especies s WHERE s.ecosistema_id = e.id) AS total_especies
             FROM ecosistemas e
             WHERE e.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*,
                    (SELECT COUNT(*) FROM especies s
                     WHERE s.ecosistema_id = e.id AND s.publicado = 1) AS total_especies
             FROM ecosistemas e
             WHERE e.slug = :slug AND e.publicado = 1
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM ecosistemas WHERE slug = :slug';
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
            'INSERT INTO ecosistemas
                (nombre, slug, descripcion, funcion_ecologica, amenazas,
                 buenas_practicas, imagen, publicado)
             VALUES
                (:nombre, :slug, :descripcion, :funcion_ecologica, :amenazas,
                 :buenas_practicas, :imagen, :publicado)'
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ecosistemas SET
                nombre = :nombre,
                slug = :slug,
                descripcion = :descripcion,
                funcion_ecologica = :funcion_ecologica,
                amenazas = :amenazas,
                buenas_practicas = :buenas_practicas,
                imagen = :imagen,
                publicado = :publicado
             WHERE id = :id'
        );
        return $stmt->execute(['id' => $id] + $data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ecosistemas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM ecosistemas')->fetchColumn();
    }
}

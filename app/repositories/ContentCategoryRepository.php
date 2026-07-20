<?php
/**
 * ============================================================================
 * ContentCategoryRepository.php — CRUD de categorías educativas
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ContentCategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM contenidos ct WHERE ct.categoria_id = c.id) AS total_contenidos
             FROM categorias_contenido c
             ORDER BY c.nombre ASC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categorias_contenido WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categorias_contenido WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM categorias_contenido WHERE slug = :slug';
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

    /** @param array{nombre:string,slug:string,descripcion:?string} $data */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categorias_contenido (nombre, slug, descripcion)
             VALUES (:nombre, :slug, :descripcion)'
        );
        $stmt->execute([
            'nombre'      => $data['nombre'],
            'slug'        => $data['slug'],
            'descripcion' => $data['descripcion'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** @param array{nombre:string,slug:string,descripcion:?string} $data */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categorias_contenido
             SET nombre = :nombre, slug = :slug, descripcion = :descripcion
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'          => $id,
            'nombre'      => $data['nombre'],
            'slug'        => $data['slug'],
            'descripcion' => $data['descripcion'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categorias_contenido WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}

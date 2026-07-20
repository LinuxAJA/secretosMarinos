<?php
/**
 * ============================================================================
 * ContentRepository.php — Acceso a contenidos educativos
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ContentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Listado público: solo publicados.
     *
     * @return list<array<string,mixed>>
     */
    public function listPublished(?string $categoriaSlug = null, ?string $q = null, int $limit = 12, int $offset = 0): array
    {
        $sql = 'SELECT ct.*, cat.nombre AS categoria_nombre, cat.slug AS categoria_slug,
                       u.nombre AS autor_nombre
                FROM contenidos ct
                LEFT JOIN categorias_contenido cat ON cat.id = ct.categoria_id
                LEFT JOIN usuarios u ON u.id = ct.autor_id
                WHERE ct.publicado = 1';
        $params = [];

        if ($categoriaSlug) {
            $sql .= ' AND cat.slug = :cat_slug';
            $params['cat_slug'] = $categoriaSlug;
        }
        if ($q) {
            // PDO nativo (EMULATE_PREPARES=false) no permite reutilizar el mismo :nombre
            // varias veces; por eso usamos :q_titulo, :q_resumen y :q_cuerpo.
            $like = '%' . $q . '%';
            $sql .= ' AND (ct.titulo LIKE :q_titulo OR ct.resumen LIKE :q_resumen OR ct.cuerpo LIKE :q_cuerpo)';
            $params['q_titulo'] = $like;
            $params['q_resumen'] = $like;
            $params['q_cuerpo'] = $like;
        }

        $sql .= ' ORDER BY ct.creado_en DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPublished(?string $categoriaSlug = null, ?string $q = null): int
    {
        $sql = 'SELECT COUNT(*)
                FROM contenidos ct
                LEFT JOIN categorias_contenido cat ON cat.id = ct.categoria_id
                WHERE ct.publicado = 1';
        $params = [];

        if ($categoriaSlug) {
            $sql .= ' AND cat.slug = :cat_slug';
            $params['cat_slug'] = $categoriaSlug;
        }
        if ($q) {
            $like = '%' . $q . '%';
            $sql .= ' AND (ct.titulo LIKE :q_titulo OR ct.resumen LIKE :q_resumen OR ct.cuerpo LIKE :q_cuerpo)';
            $params['q_titulo'] = $like;
            $params['q_resumen'] = $like;
            $params['q_cuerpo'] = $like;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Listado admin (todos). @return list<array<string,mixed>> */
    public function listAll(int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT ct.*, cat.nombre AS categoria_nombre, u.nombre AS autor_nombre
                FROM contenidos ct
                LEFT JOIN categorias_contenido cat ON cat.id = ct.categoria_id
                LEFT JOIN usuarios u ON u.id = ct.autor_id
                ORDER BY ct.creado_en DESC
                LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ct.*, cat.nombre AS categoria_nombre, cat.slug AS categoria_slug,
                    u.nombre AS autor_nombre
             FROM contenidos ct
             LEFT JOIN categorias_contenido cat ON cat.id = ct.categoria_id
             LEFT JOIN usuarios u ON u.id = ct.autor_id
             WHERE ct.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ct.*, cat.nombre AS categoria_nombre, cat.slug AS categoria_slug,
                    u.nombre AS autor_nombre
             FROM contenidos ct
             LEFT JOIN categorias_contenido cat ON cat.id = ct.categoria_id
             LEFT JOIN usuarios u ON u.id = ct.autor_id
             WHERE ct.slug = :slug AND ct.publicado = 1
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM contenidos WHERE slug = :slug';
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

    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE contenidos SET visitas = visitas + 1 WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO contenidos
                (categoria_id, autor_id, titulo, slug, resumen, cuerpo, nivel, publicado)
             VALUES
                (:categoria_id, :autor_id, :titulo, :slug, :resumen, :cuerpo, :nivel, :publicado)'
        );
        $stmt->execute([
            'categoria_id' => $data['categoria_id'],
            'autor_id'     => $data['autor_id'],
            'titulo'       => $data['titulo'],
            'slug'         => $data['slug'],
            'resumen'      => $data['resumen'],
            'cuerpo'       => $data['cuerpo'],
            'nivel'        => $data['nivel'],
            'publicado'    => $data['publicado'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE contenidos SET
                categoria_id = :categoria_id,
                titulo = :titulo,
                slug = :slug,
                resumen = :resumen,
                cuerpo = :cuerpo,
                nivel = :nivel,
                publicado = :publicado
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'           => $id,
            'categoria_id' => $data['categoria_id'],
            'titulo'       => $data['titulo'],
            'slug'         => $data['slug'],
            'resumen'      => $data['resumen'],
            'cuerpo'       => $data['cuerpo'],
            'nivel'        => $data['nivel'],
            'publicado'    => $data['publicado'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contenidos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM contenidos')->fetchColumn();
    }
}

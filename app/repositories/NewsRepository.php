<?php
/**
 * ============================================================================
 * NewsRepository.php — Acceso a noticias ambientales
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class NewsRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** @return list<array<string,mixed>> */
    public function listPublished(?string $categoria = null, ?string $q = null, int $limit = 12, int $offset = 0): array
    {
        $sql = 'SELECT n.*, u.nombre AS autor_nombre
                FROM noticias n
                LEFT JOIN usuarios u ON u.id = n.autor_id
                WHERE n.publicada = 1';
        $params = [];

        if ($categoria) {
            $sql .= ' AND n.categoria = :categoria';
            $params['categoria'] = $categoria;
        }
        if ($q) {
            // Parámetros con nombres distintos: PDO nativo no reutiliza :q
            $like = '%' . $q . '%';
            $sql .= ' AND (n.titulo LIKE :q_titulo OR n.resumen LIKE :q_resumen OR n.cuerpo LIKE :q_cuerpo)';
            $params['q_titulo'] = $like;
            $params['q_resumen'] = $like;
            $params['q_cuerpo'] = $like;
        }

        $sql .= ' ORDER BY COALESCE(n.publicado_en, n.creado_en) DESC
                  LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPublished(?string $categoria = null, ?string $q = null): int
    {
        $sql = 'SELECT COUNT(*) FROM noticias n WHERE n.publicada = 1';
        $params = [];
        if ($categoria) {
            $sql .= ' AND n.categoria = :categoria';
            $params['categoria'] = $categoria;
        }
        if ($q) {
            $like = '%' . $q . '%';
            $sql .= ' AND (n.titulo LIKE :q_titulo OR n.resumen LIKE :q_resumen OR n.cuerpo LIKE :q_cuerpo)';
            $params['q_titulo'] = $like;
            $params['q_resumen'] = $like;
            $params['q_cuerpo'] = $like;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string,mixed>> */
    public function listFeatured(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            'SELECT n.*, u.nombre AS autor_nombre
             FROM noticias n
             LEFT JOIN usuarios u ON u.id = n.autor_id
             WHERE n.publicada = 1 AND n.destacada = 1
             ORDER BY COALESCE(n.publicado_en, n.creado_en) DESC
             LIMIT ' . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return list<array<string,mixed>> */
    public function listAll(int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT n.*, u.nombre AS autor_nombre
                FROM noticias n
                LEFT JOIN usuarios u ON u.id = n.autor_id
                ORDER BY n.creado_en DESC
                LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT n.*, u.nombre AS autor_nombre
             FROM noticias n
             LEFT JOIN usuarios u ON u.id = n.autor_id
             WHERE n.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT n.*, u.nombre AS autor_nombre
             FROM noticias n
             LEFT JOIN usuarios u ON u.id = n.autor_id
             WHERE n.slug = :slug AND n.publicada = 1
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM noticias WHERE slug = :slug';
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
            'INSERT INTO noticias
                (autor_id, titulo, slug, resumen, cuerpo, categoria, destacada, publicada, publicado_en)
             VALUES
                (:autor_id, :titulo, :slug, :resumen, :cuerpo, :categoria, :destacada, :publicada, :publicado_en)'
        );
        $stmt->execute([
            'autor_id'     => $data['autor_id'],
            'titulo'       => $data['titulo'],
            'slug'         => $data['slug'],
            'resumen'      => $data['resumen'],
            'cuerpo'       => $data['cuerpo'],
            'categoria'    => $data['categoria'],
            'destacada'    => $data['destacada'],
            'publicada'    => $data['publicada'],
            'publicado_en' => $data['publicado_en'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE noticias SET
                titulo = :titulo,
                slug = :slug,
                resumen = :resumen,
                cuerpo = :cuerpo,
                categoria = :categoria,
                destacada = :destacada,
                publicada = :publicada,
                publicado_en = :publicado_en
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'           => $id,
            'titulo'       => $data['titulo'],
            'slug'         => $data['slug'],
            'resumen'      => $data['resumen'],
            'cuerpo'       => $data['cuerpo'],
            'categoria'    => $data['categoria'],
            'destacada'    => $data['destacada'],
            'publicada'    => $data['publicada'],
            'publicado_en' => $data['publicado_en'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM noticias WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM noticias')->fetchColumn();
    }

    /** @return list<string> */
    public function distinctCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT categoria FROM noticias
             WHERE categoria IS NOT NULL AND categoria <> ''
             ORDER BY categoria ASC"
        );
        return array_column($stmt->fetchAll(), 'categoria');
    }
}

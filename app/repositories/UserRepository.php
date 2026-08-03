<?php
/**
 * ============================================================================
 * UserRepository.php — Acceso a datos de usuarios (patrón Repository)
 * ============================================================================
 * ¿Para qué sirve el Repository?
 * Separa el SQL del resto de la aplicación. El controlador NO escribe
 * consultas; pide datos al repositorio. Si mañana cambias MySQL por otro
 * motor, solo tocas esta capa.
 *
 * Métodos:
 *   findByEmail()  → login / validar correo único
 *   findById()     → cargar perfil / sesión
 *   create()       → registro
 *   emailExists()  → validación de registro
 * ============================================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        // Obtiene la única conexión PDO (Singleton)
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Busca un usuario activo por correo, incluyendo el nombre del rol.
     * El JOIN evita una segunda consulta a la tabla roles.
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT u.id, u.rol_id, u.nombre, u.correo, u.password_hash,
                       u.avatar, u.puntos, u.activo, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE u.correo = :correo
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['correo' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Busca usuario por ID (útil para refrescar sesión o panel).
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT u.id, u.rol_id, u.nombre, u.correo, u.avatar,
                       u.puntos, u.activo, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE u.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * ¿Existe ya ese correo?
     * @param int|null $exceptId Ignora este usuario (útil al editar perfil)
     */
    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM usuarios WHERE correo = :correo';
        $params = ['correo' => $email];

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
     * Inserta un nuevo usuario y devuelve el ID generado.
     *
     * @param array{nombre:string,correo:string,password_hash:string,rol_id:int} $data
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO usuarios (rol_id, nombre, correo, password_hash, activo)
                VALUES (:rol_id, :nombre, :correo, :password_hash, 1)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'rol_id'        => $data['rol_id'],
            'nombre'        => $data['nombre'],
            'correo'        => $data['correo'],
            'password_hash' => $data['password_hash'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Devuelve solo el hash de contraseña (para verificar al cambiar password).
     */
    public function getPasswordHash(int $id): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT password_hash FROM usuarios WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $hash = $stmt->fetchColumn();

        return $hash !== false ? (string) $hash : null;
    }

    /**
     * Actualiza nombre y correo del propio usuario.
     *
     * @param array{nombre:string,correo:string} $data
     */
    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET nombre = :nombre, correo = :correo
             WHERE id = :id'
        );

        return $stmt->execute([
            'id'     => $id,
            'nombre' => $data['nombre'],
            'correo' => $data['correo'],
        ]);
    }

    /**
     * Actualiza el hash de contraseña.
     */
    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET password_hash = :password_hash WHERE id = :id'
        );

        return $stmt->execute([
            'id'            => $id,
            'password_hash' => $passwordHash,
        ]);
    }

    /**
     * Cuenta administradores activos (para no dejar el sistema sin admin).
     */
    public function countActiveAdmins(): int
    {
        $sql = 'SELECT COUNT(*)
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE r.nombre = :rol AND u.activo = 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['rol' => ROLE_ADMIN]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Elimina un usuario (hard delete).
     * Las FK con SET NULL / CASCADE preservan integridad en tablas hijas.
     */
    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM usuarios WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Lista usuarios activos para selects admin (ajuste de puntos).
     *
     * @return list<array<string,mixed>>
     */
    public function listActiveForSelect(): array
    {
        return $this->db
            ->query(
                'SELECT u.id, u.nombre, u.correo, u.puntos, r.nombre AS rol
                 FROM usuarios u
                 INNER JOIN roles r ON r.id = u.rol_id
                 WHERE u.activo = 1
                 ORDER BY u.nombre ASC'
            )
            ->fetchAll();
    }

    /**
     * Obtiene el id numérico de un rol por su nombre lógico
     * (admin | docente | estudiante).
     */
    public function getRoleIdByName(string $roleName): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM roles WHERE nombre = :nombre LIMIT 1'
        );
        $stmt->execute(['nombre' => $roleName]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }
}

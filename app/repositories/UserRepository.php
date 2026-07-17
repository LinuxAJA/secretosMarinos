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
     * ¿Existe ya ese correo? (registro)
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE correo = :correo LIMIT 1'
        );
        $stmt->execute(['correo' => $email]);

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

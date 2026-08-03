<?php
/**
 * ============================================================================
 * UserAdminService.php — Reglas de negocio para gestión admin de usuarios
 * ============================================================================
 * Paso 7: el administrador puede cambiar rol y estado activo.
 * No edita nombre, correo ni contraseña (eso vive en el perfil del propio usuario).
 *
 * Protecciones clave:
 *   - No dejar el sistema sin al menos un administrador activo.
 *   - Roles válidos solo: admin | docente | estudiante.
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\UserRepository;

class UserAdminService
{
    /** @var list<string> */
    private const ALLOWED_ROLES = [ROLE_ADMIN, ROLE_DOCENTE, ROLE_ESTUDIANTE];

    public function __construct(private ?UserRepository $users = null)
    {
        $this->users ??= new UserRepository();
    }

    /**
     * Actualiza rol y/o activo de un usuario existente.
     *
     * @param array{rol?:string,activo?:string|int} $input
     * @return array{ok:bool, errors?:array<string,string>}
     */
    public function updateRoleAndActive(int $userId, array $input): array
    {
        $user = $this->users->findById($userId);
        if (!$user) {
            return ['ok' => false, 'errors' => ['general' => 'Usuario no encontrado.']];
        }

        $rol = strtolower(trim((string) ($input['rol'] ?? '')));
        $activoRaw = $input['activo'] ?? null;
        $errors = [];

        // --- Validación de rol ---
        if (!in_array($rol, self::ALLOWED_ROLES, true)) {
            $errors['rol'] = 'Selecciona un rol válido.';
        }

        // --- Validación de activo (checkbox o select 0/1) ---
        if ($activoRaw === null || $activoRaw === '') {
            $errors['activo'] = 'Indica si la cuenta está activa.';
            $activo = 0;
        } else {
            $activo = (int) $activoRaw === 1 ? 1 : 0;
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $rolId = $this->users->getRoleIdByName($rol);
        if ($rolId === null) {
            return ['ok' => false, 'errors' => ['rol' => 'El rol indicado no existe en el sistema.']];
        }

        // --- Protección: único admin activo ---
        // Si el usuario actual es admin activo y el cambio lo dejaría de serlo
        // (desactivar o degradar rol), exige que quede al menos otro admin activo.
        if ($this->wouldRemoveLastActiveAdmin($user, $rol, $activo)) {
            return [
                'ok' => false,
                'errors' => [
                    'general' => 'No puedes desactivar ni degradar la única cuenta de administrador activa.',
                ],
            ];
        }

        $ok = $this->users->updateRoleAndActive($userId, $rolId, $activo);
        if (!$ok) {
            return ['ok' => false, 'errors' => ['general' => 'No se pudo guardar el cambio.']];
        }

        return ['ok' => true];
    }

    /**
     * ¿El cambio dejaría el sistema sin administradores activos?
     *
     * @param array<string,mixed> $user Fila actual (incluye rol y activo)
     */
    private function wouldRemoveLastActiveAdmin(array $user, string $newRole, int $newActivo): bool
    {
        $wasActiveAdmin = ((string) ($user['rol'] ?? '') === ROLE_ADMIN)
            && (int) ($user['activo'] ?? 0) === 1;

        if (!$wasActiveAdmin) {
            return false;
        }

        // Sigue siendo admin activo tras el cambio → no hay riesgo
        $staysActiveAdmin = ($newRole === ROLE_ADMIN) && ($newActivo === 1);
        if ($staysActiveAdmin) {
            return false;
        }

        // Solo bloquea si es el último admin activo del sistema
        return $this->users->countActiveAdmins() <= 1;
    }
}

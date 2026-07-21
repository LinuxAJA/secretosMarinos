<?php
/**
 * ============================================================================
 * ProfileService.php — Autogestión de perfil (complemento de auth)
 * ============================================================================
 * El usuario autenticado puede:
 *   - Actualizar nombre y correo (sin cambiar rol)
 *   - Cambiar contraseña (exige contraseña actual)
 *   - Eliminar su propia cuenta (hard delete + salvaguardas)
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\UserRepository;

class ProfileService
{
    private UserRepository $users;

    public function __construct(?UserRepository $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    /**
     * @return array{ok:bool, errors?:array<string,string>, user?:array}
     */
    public function updateProfile(int $userId, array $input): array
    {
        $nombre = trim($input['nombre'] ?? '');
        $correo = strtolower(trim($input['correo'] ?? ''));
        $errors = [];

        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $errors['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        } elseif (mb_strlen($nombre) > 100) {
            $errors['nombre'] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'Ingresa un correo válido.';
        } elseif ($this->users->emailExists($correo, $userId)) {
            $errors['correo'] = 'Este correo ya está en uso por otra cuenta.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->users->updateProfile($userId, [
            'nombre' => $nombre,
            'correo' => $correo,
        ]);

        $fresh = $this->users->findById($userId);
        if (!$fresh) {
            return ['ok' => false, 'errors' => [
                'general' => 'No se pudo actualizar el perfil.',
            ]];
        }

        return ['ok' => true, 'user' => $fresh];
    }

    /**
     * @return array{ok:bool, errors?:array<string,string>}
     */
    public function changePassword(int $userId, array $input): array
    {
        $current = $input['password_actual'] ?? '';
        $nuevo   = $input['password'] ?? '';
        $confirm = $input['password_confirm'] ?? '';
        $errors  = [];

        if ($current === '') {
            $errors['password_actual'] = 'Ingresa tu contraseña actual.';
        }

        if (mb_strlen($nuevo) < 8) {
            $errors['password'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }

        if ($nuevo !== $confirm) {
            $errors['password_confirm'] = 'Las contraseñas nuevas no coinciden.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $hash = $this->users->getPasswordHash($userId);
        if ($hash === null || !password_verify($current, $hash)) {
            return ['ok' => false, 'errors' => [
                'password_actual' => 'La contraseña actual no es correcta.',
            ]];
        }

        if (password_verify($nuevo, $hash)) {
            return ['ok' => false, 'errors' => [
                'password' => 'La nueva contraseña debe ser distinta a la actual.',
            ]];
        }

        $this->users->updatePassword($userId, password_hash($nuevo, PASSWORD_DEFAULT));

        return ['ok' => true];
    }

    /**
     * Elimina la cuenta del propio usuario (hard delete).
     *
     * Salvaguardas:
     *   - Contraseña actual correcta
     *   - Confirmación explícita (checkbox)
     *   - No permitir borrar al único admin activo
     *
     * @return array{ok:bool, errors?:array<string,string>}
     */
    public function deleteAccount(int $userId, array $input): array
    {
        $password = $input['password'] ?? '';
        $confirmed = !empty($input['confirmar_borrado']);
        $errors = [];

        if ($password === '') {
            $errors['password'] = 'Ingresa tu contraseña para confirmar.';
        }

        if (!$confirmed) {
            $errors['confirmar_borrado'] = 'Debes confirmar que entiendes que la acción es irreversible.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $user = $this->users->findById($userId);
        if (!$user || (int) $user['activo'] !== 1) {
            return ['ok' => false, 'errors' => [
                'general' => 'No se encontró la cuenta a eliminar.',
            ]];
        }

        $hash = $this->users->getPasswordHash($userId);
        if ($hash === null || !password_verify($password, $hash)) {
            return ['ok' => false, 'errors' => [
                'password' => 'La contraseña no es correcta.',
            ]];
        }

        if (($user['rol'] ?? '') === ROLE_ADMIN && $this->users->countActiveAdmins() <= 1) {
            return ['ok' => false, 'errors' => [
                'general' => 'No puedes eliminar la única cuenta de administrador activa. Crea otro admin antes de borrar esta.',
            ]];
        }

        if (!$this->users->deleteById($userId)) {
            return ['ok' => false, 'errors' => [
                'general' => 'No se pudo eliminar la cuenta. Intenta de nuevo.',
            ]];
        }

        return ['ok' => true];
    }
}

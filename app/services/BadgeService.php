<?php
/**
 * ============================================================================
 * BadgeService.php — Validación y CRUD del catálogo de insignias
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\BadgeRepository;

class BadgeService
{
    public function __construct(
        private ?BadgeRepository $badges = null
    ) {
        $this->badges ??= new BadgeRepository();
    }

    /**
     * @param array<string,mixed> $input
     * @return array{ok:bool,errors?:array<string,string>,id?:int}
     */
    public function create(array $input): array
    {
        $result = $this->validate($input);
        if (!$result['ok']) {
            return $result;
        }

        return ['ok' => true, 'id' => $this->badges->create($result['data'])];
    }

    /**
     * @param array<string,mixed> $input
     * @return array{ok:bool,errors?:array<string,string>}
     */
    public function update(int $id, array $input): array
    {
        if (!$this->badges->findById($id)) {
            return ['ok' => false, 'errors' => ['general' => 'Insignia no encontrada.']];
        }

        $result = $this->validate($input, $id);
        if (!$result['ok']) {
            return $result;
        }

        $this->badges->update($id, $result['data']);
        return ['ok' => true];
    }

    public function delete(int $id): bool
    {
        return $this->badges->findById($id)
            ? $this->badges->delete($id)
            : false;
    }

    /**
     * Valida el catálogo de insignias.
     * El listado público/admin se ordena por puntos_requeridos (única fuente de verdad).
     *
     * @param array<string,mixed> $input
     * @return array{ok:bool,errors?:array<string,string>,data?:array<string,mixed>}
     */
    private function validate(array $input, ?int $exceptId = null): array
    {
        $codigo = slugify(trim($input['codigo'] ?? '') ?: trim($input['nombre'] ?? ''));
        $nombre = trim($input['nombre'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $icono = trim($input['icono'] ?? '');
        $puntos = (int) ($input['puntos_requeridos'] ?? 0);
        $activa = !empty($input['activa']) ? 1 : 0;
        $errors = [];

        if ($codigo === '' || mb_strlen($codigo) > 60) {
            $errors['codigo'] = 'El código debe ser un slug de hasta 60 caracteres.';
        } elseif ($this->badges->codigoExists($codigo, $exceptId)) {
            $errors['codigo'] = 'Ya existe una insignia con ese código.';
        }
        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 120) {
            $errors['nombre'] = 'El nombre debe tener entre 3 y 120 caracteres.';
        }
        if (mb_strlen($descripcion) < 10 || mb_strlen($descripcion) > 255) {
            $errors['descripcion'] = 'La descripción debe tener entre 10 y 255 caracteres.';
        }
        if ($icono !== '' && mb_strlen($icono) > 120) {
            $errors['icono'] = 'El icono no puede superar 120 caracteres.';
        }
        if ($puntos < 0 || $puntos > 100000) {
            $errors['puntos_requeridos'] = 'Indica un umbral de puntos válido.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        return ['ok' => true, 'data' => [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'icono' => $icono !== '' ? $icono : null,
            'puntos_requeridos' => $puntos,
            'activa' => $activa,
        ]];
    }
}

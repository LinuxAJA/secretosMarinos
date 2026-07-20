<?php
/**
 * ============================================================================
 * CategoryService.php — Validación de categorías educativas
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\ContentCategoryRepository;

class CategoryService
{
    private ContentCategoryRepository $categories;

    public function __construct(?ContentCategoryRepository $categories = null)
    {
        $this->categories = $categories ?? new ContentCategoryRepository();
    }

    /**
     * @return array{ok:bool, errors?:array<string,string>, data?:array<string,mixed>}
     */
    public function validateAndPrepare(array $input, ?int $exceptId = null): array
    {
        $nombre = trim($input['nombre'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $slugInput = trim($input['slug'] ?? '');
        $slug = $slugInput !== '' ? slugify($slugInput) : slugify($nombre);
        $errors = [];

        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $errors['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        } elseif (mb_strlen($nombre) > 100) {
            $errors['nombre'] = 'El nombre no puede superar 100 caracteres.';
        }

        $baseSlug = $slug;
        $i = 2;
        while ($this->categories->slugExists($slug, $exceptId)) {
            $slug = $baseSlug . '-' . $i;
            $i++;
            if ($i > 50) {
                $errors['slug'] = 'No se pudo generar un slug único.';
                break;
            }
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        return [
            'ok' => true,
            'data' => [
                'nombre'      => $nombre,
                'slug'        => $slug,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
            ],
        ];
    }

    /** @return array{ok:bool, errors?:array<string,string>, id?:int} */
    public function create(array $input): array
    {
        $result = $this->validateAndPrepare($input);
        if (!$result['ok']) {
            return $result;
        }
        $id = $this->categories->create($result['data']);
        return ['ok' => true, 'id' => $id];
    }

    /** @return array{ok:bool, errors?:array<string,string>} */
    public function update(int $id, array $input): array
    {
        if (!$this->categories->findById($id)) {
            return ['ok' => false, 'errors' => ['general' => 'Categoría no encontrada.']];
        }
        $result = $this->validateAndPrepare($input, $id);
        if (!$result['ok']) {
            return $result;
        }
        $this->categories->update($id, $result['data']);
        return ['ok' => true];
    }
}

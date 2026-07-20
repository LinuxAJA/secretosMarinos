<?php
/**
 * ============================================================================
 * ContentService.php — Validación y reglas de contenidos educativos
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\ContentCategoryRepository;
use App\Repositories\ContentRepository;

class ContentService
{
    private ContentRepository $contents;
    private ContentCategoryRepository $categories;

    public function __construct(
        ?ContentRepository $contents = null,
        ?ContentCategoryRepository $categories = null
    ) {
        $this->contents = $contents ?? new ContentRepository();
        $this->categories = $categories ?? new ContentCategoryRepository();
    }

    /**
     * @return array{ok:bool, errors?:array<string,string>, data?:array<string,mixed>}
     */
    public function validateAndPrepare(array $input, ?int $exceptId = null): array
    {
        $titulo = trim($input['titulo'] ?? '');
        $resumen = trim($input['resumen'] ?? '');
        $cuerpo = trim($input['cuerpo'] ?? '');
        $nivel = $input['nivel'] ?? 'basico';
        $categoriaId = $input['categoria_id'] !== '' && $input['categoria_id'] !== null
            ? (int) $input['categoria_id']
            : null;
        $publicado = !empty($input['publicado']) ? 1 : 0;
        $slugInput = trim($input['slug'] ?? '');
        $slug = $slugInput !== '' ? slugify($slugInput) : slugify($titulo);

        $errors = [];
        $niveles = ['basico', 'intermedio', 'avanzado'];

        if ($titulo === '' || mb_strlen($titulo) < 5) {
            $errors['titulo'] = 'El título debe tener al menos 5 caracteres.';
        } elseif (mb_strlen($titulo) > 200) {
            $errors['titulo'] = 'El título no puede superar 200 caracteres.';
        }

        if ($cuerpo === '' || mb_strlen($cuerpo) < 20) {
            $errors['cuerpo'] = 'El contenido debe tener al menos 20 caracteres.';
        }

        if ($resumen !== '' && mb_strlen($resumen) > 500) {
            $errors['resumen'] = 'El resumen no puede superar 500 caracteres.';
        }

        if (!in_array($nivel, $niveles, true)) {
            $errors['nivel'] = 'Nivel no válido.';
        }

        if ($categoriaId !== null && !$this->categories->findById($categoriaId)) {
            $errors['categoria_id'] = 'La categoría seleccionada no existe.';
        }

        // Garantiza slug único añadiendo sufijo numérico si hace falta
        $baseSlug = $slug;
        $i = 2;
        while ($this->contents->slugExists($slug, $exceptId)) {
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
                'categoria_id' => $categoriaId,
                'titulo'       => $titulo,
                'slug'         => $slug,
                'resumen'      => $resumen !== '' ? $resumen : null,
                'cuerpo'       => $cuerpo,
                'nivel'        => $nivel,
                'publicado'    => $publicado,
            ],
        ];
    }

    /** @return array{ok:bool, errors?:array<string,string>, id?:int} */
    public function create(array $input, int $autorId): array
    {
        $result = $this->validateAndPrepare($input);
        if (!$result['ok']) {
            return $result;
        }

        $data = $result['data'];
        $data['autor_id'] = $autorId;
        $id = $this->contents->create($data);

        return ['ok' => true, 'id' => $id];
    }

    /** @return array{ok:bool, errors?:array<string,string>} */
    public function update(int $id, array $input): array
    {
        if (!$this->contents->findById($id)) {
            return ['ok' => false, 'errors' => ['general' => 'Contenido no encontrado.']];
        }

        $result = $this->validateAndPrepare($input, $id);
        if (!$result['ok']) {
            return $result;
        }

        $this->contents->update($id, $result['data']);
        return ['ok' => true];
    }
}

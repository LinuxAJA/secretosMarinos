<?php
/**
 * ============================================================================
 * NewsService.php — Validación y reglas de noticias
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\NewsRepository;

class NewsService
{
    private NewsRepository $news;

    public function __construct(?NewsRepository $news = null)
    {
        $this->news = $news ?? new NewsRepository();
    }

    /**
     * @return array{ok:bool, errors?:array<string,string>, data?:array<string,mixed>}
     */
    public function validateAndPrepare(array $input, ?int $exceptId = null): array
    {
        $titulo = trim($input['titulo'] ?? '');
        $resumen = trim($input['resumen'] ?? '');
        $cuerpo = trim($input['cuerpo'] ?? '');
        $categoria = trim($input['categoria'] ?? '');
        $publicada = !empty($input['publicada']) ? 1 : 0;
        $destacada = !empty($input['destacada']) ? 1 : 0;
        $slugInput = trim($input['slug'] ?? '');
        $slug = $slugInput !== '' ? slugify($slugInput) : slugify($titulo);

        $errors = [];

        if ($titulo === '' || mb_strlen($titulo) < 5) {
            $errors['titulo'] = 'El título debe tener al menos 5 caracteres.';
        } elseif (mb_strlen($titulo) > 200) {
            $errors['titulo'] = 'El título no puede superar 200 caracteres.';
        }

        if ($cuerpo === '' || mb_strlen($cuerpo) < 20) {
            $errors['cuerpo'] = 'El cuerpo debe tener al menos 20 caracteres.';
        }

        if ($resumen !== '' && mb_strlen($resumen) > 500) {
            $errors['resumen'] = 'El resumen no puede superar 500 caracteres.';
        }

        if ($categoria !== '' && mb_strlen($categoria) > 80) {
            $errors['categoria'] = 'La categoría no puede superar 80 caracteres.';
        }

        $baseSlug = $slug;
        $i = 2;
        while ($this->news->slugExists($slug, $exceptId)) {
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

        // Si se publica y aún no hay fecha, se asigna ahora
        $publicadoEn = null;
        if ($publicada) {
            $publicadoEn = date('Y-m-d H:i:s');
            // En update, si ya tenía fecha, la conserva el controlador/servicio vía existing
            if (!empty($input['publicado_en_existente'])) {
                $publicadoEn = $input['publicado_en_existente'];
            }
        }

        return [
            'ok' => true,
            'data' => [
                'titulo'       => $titulo,
                'slug'         => $slug,
                'resumen'      => $resumen !== '' ? $resumen : null,
                'cuerpo'       => $cuerpo,
                'categoria'    => $categoria !== '' ? $categoria : null,
                'destacada'    => $destacada,
                'publicada'    => $publicada,
                'publicado_en' => $publicadoEn,
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
        $id = $this->news->create($data);
        return ['ok' => true, 'id' => $id];
    }

    /** @return array{ok:bool, errors?:array<string,string>} */
    public function update(int $id, array $input): array
    {
        $existing = $this->news->findById($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Noticia no encontrada.']];
        }

        // Conserva fecha de publicación original si ya estaba publicada
        if (!empty($existing['publicado_en'])) {
            $input['publicado_en_existente'] = $existing['publicado_en'];
        }

        $result = $this->validateAndPrepare($input, $id);
        if (!$result['ok']) {
            return $result;
        }

        $this->news->update($id, $result['data']);
        return ['ok' => true];
    }
}

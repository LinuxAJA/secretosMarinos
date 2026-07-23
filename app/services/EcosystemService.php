<?php
/**
 * ============================================================================
 * EcosystemService.php — Reglas de negocio de ecosistemas
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\EcosystemRepository;
use Throwable;

class EcosystemService
{
    public function __construct(
        private ?EcosystemRepository $ecosystems = null,
        private ?ImageUploadService $images = null
    ) {
        $this->ecosystems ??= new EcosystemRepository();
        $this->images ??= new ImageUploadService();
    }

    /** @return array{ok:bool,errors?:array<string,string>,id?:int} */
    public function create(array $input, ?array $file): array
    {
        $result = $this->validate($input);
        if (!$result['ok']) {
            return $result;
        }

        $upload = $this->images->upload($file, 'ecosistemas');
        if (!$upload['ok']) {
            return ['ok' => false, 'errors' => ['imagen' => $upload['error']]];
        }

        $data = $result['data'];
        $data['imagen'] = $upload['path'];

        try {
            return ['ok' => true, 'id' => $this->ecosystems->create($data)];
        } catch (Throwable $e) {
            if ($upload['uploaded'] ?? false) {
                $this->images->delete($upload['path']);
            }
            throw $e;
        }
    }

    /** @return array{ok:bool,errors?:array<string,string>} */
    public function update(int $id, array $input, ?array $file): array
    {
        $existing = $this->ecosystems->findById($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Ecosistema no encontrado.']];
        }

        $result = $this->validate($input, $id);
        if (!$result['ok']) {
            return $result;
        }

        $upload = $this->images->upload($file, 'ecosistemas');
        if (!$upload['ok']) {
            return ['ok' => false, 'errors' => ['imagen' => $upload['error']]];
        }

        $remove = !empty($input['eliminar_imagen']);
        $newImage = $existing['imagen'];
        if ($upload['uploaded'] ?? false) {
            $newImage = $upload['path'];
        } elseif ($remove) {
            $newImage = null;
        }

        $data = $result['data'];
        $data['imagen'] = $newImage;

        try {
            $this->ecosystems->update($id, $data);
        } catch (Throwable $e) {
            if ($upload['uploaded'] ?? false) {
                $this->images->delete($upload['path']);
            }
            throw $e;
        }

        if (($upload['uploaded'] ?? false) || $remove) {
            $this->images->delete($existing['imagen']);
        }

        return ['ok' => true];
    }

    public function delete(int $id): bool
    {
        $existing = $this->ecosystems->findById($id);
        if (!$existing) {
            return false;
        }

        $deleted = $this->ecosystems->delete($id);
        if ($deleted) {
            $this->images->delete($existing['imagen']);
        }
        return $deleted;
    }

    /** @return array{ok:bool,errors?:array<string,string>,data?:array<string,mixed>} */
    private function validate(array $input, ?int $exceptId = null): array
    {
        $nombre = trim($input['nombre'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $funcion = trim($input['funcion_ecologica'] ?? '');
        $amenazas = trim($input['amenazas'] ?? '');
        $practicas = trim($input['buenas_practicas'] ?? '');
        $publicado = !empty($input['publicado']) ? 1 : 0;
        $slug = slugify(trim($input['slug'] ?? '') ?: $nombre);
        $errors = [];

        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 120) {
            $errors['nombre'] = 'El nombre debe tener entre 3 y 120 caracteres.';
        }
        if (mb_strlen($descripcion) < 20) {
            $errors['descripcion'] = 'La descripción debe tener al menos 20 caracteres.';
        }

        $baseSlug = $slug;
        $suffix = 2;
        while ($this->ecosystems->slugExists($slug, $exceptId)) {
            $slug = $baseSlug . '-' . $suffix++;
            if ($suffix > 50) {
                $errors['slug'] = 'No se pudo generar un slug único.';
                break;
            }
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        return ['ok' => true, 'data' => [
            'nombre' => $nombre,
            'slug' => $slug,
            'descripcion' => $descripcion,
            'funcion_ecologica' => $funcion !== '' ? $funcion : null,
            'amenazas' => $amenazas !== '' ? $amenazas : null,
            'buenas_practicas' => $practicas !== '' ? $practicas : null,
            'publicado' => $publicado,
        ]];
    }
}

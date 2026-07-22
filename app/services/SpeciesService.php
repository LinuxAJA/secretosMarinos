<?php
/**
 * ============================================================================
 * SpeciesService.php — Reglas de negocio de especies marinas
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\EcosystemRepository;
use App\Repositories\SpeciesRepository;
use Throwable;

class SpeciesService
{
    public const CONSERVATION_STATES = [
        'No evaluado',
        'Datos insuficientes',
        'Preocupación menor',
        'Casi amenazada',
        'Vulnerable',
        'En peligro',
        'En peligro crítico',
        'Extinta en estado silvestre',
        'Extinta',
    ];

    public function __construct(
        private ?SpeciesRepository $species = null,
        private ?EcosystemRepository $ecosystems = null,
        private ?ImageUploadService $images = null
    ) {
        $this->species ??= new SpeciesRepository();
        $this->ecosystems ??= new EcosystemRepository();
        $this->images ??= new ImageUploadService();
    }

    /** @return array{ok:bool,errors?:array<string,string>,id?:int} */
    public function create(array $input, ?array $file, int $authorId): array
    {
        $result = $this->validate($input);
        if (!$result['ok']) {
            return $result;
        }

        $upload = $this->images->upload($file, 'especies');
        if (!$upload['ok']) {
            return ['ok' => false, 'errors' => ['imagen' => $upload['error']]];
        }

        $data = $result['data'];
        $data['autor_id'] = $authorId;
        $data['imagen'] = $upload['path'];

        try {
            return ['ok' => true, 'id' => $this->species->create($data)];
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
        $existing = $this->species->findById($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Especie no encontrada.']];
        }

        $result = $this->validate($input, $id);
        if (!$result['ok']) {
            return $result;
        }

        $upload = $this->images->upload($file, 'especies');
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
            $this->species->update($id, $data);
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
        $existing = $this->species->findById($id);
        if (!$existing) {
            return false;
        }

        $deleted = $this->species->delete($id);
        if ($deleted) {
            $this->images->delete($existing['imagen']);
        }
        return $deleted;
    }

    /** @return array{ok:bool,errors?:array<string,string>,data?:array<string,mixed>} */
    private function validate(array $input, ?int $exceptId = null): array
    {
        $common = trim($input['nombre_comun'] ?? '');
        $scientific = trim($input['nombre_cientifico'] ?? '');
        $description = trim($input['descripcion'] ?? '');
        $classification = trim($input['clasificacion'] ?? '');
        $habitat = trim($input['habitat'] ?? '');
        $distribution = trim($input['distribucion'] ?? '');
        $threats = trim($input['amenazas'] ?? '');
        $conservation = trim($input['estado_conservacion'] ?? '');
        $ecosystemId = ($input['ecosistema_id'] ?? '') !== ''
            ? (int) $input['ecosistema_id']
            : null;
        $published = !empty($input['publicado']) ? 1 : 0;
        $slug = slugify(trim($input['slug'] ?? '') ?: $common);
        $errors = [];

        if (mb_strlen($common) < 2 || mb_strlen($common) > 150) {
            $errors['nombre_comun'] = 'El nombre común debe tener entre 2 y 150 caracteres.';
        }
        if (mb_strlen($scientific) < 2 || mb_strlen($scientific) > 150) {
            $errors['nombre_cientifico'] = 'El nombre científico debe tener entre 2 y 150 caracteres.';
        }
        if (mb_strlen($description) < 20) {
            $errors['descripcion'] = 'La descripción debe tener al menos 20 caracteres.';
        }
        if ($ecosystemId !== null && !$this->ecosystems->findById($ecosystemId)) {
            $errors['ecosistema_id'] = 'El ecosistema seleccionado no existe.';
        }
        if ($conservation !== '' && !in_array($conservation, self::CONSERVATION_STATES, true)) {
            $errors['estado_conservacion'] = 'Selecciona un estado de conservación válido.';
        }

        $baseSlug = $slug;
        $suffix = 2;
        while ($this->species->slugExists($slug, $exceptId)) {
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
            'ecosistema_id' => $ecosystemId,
            'nombre_comun' => $common,
            'nombre_cientifico' => $scientific,
            'slug' => $slug,
            'clasificacion' => $classification !== '' ? $classification : null,
            'habitat' => $habitat !== '' ? $habitat : null,
            'distribucion' => $distribution !== '' ? $distribution : null,
            'amenazas' => $threats !== '' ? $threats : null,
            'estado_conservacion' => $conservation !== '' ? $conservation : null,
            'descripcion' => $description,
            'publicado' => $published,
        ]];
    }
}

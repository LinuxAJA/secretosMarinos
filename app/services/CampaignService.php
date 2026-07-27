<?php
/**
 * ============================================================================
 * CampaignService.php — Reglas de negocio de campañas ambientales
 * ============================================================================
 * Valida datos, aplica la regla de motivo obligatorio al cancelar,
 * genera slugs únicos y coordina la subida segura de imágenes.
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\CampaignRepository;
use Throwable;

class CampaignService
{
    /** Estados válidos del ciclo de vida de una campaña. */
    public const STATES = ['borrador', 'activa', 'finalizada', 'cancelada'];

    /** Longitud mínima del motivo de cancelación. */
    private const CANCEL_REASON_MIN = 15;

    /** Longitud máxima del motivo de cancelación. */
    private const CANCEL_REASON_MAX = 500;

    public function __construct(
        private ?CampaignRepository $campaigns = null,
        private ?ImageUploadService $images = null
    ) {
        $this->campaigns ??= new CampaignRepository();
        $this->images ??= new ImageUploadService();
    }

    /**
     * Crea una campaña asignando responsable_id al usuario actual.
     *
     * @param array<string,mixed>      $input Datos del formulario
     * @param array<string,mixed>|null $file  Elemento de $_FILES['imagen']
     * @param int                      $responsableId ID del creador
     * @return array{ok:bool,errors?:array<string,string>,id?:int}
     */
    public function create(array $input, ?array $file, int $responsableId): array
    {
        $result = $this->validate($input);
        if (!$result['ok']) {
            return $result;
        }

        $upload = $this->images->upload($file, 'campanias');
        if (!$upload['ok']) {
            return ['ok' => false, 'errors' => ['imagen' => $upload['error']]];
        }

        $data = $result['data'];
        $data['responsable_id'] = $responsableId;
        $data['imagen'] = $upload['path'];

        try {
            return ['ok' => true, 'id' => $this->campaigns->create($data)];
        } catch (Throwable $e) {
            if ($upload['uploaded'] ?? false) {
                $this->images->delete($upload['path']);
            }
            throw $e;
        }
    }

    /**
     * Actualiza una campaña existente (incluye regla de cancelación).
     *
     * @param array<string,mixed>      $input
     * @param array<string,mixed>|null $file
     * @return array{ok:bool,errors?:array<string,string>}
     */
    public function update(int $id, array $input, ?array $file): array
    {
        $existing = $this->campaigns->findById($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Campaña no encontrada.']];
        }

        $result = $this->validate($input, $id, $existing);
        if (!$result['ok']) {
            return $result;
        }

        $upload = $this->images->upload($file, 'campanias');
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
            $this->campaigns->update($id, $data);
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

    /**
     * Elimina la campaña y su imagen asociada si existe.
     */
    public function delete(int $id): bool
    {
        $existing = $this->campaigns->findById($id);
        if (!$existing) {
            return false;
        }

        $deleted = $this->campaigns->delete($id);
        if ($deleted) {
            $this->images->delete($existing['imagen']);
        }
        return $deleted;
    }

    /**
     * Valida y normaliza el payload de campaña.
     *
     * Regla clave: si estado = cancelada, motivo_cancelacion es obligatorio
     * (mín. CANCEL_REASON_MIN caracteres). Al cancelar se fija cancelada_en;
     * si se reactiva, se conserva el último motivo como historial.
     *
     * @param array<string,mixed>      $input
     * @param array<string,mixed>|null $existing Fila actual (edición)
     * @return array{ok:bool,errors?:array<string,string>,data?:array<string,mixed>}
     */
    private function validate(array $input, ?int $exceptId = null, ?array $existing = null): array
    {
        $titulo = trim($input['titulo'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $objetivo = trim($input['objetivo'] ?? '');
        $estado = trim($input['estado'] ?? 'borrador');
        $motivo = trim($input['motivo_cancelacion'] ?? '');
        $fechaInicio = trim($input['fecha_inicio'] ?? '');
        $fechaFin = trim($input['fecha_fin'] ?? '');
        $slug = slugify(trim($input['slug'] ?? '') ?: $titulo);
        $errors = [];

        if (mb_strlen($titulo) < 3 || mb_strlen($titulo) > 200) {
            $errors['titulo'] = 'El título debe tener entre 3 y 200 caracteres.';
        }
        if (mb_strlen($descripcion) < 20) {
            $errors['descripcion'] = 'La descripción debe tener al menos 20 caracteres.';
        }
        if ($objetivo !== '' && mb_strlen($objetivo) > 500) {
            $errors['objetivo'] = 'El objetivo no puede superar 500 caracteres.';
        }
        if (!in_array($estado, self::STATES, true)) {
            $errors['estado'] = 'Selecciona un estado válido.';
        }

        if ($fechaInicio !== '' && !$this->isValidDate($fechaInicio)) {
            $errors['fecha_inicio'] = 'La fecha de inicio no es válida.';
        }
        if ($fechaFin !== '' && !$this->isValidDate($fechaFin)) {
            $errors['fecha_fin'] = 'La fecha de fin no es válida.';
        }
        if (
            $fechaInicio !== ''
            && $fechaFin !== ''
            && empty($errors['fecha_inicio'])
            && empty($errors['fecha_fin'])
            && $fechaFin < $fechaInicio
        ) {
            $errors['fecha_fin'] = 'La fecha de fin no puede ser anterior a la de inicio.';
        }

        // --- Regla de negocio: cancelación justificada ---
        $motivoFinal = $existing['motivo_cancelacion'] ?? null;
        $canceladaEn = $existing['cancelada_en'] ?? null;

        if ($estado === 'cancelada') {
            if (mb_strlen($motivo) < self::CANCEL_REASON_MIN) {
                $errors['motivo_cancelacion'] = 'Al cancelar debes indicar un motivo de al menos '
                    . self::CANCEL_REASON_MIN . ' caracteres.';
            } elseif (mb_strlen($motivo) > self::CANCEL_REASON_MAX) {
                $errors['motivo_cancelacion'] = 'El motivo no puede superar '
                    . self::CANCEL_REASON_MAX . ' caracteres.';
            } else {
                $motivoFinal = $motivo;
                // Solo fija la marca temporal en la primera cancelación o si cambia el motivo
                $wasCancelled = ($existing['estado'] ?? '') === 'cancelada';
                $canceladaEn = $wasCancelled && !empty($existing['cancelada_en'])
                    ? $existing['cancelada_en']
                    : date('Y-m-d H:i:s');
            }
        } elseif ($motivo !== '') {
            // Permite conservar/actualizar el historial aunque ya no esté cancelada
            if (mb_strlen($motivo) > self::CANCEL_REASON_MAX) {
                $errors['motivo_cancelacion'] = 'El motivo no puede superar '
                    . self::CANCEL_REASON_MAX . ' caracteres.';
            } else {
                $motivoFinal = $motivo;
            }
        }

        $baseSlug = $slug;
        $suffix = 2;
        while ($this->campaigns->slugExists($slug, $exceptId)) {
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
            'titulo' => $titulo,
            'slug' => $slug,
            'descripcion' => $descripcion,
            'objetivo' => $objetivo !== '' ? $objetivo : null,
            'fecha_inicio' => $fechaInicio !== '' ? $fechaInicio : null,
            'fecha_fin' => $fechaFin !== '' ? $fechaFin : null,
            'estado' => $estado,
            'motivo_cancelacion' => $motivoFinal,
            'cancelada_en' => $estado === 'cancelada' ? $canceladaEn : ($existing['cancelada_en'] ?? null),
        ]];
    }

    /**
     * Valida una fecha en formato YYYY-MM-DD.
     */
    private function isValidDate(string $date): bool
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        return $dt !== false && $dt->format('Y-m-d') === $date;
    }
}

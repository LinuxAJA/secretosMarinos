<?php
/**
 * ============================================================================
 * ReportService.php — Reglas de negocio de reportes ambientales
 * ============================================================================
 * Separa creación/edición del ciudadano (ticket pendiente) de la
 * revisión del staff (cambio de estado + notas + revisor_id).
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\ReportRepository;
use Throwable;

class ReportService
{
    /** Tipos de incidencia permitidos (coinciden con el ENUM de BD). */
    public const TYPES = [
        'contaminacion' => 'Contaminación',
        'residuos' => 'Residuos',
        'fauna_afectada' => 'Fauna afectada',
        'deterioro' => 'Deterioro',
        'otro' => 'Otro',
    ];

    /** Estados del ciclo de revisión. */
    public const STATES = [
        'pendiente' => 'Pendiente',
        'en_revision' => 'En revisión',
        'resuelto' => 'Resuelto',
    ];

    public function __construct(
        private ?ReportRepository $reports = null,
        private ?ImageUploadService $images = null
    ) {
        $this->reports ??= new ReportRepository();
        $this->images ??= new ImageUploadService();
    }

    /**
     * Crea un reporte ciudadano. El estado inicial siempre es pendiente.
     *
     * @param array<string,mixed>      $input
     * @param array<string,mixed>|null $file
     * @param int                      $userId Autor del reporte
     * @return array{ok:bool,errors?:array<string,string>,id?:int}
     */
    public function create(array $input, ?array $file, int $userId): array
    {
        $result = $this->validateCitizenInput($input);
        if (!$result['ok']) {
            return $result;
        }

        $upload = $this->images->upload($file, 'reportes');
        if (!$upload['ok']) {
            return ['ok' => false, 'errors' => ['imagen' => $upload['error']]];
        }

        $data = $result['data'];
        $data['usuario_id'] = $userId;
        $data['estado'] = 'pendiente';
        $data['imagen'] = $upload['path'];

        try {
            return ['ok' => true, 'id' => $this->reports->create($data)];
        } catch (Throwable $e) {
            if ($upload['uploaded'] ?? false) {
                $this->images->delete($upload['path']);
            }
            throw $e;
        }
    }

    /**
     * Actualiza un reporte propio mientras esté pendiente.
     *
     * @param array<string,mixed>      $input
     * @param array<string,mixed>|null $file
     * @return array{ok:bool,errors?:array<string,string>}
     */
    public function updateByOwner(int $id, array $input, ?array $file): array
    {
        $existing = $this->reports->findById($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Reporte no encontrado.']];
        }
        if (($existing['estado'] ?? '') !== 'pendiente') {
            return ['ok' => false, 'errors' => [
                'general' => 'Solo puedes editar reportes en estado pendiente.',
            ]];
        }

        $result = $this->validateCitizenInput($input);
        if (!$result['ok']) {
            return $result;
        }

        $upload = $this->images->upload($file, 'reportes');
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
            $this->reports->updateByOwner($id, $data);
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
     * Revisión de staff: cambia estado, guarda nota y registra revisor.
     *
     * @param array<string,mixed> $input estado, notas_revision
     * @param int                 $reviewerId Usuario admin/docente
     * @return array{ok:bool,errors?:array<string,string>}
     */
    public function review(int $id, array $input, int $reviewerId): array
    {
        $existing = $this->reports->findById($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Reporte no encontrado.']];
        }

        $estado = trim($input['estado'] ?? '');
        $notas = trim($input['notas_revision'] ?? '');
        $errors = [];

        if (!isset(self::STATES[$estado])) {
            $errors['estado'] = 'Selecciona un estado de revisión válido.';
        }
        if ($notas !== '' && mb_strlen($notas) > 2000) {
            $errors['notas_revision'] = 'Las notas no pueden superar 2000 caracteres.';
        }
        // Se recomienda nota al avanzar a en_revision o resuelto
        if (in_array($estado, ['en_revision', 'resuelto'], true) && mb_strlen($notas) < 5) {
            $errors['notas_revision'] = 'Indica una nota breve al pasar a revisión o resolver.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->reports->updateReview($id, [
            'estado' => $estado,
            'notas_revision' => $notas !== '' ? $notas : null,
            'revisor_id' => $reviewerId,
        ]);

        return ['ok' => true];
    }

    /**
     * Elimina el reporte y su evidencia fotográfica.
     */
    public function delete(int $id): bool
    {
        $existing = $this->reports->findById($id);
        if (!$existing) {
            return false;
        }

        $deleted = $this->reports->delete($id);
        if ($deleted) {
            $this->images->delete($existing['imagen']);
        }
        return $deleted;
    }

    /**
     * Validación del formulario ciudadano (crear / editar pendiente).
     *
     * @param array<string,mixed> $input
     * @return array{ok:bool,errors?:array<string,string>,data?:array<string,mixed>}
     */
    private function validateCitizenInput(array $input): array
    {
        $titulo = trim($input['titulo'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $ubicacion = trim($input['ubicacion'] ?? '');
        $tipo = trim($input['tipo'] ?? 'otro');
        $errors = [];

        if (mb_strlen($titulo) < 5 || mb_strlen($titulo) > 180) {
            $errors['titulo'] = 'El título debe tener entre 5 y 180 caracteres.';
        }
        if (mb_strlen($descripcion) < 20) {
            $errors['descripcion'] = 'La descripción debe tener al menos 20 caracteres.';
        }
        if ($ubicacion !== '' && mb_strlen($ubicacion) > 255) {
            $errors['ubicacion'] = 'La ubicación no puede superar 255 caracteres.';
        }
        if (!isset(self::TYPES[$tipo])) {
            $errors['tipo'] = 'Selecciona un tipo de reporte válido.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        return ['ok' => true, 'data' => [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'ubicacion' => $ubicacion !== '' ? $ubicacion : null,
            'tipo' => $tipo,
        ]];
    }
}

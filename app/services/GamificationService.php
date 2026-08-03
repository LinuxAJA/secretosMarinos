<?php
/**
 * ============================================================================
 * GamificationService.php — Motor de puntos e insignias
 * ============================================================================
 * Único dueño de:
 *   - sumar/restar puntos con historial e idempotencia por referencia
 *   - evaluar y otorgar insignias por umbral
 *
 * Los módulos (p. ej. ReportService) solo invocan este servicio;
 * no escriben SQL de gamificación directamente.
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\BadgeRepository;
use App\Repositories\PointsRepository;
use App\Repositories\UserRepository;
use Throwable;

class GamificationService
{
    /** Puntos al crear un reporte ambiental. */
    public const POINTS_REPORT_CREATED = 10;

    /** Bono al autor cuando su reporte pasa a resuelto. */
    public const POINTS_REPORT_RESOLVED = 15;

    /** Tipos de referencia para idempotencia. */
    public const REF_REPORT_CREATED = 'reporte_creado';
    public const REF_REPORT_RESOLVED = 'reporte_resuelto';
    public const REF_MANUAL = 'ajuste_manual';

    public function __construct(
        private ?PointsRepository $points = null,
        private ?BadgeRepository $badges = null,
        private ?UserRepository $users = null
    ) {
        $this->points ??= new PointsRepository();
        $this->badges ??= new BadgeRepository();
        $this->users ??= new UserRepository();
    }

    /**
     * Suma puntos por creación de reporte (+10), una sola vez por reporte.
     *
     * @return array{ok:bool,awarded:bool,newTotal?:int,newBadges?:list<array<string,mixed>>,errors?:array<string,string>}
     */
    public function onReportCreated(int $userId, int $reportId): array
    {
        return $this->awardPoints(
            $userId,
            self::POINTS_REPORT_CREATED,
            'Reporte ambiental creado',
            self::REF_REPORT_CREATED,
            $reportId
        );
    }

    /**
     * Suma bono al autor cuando el reporte se resuelve (+15), una sola vez.
     *
     * @return array{ok:bool,awarded:bool,newTotal?:int,newBadges?:list<array<string,mixed>>,errors?:array<string,string>}
     */
    public function onReportResolved(int $authorId, int $reportId): array
    {
        if ($authorId <= 0) {
            return ['ok' => true, 'awarded' => false];
        }

        return $this->awardPoints(
            $authorId,
            self::POINTS_REPORT_RESOLVED,
            'Reporte ambiental resuelto',
            self::REF_REPORT_RESOLVED,
            $reportId
        );
    }

    /**
     * Ajuste manual de puntos (admin). Puede ser positivo o negativo.
     * No permite dejar el saldo por debajo de cero.
     *
     * @return array{ok:bool,awarded?:bool,newTotal?:int,newBadges?:list<array<string,mixed>>,errors?:array<string,string>}
     */
    public function adjustPoints(int $userId, int $delta, string $motivo): array
    {
        $motivo = trim($motivo);
        $errors = [];

        if ($delta === 0) {
            $errors['puntos'] = 'Indica un valor distinto de cero.';
        }
        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 255) {
            $errors['motivo'] = 'El motivo debe tener entre 5 y 255 caracteres.';
        }
        if (!$this->users->findById($userId)) {
            $errors['usuario_id'] = 'Usuario no encontrado.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $balance = $this->points->getBalance($userId);
        if ($balance + $delta < 0) {
            return ['ok' => false, 'errors' => [
                'puntos' => "El saldo no puede quedar negativo (actual: {$balance}).",
            ]];
        }

        // Sin referencia_id fija: cada ajuste es un movimiento independiente
        return $this->awardPoints(
            $userId,
            $delta,
            'Ajuste manual: ' . $motivo,
            self::REF_MANUAL,
            null
        );
    }

    /**
     * Otorga (o resta) puntos, registra historial y evalúa insignias.
     *
     * Idempotencia: si refType + refId ya existen para el usuario, no vuelve a sumar.
     *
     * @param int         $userId  Beneficiario
     * @param int         $amount  Delta (puede ser negativo en ajustes)
     * @param string      $motivo  Texto visible en el historial
     * @param string|null $refType Tipo de evento (reporte_creado, etc.)
     * @param int|null    $refId   ID de la entidad origen (null = no idempotente por id)
     * @return array{ok:bool,awarded:bool,newTotal?:int,newBadges?:list<array<string,mixed>>,errors?:array<string,string>}
     */
    public function awardPoints(
        int $userId,
        int $amount,
        string $motivo,
        ?string $refType = null,
        ?int $refId = null
    ): array {
        if ($userId <= 0 || $amount === 0) {
            return ['ok' => false, 'awarded' => false, 'errors' => [
                'general' => 'Parámetros de puntos inválidos.',
            ]];
        }

        // Idempotencia por referencia concreta
        if ($refType !== null && $refId !== null
            && $this->points->referenceExists($userId, $refType, $refId)
        ) {
            return [
                'ok' => true,
                'awarded' => false,
                'newTotal' => $this->points->getBalance($userId),
                'newBadges' => [],
            ];
        }

        $pdo = $this->points->getConnection();

        try {
            $pdo->beginTransaction();

            $this->points->insertMovement($userId, $amount, $motivo, $refType, $refId);
            $this->points->applyDelta($userId, $amount);
            $newTotal = $this->points->getBalance($userId);
            $newBadges = $this->evaluateAndGrantBadges($userId, $newTotal);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Duplicado por índice único (carrera): tratar como no awarded
            if (str_contains($e->getMessage(), 'uk_puntos_referencia')
                || str_contains($e->getMessage(), 'Duplicate')
            ) {
                return [
                    'ok' => true,
                    'awarded' => false,
                    'newTotal' => $this->points->getBalance($userId),
                    'newBadges' => [],
                ];
            }
            throw $e;
        }

        return [
            'ok' => true,
            'awarded' => true,
            'newTotal' => $newTotal,
            'newBadges' => $newBadges,
        ];
    }

    /**
     * Otorga todas las insignias activas cuyo umbral el saldo ya cumple.
     * Las ya poseídas no se revocan ni se reinsertan.
     *
     * @return list<array<string,mixed>> Insignias recién otorgadas
     */
    public function evaluateAndGrantBadges(int $userId, ?int $points = null): array
    {
        $points ??= $this->points->getBalance($userId);
        $eligible = $this->badges->eligibleForUser($userId, $points);
        $granted = [];

        foreach ($eligible as $badge) {
            if ($this->badges->grant($userId, (int) $badge['id'])) {
                $granted[] = $badge;
            }
        }

        return $granted;
    }

    /**
     * Resumen para el panel del usuario.
     *
     * @return array{
     *   puntos:int,
     *   badges:list<array<string,mixed>>,
     *   next:?array<string,mixed>,
     *   history:list<array<string,mixed>>,
     *   progress:int
     * }
     */
    public function panelSummary(int $userId): array
    {
        $puntos = $this->points->getBalance($userId);
        $next = $this->badges->nextForUser($userId, $puntos);
        $progress = 100;

        if ($next) {
            $target = (int) $next['puntos_requeridos'];
            $progress = $target > 0
                ? (int) min(100, round(($puntos / $target) * 100))
                : 100;
        }

        return [
            'puntos' => $puntos,
            'badges' => $this->badges->listByUser($userId),
            'next' => $next,
            'history' => $this->points->listByUser($userId, 10),
            'progress' => $progress,
        ];
    }

    /**
     * Genera un mensaje flash amigable si hubo insignias nuevas.
     *
     * @param list<array<string,mixed>> $newBadges
     */
    public static function flashNewBadges(array $newBadges): void
    {
        if (!$newBadges) {
            return;
        }

        $names = array_map(
            static fn(array $b): string => (string) ($b['nombre'] ?? 'Insignia'),
            $newBadges
        );
        flash(
            'success',
            '¡Nueva insignia desbloqueada!: ' . implode(', ', $names)
        );
    }
}

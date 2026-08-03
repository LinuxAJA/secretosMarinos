<?php
/**
 * ============================================================================
 * RankingController.php — Ranking público de puntos ecológicos
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\PointsRepository;

class RankingController extends Controller
{
    public function __construct(
        private ?PointsRepository $points = null
    ) {
        $this->points ??= new PointsRepository();
    }

    /** GET /ranking */
    public function index(): void
    {
        $this->render('pages/ranking/index', [
            'pageTitle' => 'Ranking ecológico',
            'items' => $this->points->ranking(10),
        ]);
    }
}

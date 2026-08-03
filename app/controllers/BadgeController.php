<?php
/**
 * ============================================================================
 * BadgeController.php — Catálogo público de insignias
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\BadgeRepository;

class BadgeController extends Controller
{
    public function __construct(
        private ?BadgeRepository $badges = null
    ) {
        $this->badges ??= new BadgeRepository();
    }

    /** GET /insignias */
    public function index(): void
    {
        $ownedIds = [];
        if (is_logged_in()) {
            $ownedIds = $this->badges->ownedIds((int) current_user()['id']);
        }

        $this->render('pages/insignias/index', [
            'pageTitle' => 'Insignias',
            'items' => $this->badges->listAll(true),
            'ownedIds' => $ownedIds,
        ]);
    }
}

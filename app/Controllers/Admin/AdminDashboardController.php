<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AdminStatsRepository;

final class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminStatsRepository $stats,
    ) {
    }

    public function overview(Request $request): Response
    {
        return $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $this->stats->overview(),
        ]);
    }
}

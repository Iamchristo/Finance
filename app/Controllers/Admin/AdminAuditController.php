<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AuditRepository;

final class AdminAuditController extends Controller
{
    public function __construct(
        private readonly AuditRepository $audit,
    ) {
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 50;

        return $this->view('admin/audit/index', [
            'title' => 'Audit Log',
            'entries' => $this->audit->paginate($page, $perPage),
            'page' => $page,
            'perPage' => $perPage,
            'totalCount' => $this->audit->count(),
        ]);
    }
}

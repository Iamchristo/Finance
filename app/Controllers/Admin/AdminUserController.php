<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\UserRepository;
use App\Services\Admin\UserManagementService;

final class AdminUserController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserManagementService $userManagement,
    ) {
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->input('page', 1));
        $search = $request->input('search') !== null ? (string) $request->input('search') : null;

        return $this->view('admin/users/index', [
            'title' => 'Users',
            'users' => $this->users->paginate($page, 25, $search),
            'page' => $page,
            'totalCount' => $this->users->countAll($search),
            'perPage' => 25,
            'search' => $search ?? '',
            'roles' => $this->users->allRoles(),
        ]);
    }

    public function updateStatus(Request $request): Response
    {
        $userId = (int) $request->input('user_id', 0);
        $status = (string) $request->input('status', '');

        try {
            $this->userManagement->setStatus((int) $this->currentUserId(), $userId, $status, $request);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/admin/users');
        }

        Session::flash('success', 'User status updated.');

        return $this->redirect('/admin/users');
    }

    public function updateRole(Request $request): Response
    {
        $userId = (int) $request->input('user_id', 0);
        $roleId = (int) $request->input('role_id', 0);

        $this->userManagement->assignRole((int) $this->currentUserId(), $userId, $roleId, $request);

        Session::flash('success', 'User role updated.');

        return $this->redirect('/admin/users');
    }
}

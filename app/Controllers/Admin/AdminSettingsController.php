<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\SettingsRepository;
use App\Services\AuditService;

final class AdminSettingsController extends Controller
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly AuditService $audit,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->view('admin/settings/index', [
            'title' => 'Platform Settings',
            'settings' => $this->settings->all(),
        ]);
    }

    public function update(Request $request): Response
    {
        $key = (string) $request->input('setting_key', '');
        $value = (string) $request->input('setting_value', '');
        $type = (string) $request->input('setting_type', 'string');

        if ($key === '') {
            Session::flash('error', 'A setting key is required.');

            return $this->redirect('/admin/settings');
        }

        $this->settings->upsert($key, $value, $type);
        $this->audit->log((int) $this->currentUserId(), 'admin', 'settings.update', 'setting', null, ['key' => $key], $request);

        Session::flash('success', 'Setting saved.');

        return $this->redirect('/admin/settings');
    }
}

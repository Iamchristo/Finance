<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;

final class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function showRegister(Request $request): Response
    {
        return $this->view('auth/register', ['title' => 'Create your account']);
    }

    public function register(Request $request): Response
    {
        try {
            $user = $this->auth->register(
                (string) $request->input('first_name', ''),
                (string) $request->input('last_name', ''),
                trim((string) $request->input('email', '')),
                (string) $request->input('password', ''),
                $request->input('referral_code') !== '' ? (string) $request->input('referral_code') : null,
                $request->ip(),
            );
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/register');
        }

        $this->auth->attempt($user->email, (string) $request->input('password', ''), $request->ip());
        Session::flash('success', 'Welcome to Meridian Capital. Your account is ready.');

        return $this->redirect('/wallet');
    }

    public function showLogin(Request $request): Response
    {
        return $this->view('auth/login', ['title' => 'Sign in']);
    }

    public function login(Request $request): Response
    {
        try {
            $this->auth->attempt(
                trim((string) $request->input('email', '')),
                (string) $request->input('password', ''),
                $request->ip(),
            );
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/login');
        }

        return $this->redirect('/wallet');
    }

    public function logout(Request $request): Response
    {
        $userId = $this->currentUserId();

        if ($userId !== null) {
            $this->auth->logout($userId);
        }

        return $this->redirect('/login');
    }
}

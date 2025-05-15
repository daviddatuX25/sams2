<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\UserService;
use App\Services\NotificationService;
use App\Models\UserModel;
use App\Models\NotificationsModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService(
            new UserService(new UserModel()),
            new NotificationService(new NotificationsModel())
        );
    }

    public function index($role = null, $action = null)
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('role');
            return redirect()->to($role . '/');
        }

        if (!$role) {
            return view('auth/index', ['navbar' => 'home']);
        }

        if (!$action) {
            return view('auth/role_action', ['role' => $role, 'navbar' => 'home']);
        }

        switch ($action) {
            case 'login':
                return $this->login($role);
            case 'register':
                return $this->register($role);
            default:
                session()->setFlashdata('error', 'Invalid action');
                return redirect()->to('/auth');
        }

    }

    private function login($role = null)
    {
        if ($this->request->getMethod() === 'POST') {
            try {
                $userKey = $this->request->getPost('user_key');
                $password = $this->request->getPost('password');
                $user = $this->authService->login($userKey, $password, $role);
                session()->setFlashdata('success_notification', 'Welcome, ' . $user['first_name']);
                return redirect()->to($user['role'] . '/');
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
                return redirect()->to($role ? "auth/{$role}/login" : '/auth');
            }
        }

        return view('auth/login', ['role' => $role, 'navbar' => 'home']);
    }

    private function register($role = null)
    {
        if ($this->request->getMethod() === 'POST') {
            try {
                $userData = $this->request->getPost();
                $userData['role'] = $role ?? $userData['role'];
                $newUser = $this->authService->register($userData);

                session()->setFlashdata('success', 'Registration successful. Please login.');
                return redirect()->to('/auth/' . $newUser['role'] . '/login');
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
                return redirect()->to($role ? "/auth/{$role}/register" : '/auth');
            }
        }

        return view('auth/register', ['role' => $role ?? 'student', 'navbar' => 'home']);
    }

    public function forgotPassword()
    {
        if ($this->request->getMethod() === 'POST') {
            try {
                $userKey = $this->request->getPost('user_key');
                $this->authService->forgotPassword($userKey);
                session()->setFlashdata('success', 'Password reset successful. Contact Administrator to get new password.');
                return redirect()->to('/auth');
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
                return redirect()->to('auth/forgot_password');
            }
        }

        return view('auth/forgot_password', ['navbar' => 'home']);
    }

    public function logout()
    {
        $this->authService->logout();
        return redirect()->to('/');
    }
}
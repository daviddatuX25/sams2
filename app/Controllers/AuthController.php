<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{

    public function index($role = null, $action = null)
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('role');
            return redirect()->to($role . '/');
        }

        if (!$role) {
            return view('auth/index', ['navbar' => 'home']);
        } elseif (!$action) {
            return view('auth/role_action', ['role' => $role, 'navbar' => 'home']);
        } else {
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
    }

    private function login($role = null)
    {
        if ($this->request->getMethod() === 'POST') {
            $userKey = $this->request->getPost('user_key');
            $password = $this->request->getPost('password');
            $userModel = new UserModel();
            $user = $userModel->authenticateUserByPassword($userKey, $password);
            if($role != $user['role']) {
                session()->setFlashdata('error', 'User is not authorized for this role');
                return redirect()->back();
            }
            if ($user && $user['status'] === 'active') {
                session()->set([
                    'user_id' => $user['user_id'],
                    'role' => $user['role'],
                    'first_name' => $user['first_name'],
                    'profile_picture' => $user['profile_picture'],
                    'isLoggedIn' => true
                ]);
                session()->setFlashdata('success_notification', 'Welcome, ' . $user['first_name']);
                
                switch ($user['role']) {
                    case 'student':
                        return redirect()->to('/student');
                    case 'teacher':
                        return redirect()->to('/teacher');
                    case 'admin':
                        return redirect()->to('/admin');
                    default:
                        session()->remove('success_notification');
                        return redirect()->to('/');
                }
            } else {
                session()->setFlashdata('error', 'Invalid credentials or inactive account');
                return redirect()->to($role ? "auth/{$role}/login" : '/auth');
            }
        }
        return view('auth/login', ['role' => $role, 'navbar' => 'home']);
    }

    private function register($role = null)
    {
        if ($this->request->getMethod() === 'POST') {
            $userData = $this->request->getPost();
            $userModel = new UserModel();
            try {
                $userData['role'] = $role ?? $userData['role'];
                $newUser = $userModel->createUser($userData);
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
            $userKey = $this->request->getPost('user_key');
            $userModel = new UserModel();
            $user = $userModel->where('user_key', $userKey)->first();
            if ($user) {
                $newPassword = bin2hex(random_bytes(8));
                $userModel->resetPassword($user['user_id'], $newPassword);
                session()->setFlashdata('success', 'Password reset successful. Contact Administrator to get new password.');
                return redirect()->to('/auth');
            } else {
                session()->setFlashdata('error', 'User not found');
                return redirect()->to('auth/forgot_password');
            }
        }
        return view('auth/forgot_password', ['navbar' => 'home']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
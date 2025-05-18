<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\NotificationService;
use App\Models\UserModel;
use App\Models\NotificationsModel;
use App\Models\EnrollmentTermModel;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\ExceptionHandlingTrait;
class AuthController extends BaseController
{   
    protected $userService;

    public function __construct()
    {
        $this->userService = new UserService();
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

        return $this->handleAction(function()
        use($role, $action){
             switch ($action) {
                case 'login':
                    return $this->login($role);
                case 'register':
                    return $this->register($role);
                default:
                    return redirect()->to('auth');
                    throw new BusinessRuleException('Invalid Page');
            }
        });
    }

    private function login($role = null)
    {
        if ($this->request->getMethod() === 'POST') {
            $userKey = $this->request->getPost('user_key');
            $password = $this->request->getPost('password');
            $user = $this->userService->login($userKey, $password, $role);
            session()->setFlashdata('success', 'Welcome, ' . $user['first_name']);
            return redirect()->to($user['role'] . '/');
        }

        return view('auth/login', ['role' => $role, 'navbar' => 'home']);
    }

    private function register($role = null)
    {
        if ($this->request->getMethod() === 'POST') {
            $userData = $this->request->getPost();
            $userData['role'] = $role ?? $userData['role'];
            $newUser = $this->userService->register($userData);
            session()->setFlashdata('success', 'Registration successful. Please login.');
            return redirect()->to('/auth/' . $newUser['role'] . '/login');
        }
        return view('auth/register', ['role' => $role ?? 'student', 'navbar' => 'home']);
    }

    public function forgotPassword()
    {
        return $this->handleAction(function()
        {
            if ($this->request->getMethod() === 'POST') {
                $userKey = $this->request->getPost('user_key');
                $user = $this->userService->resetPassword($userKey);
                if((new UserModel)->find($this->superadmin)['role'] === 'admin'){
                    try {
                        if (!is_numeric($this->superadmin)) {
                            throw new \Exception('Invalid superadmin user ID');
                        }
                        $notification = [
                            $this->superadmin => [
                                'message' =>  'Password reset for user ' . $user['user_id'],
                                'type' => 'info'
                            ]
                        ];
                        (new \App\Services\NotificationService)->magicCreateNotifications($notification);
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to create notification: ' . $e->getMessage());
                        session()->setFlashdata('error', 'Failed to send notification: ' . $e->getMessage());
                    }
                }
                 session()->setFlashdata('success', 'Password reset successfully.');
             }
           
            return view('auth/forgot_password', ['navbar' => 'home']);
        });
    }

    public function logout()
    {
        $this->userService->logout();
    }
}
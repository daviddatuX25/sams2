<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProtectedController extends BaseController
{
    public function logout()
    {
        session()->destroy();

        if ($this->request->isAJAX()) {
            $response = [
                'success' => true,
                'role' => session()->get('user')['role'] ?? null,
            ];
            return $this->response->setJSON($response)->setStatusCode(200);
        }

        return redirect()->to(base_url('/login'));
    }

    protected function verifyRole(string $requiredRole): bool
    {
        return session()->get('user')['role'] === $requiredRole;
    }

    protected function verifyUserStatus(): array
    {
        $currentUser = session()->get('user');
        if ($currentUser['status'] !== 'active') {
            $updatedUser = $this->updatedUser($currentUser['user_id']);
            if ($updatedUser['status'] !== 'active') {
                $message = match ($updatedUser['status']) {
                    'pending' => 'Your account is awaiting administrator approval. Please be patient, thank you!',
                    'archived' => 'Your account has been archived. Please contact the administrator for account reactivation.',
                    default => 'Your account has an unknown status. Please contact support.',
                };
                return ['status' => false, 'message' => $message, 'user' => $currentUser['first_name']];
            }
        }
        return ['status' => true];
    }

    protected function redirectToPortal(string $portal)
    {
        $exemptedPortals = ['']; // Define allowed portals
        $userRole = session()->get('user')['role'] ?? null;

        if ($portal !== $userRole && !in_array($portal, $exemptedPortals)) {
            $controllerClass = match ($userRole) {
                'student' => 'App\Controllers\Student\MainController',
                'teacher' => 'App\Controllers\Teacher\MainController',
                'admin' => 'App\Controllers\Admin\MainController',
                default => null,
            };

            if ($controllerClass) {
                return redirect()->to(base_url($controllerClass));
            }
        }
    }

    protected function isLoggedIn(): bool
    {
        return session()->has('user');
    }

    protected function updatedUser(int $userId): ?array
    {
        $userModel = new UserModel();
        return $userModel->find($userId);
    }

    protected function currentUser(): ?array
    {
        return session()->get('user');
    }
}
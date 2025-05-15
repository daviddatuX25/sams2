<?php

namespace App\Services;

use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class UserService
{
    protected $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function getUser(int $userId): array
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new PageNotFoundException('User not found');
        }
        return $user;
    }

    public function login(string $userKey, string $password, ?string $role = null): array
    {
        $user = $this->userModel->where('user_key', $userKey)->first();

        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        $isPasswordValid = false;

        if ($user['is_password_temporary']) {
            // Temporary password check (assumes plain text match)
            $isPasswordValid = $password === $user['password_hash'];
        } else {
            // Normal password check (hashed)
            $isPasswordValid = password_verify($password, $user['password_hash']);
        }

        if (!$isPasswordValid) {
            throw new \Exception('Invalid credentials');
        }

        if ($role && $user['role'] !== $role) {
            throw new \Exception('Invalid role');
        }

        if ($user['status'] !== 'active') {
            throw new \Exception('Account is not active');
        }

        session()->set([
            'user_id' => $user['user_id'],
            'role' => $user['role'],
            'first_name' => $user['first_name'],
            'profile_picture' => $user['profile_picture'],
            'isLoggedIn' => true,
            'is_password_temporary' => $user['is_password_temporary'], // Optional: help frontend force password change
        ]);

        return $user;
    }


    public function register(array $userData): array
    {
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userData['is_password_temporary'] = 0;
        $userData['status'] = 'pending';
        unset($userData['password']);
        $userId = $this->userModel->insert($userData);
        if (!$userId) {
            throw new \Exception('Registration failed');
        }
        return $this->userModel->find($userId);
    }

    public function forgotPassword(string $userKey): void
    {
        $user = $this->userModel->where('user_key', $userKey)->first();
        if (!$user) {
            throw new \Exception('User not found');
        }
        $newPassword = bin2hex(random_bytes(8));
        $this->userModel->update($user['user_id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'is_password_temporary' => 1
        ]);
        // Notify admin (implementation depends on NotificationService)
    }

    public function updateProfile(int $userId, array $userData): void
    {
        if (!$this->userModel->update($userId, $userData)) {
            throw new \Exception('Profile update failed: ' . implode(', ', $this->userModel->errors()));
        }
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): void
    {
        $user = $this->userModel->find($userId);
        if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
            throw new \Exception('Invalid current password');
        }
        $this->userModel->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'is_password_temporary' => 0
        ]);
    }

    public function updateProfilePicture(int $userId, string $photoPath): void
    {
        if (!$this->userModel->update($userId, ['profile_picture' => $photoPath])) {
            throw new \Exception('Profile picture update failed');
        }
    }

    public function logout(): void
    {
        session()->destroy();
    }
}
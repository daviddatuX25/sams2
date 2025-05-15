<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\NotificationsModel;

class AuthService
{
    protected $userService;
    protected $notificationService;

    public function __construct(UserService $userService, NotificationService $notificationService)
    {
        $this->userService = $userService;
        $this->notificationService = $notificationService;
    }

    public function login(string $userKey, string $password, ?string $role = null): array
    {
        $user = $this->userService->login($userKey, $password, $role);
        $this->notificationService->createNotification($user['user_id'], 'Logged in successfully', 'success');
        return $user;
    }

    public function register(array $userData): array
    {
        $user = $this->userService->register($userData);
        $this->notificationService->createNotification($user['user_id'], 'Registration successful. Awaiting approval.', 'info');
        return $user;
    }

    public function forgotPassword(string $userKey): void
    {
        $this->userService->forgotPassword($userKey);
        // Notify admin (simplified; actual implementation may vary)
        $this->notificationService->createNotification(1, "Password reset requested for $userKey", 'warning');
    }

    public function logout(): void
    {
        $this->userService->logout();
    }
}
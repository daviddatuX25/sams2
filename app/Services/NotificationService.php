<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Traits\ServiceExceptionTrait;

class NotificationService
{
    use ServiceExceptionTrait;

    protected ?NotificationModel $notificationModel;

    public function __construct(
        ?NotificationModel $notificationModel = null
    ) {
        $this->notificationModel = $notificationModel ?? new NotificationModel();
    }

    /**
     * Get unread notifications for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getUnreadNotificationsByUser(int $userId): array
    {
        return $this->notificationModel
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Get all notifications for a user, optionally filtered by status, with pagination.
     *
     * @param int $userId
     * @param string|null $status (read, unread, all)
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getNotifications(int $userId, ?string $status = null, int $page = 1, int $perPage = 10): array
    {
        if ($status !== null && !in_array($status, ['read', 'unread'])) {
            $this->throwValidationError('Status must be read, unread, or null.');
        }

        if ($page < 1 || $perPage < 1) {
            $this->throwValidationError('Page and perPage must be positive integers.');
        }

        $builder = $this->notificationModel
            ->select('notification_id, user_id, message, type, is_read, created_at')
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL');

        if ($status === 'read') {
            $builder->where('is_read', 1);
        } elseif ($status === 'unread') {
            $builder->where('is_read', 0);
        }

        return $builder
            ->orderBy('created_at', 'DESC')
            ->findAll($perPage, ($page - 1) * $perPage);
    }

    public function getNotificationsByRecipient(int $recipientId): array
    {
        $userModel = new \App\Models\UserModel();
        if (!$userModel->where('user_id', $recipientId)->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('User', $recipientId);
        }

        return $this->notificationModel
            ->select('notification_id, recipient_id, message, status, created_at')
            ->where('recipient_id', $recipientId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Retrieve notifications by status.
     */
    public function getNotificationsByStatus(string $status): array
    {
        $validStatuses = ['read', 'unread'];
        if (!in_array($status, $validStatuses)) {
            $this->throwValidationError("Invalid status: {$status}. Must be one of: " . implode(', ', $validStatuses));
        }

        return $this->notificationModel
            ->select('notification_id, recipient_id, message, status, created_at')
            ->where('status', $status)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Count all notifications for a user, optionally filtered by status.
     *
     * @param int $userId
     * @param string|null $status
     * @return int
     */
    public function countNotifications(int $userId, ?string $status = null): int
    {
        if ($status !== null && !in_array($status, ['read', 'unread'])) {
            $this->throwValidationError('Status must be read, unread, or null.');
        }

        $builder = $this->notificationModel
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL');

        if ($status === 'read') {
            $builder->where('is_read', 1);
        } elseif ($status === 'unread') {
            $builder->where('is_read', 0);
        }

        return $builder->countAllResults();
    }

    /**
     * Count unread notifications for a user.
     *
     * @param int $userId
     * @return int
     */
    public function countUnreadNotifications(int $userId): int
    {
        return $this->notificationModel
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->where('deleted_at IS NULL')
            ->countAllResults();
    }

    /**
     * Mark a notification as read.
     *
     * @param int $userId
     * @param int $notificationId
     * @return bool
     */
    public function markNotificationRead(int $userId, int $notificationId): bool
    {
        $notification = $this->notificationModel
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$notification) {
            $this->throwNotFound('Notification', $notificationId);
        }

        return $this->notificationModel->update($notificationId, ['is_read' => 1]);
    }

    /**
     * Mark a notification as unread.
     *
     * @param int $userId
     * @param int $notificationId
     * @return bool
     */
    public function markNotificationUnread(int $userId, int $notificationId): bool
    {
        $notification = $this->notificationModel
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$notification) {
            $this->throwNotFound('Notification', $notificationId);
        }

        return $this->notificationModel->update($notificationId, ['is_read' => 0]);
    }

    /**
     * Delete a notification.
     *
     * @param int $userId
     * @param int $notificationId
     * @return bool
     */
    public function deleteNotification(int $userId, int $notificationId): bool
    {
        $notification = $this->notificationModel
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$notification) {
            $this->throwNotFound('Notification', $notificationId);
        }

        return $this->notificationModel->delete($notificationId);
    }

    /**
     * Generate bulk notifications for a list of users.
     *
     * @param array $userIds Array of user IDs
     * @param string $message Notification message
     * @return bool
     */
    public function bulkGenerateNotifications(array $userIds, string $message): bool
    {
        $validation = \Config\Services::validation();
        $rules = [
            'userIds' => 'required|is_array|min_length[1]',
            'message' => 'required|min_length[5]|max_length[1000]'
        ];

        if (!$validation->setRules($rules)->run(['userIds' => $userIds, 'message' => $message])) {
            $this->throwValidationError(implode(', ', $validation->getErrors()));
        }

        $userModel = new \App\Models\UserModel();
        $validUserIds = $userModel->whereIn('user_id', $userIds)->where('deleted_at IS NULL')->findColumn('user_id');

        if (empty($validUserIds)) {
            $this->throwNotFound('Users', implode(',', $userIds));
        }

        $notifications = [];
        foreach ($validUserIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'message' => $message,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        if (!$this->notificationModel->insertBatch($notifications)) {
            $this->throwBusinessRule('Failed to generate bulk notifications.');
        }

        return true;
    }


    /**
     * Create notifications for one or multiple users.
     *
     * @param array $notifications Array of [userId => ['message' => string, 'type' => string]]
     * @return bool
     */
    public function magicCreateNotifications(array $notifications): bool
    {
        $validation = \Config\Services::validation();
        $rules = [
            'notifications' => 'required|is_array',
            'notifications.*.message' => 'required|min_length[5]|max_length[1000]',
            'notifications.*.type' => 'required|in_list[info,warning,error,success]'
        ];

        // Prepare validation data directly
        $validationData = ['notifications' => array_values($notifications)];
        log_message('debug', 'Validation data: ' . print_r($validationData, true));

        if (!$validation->setRules($rules)->run($validationData)) {
            $errors = $validation->getErrors();
            log_message('error', 'Validation errors: ' . print_r($errors, true));
            $this->throwValidationError(implode(', ', array_values($errors)));
        }

        $userModel = new \App\Models\UserModel();
        $userIds = array_keys($notifications);
        $validUserIds = $userModel->whereIn('user_id', $userIds)->where('deleted_at IS NULL')->findColumn('user_id') ?? [];

        if (empty($validUserIds)) {
            $invalidUserIds = $userIds;
            $this->throwNotFound('Users', 'No valid users found. Invalid user IDs: ' . implode(',', $invalidUserIds));
        }

        $invalidUserIds = array_diff($userIds, $validUserIds);
        if (!empty($invalidUserIds)) {
            $this->throwNotFound('Users', 'Some users not found. Invalid user IDs: ' . implode(',', $invalidUserIds));
        }

        $insertData = [];
        foreach ($validUserIds as $userId) {
            if (isset($notifications[$userId])) {
                $insertData[] = [
                    'user_id' => $userId,
                    'message' => $notifications[$userId]['message'],
                    'type' => $notifications[$userId]['type'],
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        if (empty($insertData)) {
            $this->throwBusinessRule('No valid notifications to create.');
        }

        if (!$this->notificationModel->insertBatch($insertData)) {
            $this->throwBusinessRule('Failed to create notifications.');
        }

        return true;
    }

    public function createNotification(array $data): int
    {
        $validation = \Config\Services::validation();
        $rules = [
            'user_id' => 'required|is_natural_no_zero',
            'message' => 'required|min_length[5]|max_length[1000]',
            'type' => 'required|in_list[info,warning,error,success]'
        ];
        if (!$validation->setRules($rules)->run($data)) {
            throw new \CodeIgniter\Validation\Exceptions\ValidationException(implode(', ', $validation->getErrors()));
        }
        $insertData = [
            'user_id' => $data['user_id'],
            'message' => $data['message'],
            'type' => $data['type'],
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->notificationModel->insert($insertData);
        return $this->notificationModel->insertID();
    }

    public function updateNotification(int $notificationId, array $data): bool
    {
        return $this->notificationModel->update($notificationId, $data);
    }
    
}
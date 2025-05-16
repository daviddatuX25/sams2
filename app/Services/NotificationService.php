<?php
namespace App\Services;

use App\Models\NotificationModel;
use CodeIgniter\Validation\Exceptions\ValidationException;

class NotificationService
{
    protected $userRole;
    protected ?NotificationModel $notificationModel;

    public function __construct(
        ?string $userRole = null,
        ?NotificationModel $notificationModel = null
    ) {
        $this->userRole = $userRole ?? session()->get('role');
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
        if (!$this->notificationModel) {
            $this->notificationModel;
        }
        return $this->notificationModel
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Get all notifications for a user, optionally filtered by status.
     *
     * @param int $userId
     * @param ?string $status (read, unread, all)
     * @return array
     * @throws ValidationException
     */
    public function getNotifications(int $userId, ?string $status = null): array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }


        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only view your own notifications.');
        }

        $builder = $this->notificationModel
            ->select('notification_id, user_id, message, is_read, created_at')
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL');

        if ($status === 'read') {
            $builder->where('is_read', 1);
        } elseif ($status === 'unread') {
            $builder->where('is_read', 0);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Mark a notification as read.
     *
     * @param int $userId
     * @param int $notificationId
     * @return bool
     * @throws ValidationException
     */
    public function markNotificationRead(int $userId, int $notificationId): bool
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }


        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only modify your own notifications.');
        }

        $notification = $this->notificationModel
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$notification) {
            throw new ValidationException('Notification not found.');
        }

        return $this->notificationModel->update($notificationId, ['is_read' => 1]);
    }

    /**
     * Mark a notification as unread.
     *
     * @param int $userId
     * @param int $notificationId
     * @return bool
     * @throws ValidationException
     */
    public function markNotificationUnread(int $userId, int $notificationId): bool
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }


        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only modify your own notifications.');
        }

        $notification = $this->notificationModel
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$notification) {
            throw new ValidationException('Notification not found.');
        }

        return $this->notificationModel->update($notificationId, ['is_read' => 0]);
    }
}
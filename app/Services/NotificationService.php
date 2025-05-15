<?php

namespace App\Services;

use App\Models\NotificationsModel;
use CodeIgniter\Validation\Exceptions\ValidationException;

class NotificationService
{
    protected $notificationsModel;

    public function __construct(NotificationsModel $notificationsModel)
    {
        $this->notificationsModel = $notificationsModel;
    }

    public function getUnreadCount(int $userId): int
    {
        if (!is_numeric($userId)) {
            throw new ValidationException('User ID must be an integer.');
        }

        return $this->notificationsModel->where('user_id', $userId)
                                       ->where('read_datetime', null)
                                       ->countAllResults();
    }

    public function createNotification(int $userId, string $message): int
    {
        if (!is_numeric($userId)) {
            throw new ValidationException('User ID must be an integer.');
        }
        if (empty($message)) {
            throw new ValidationException('Message is required.');
        }

        return $this->notificationsModel->insert([
            'user_id' => $userId,
            'message' => $message,
            'create_datetime' => date('Y-m-d H:i:s')
        ]);
    }

    public function bulkCreateNotifications(array $notifications): array
    {
        $success = [];
        $failed = [];

        foreach ($notifications as $notif) {
            try {
                $id = $this->createNotification($notif['user_id'], $notif['message']);
                $success[] = 'Notification created for user ' . $notif['user_id'];
            } catch (\Exception $e) {
                $failed[] = $e->getMessage();
            }
        }

        return ['success' => $success, 'failed' => $failed];
    }

    public function notifyClassStudents(int $classId, string $message, string $type): void
    {
        $students = $this->studentAssignmentModel
            ->select('student_id')
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->findAll();

        foreach ($students as $student) {
            $this->createNotification($student['student_id'], $message, $type);
        }
    }

    public function notifyTeacher(int $classId, string $message, string $type): void
    {
        $teacher = $this->teacherAssignmentModel
            ->select('teacher_id')
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->first();

        if ($teacher) {
            $this->createNotification($teacher['teacher_id'], $message, $type);
        }
    }

    public function getUnreadNotifications(int $userId): array
    {
        return $this->notificationModel
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->where('deleted_at IS NULL')
            ->findAll();
    }
}
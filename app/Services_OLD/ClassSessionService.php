<?php

namespace App\Services;

use App\Models\ClassSessionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ClassSessionService
{
    protected $classSessionModel;

    public function __construct(
        ClassSessionModel $classSessionModel = null,
    ) {
        $this->classSessionModel = $classSessionModel ?? new ClassSessionModel();
    }

    public function getStudentSessionsForDate(int $userId, string $date): array
    {
        $startOfDay = "$date 00:00:00";
        $endOfDay = "$date 23:59:59";

        return $this->classSessionModel
            ->select('class_session.class_session_id, class_session.class_session_name, class_session.open_datetime, class_session.close_datetime, class.class_name')
            ->join('class', 'class.class_id = class_session.class_id')
            ->join('student_assignment', 'student_assignment.class_id = class.class_id')
            ->join('schedule', 'schedule.class_id = class.class_id')
            ->where('student_assignment.student_id', $userId)
            ->where('schedule.status', 'active')
            ->where('class_session.open_datetime >=', $startOfDay)
            ->where('class_session.open_datetime <=', $endOfDay)
            ->where('class_session.deleted_at IS NULL')
            ->where('class.deleted_at IS NULL')
            ->where('schedule.deleted_at IS NULL')
            ->where('student_assignment.deleted_at IS NULL')
            ->findAll();
    }

    public function getSessionsByClass(int $classId): array
    {
        return $this->classSessionModel
            ->select('class_session.*')
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    public function createSession(array $sessionData): void
    {
        if (!$this->classSessionModel->insert($sessionData)) {
            throw new \Exception('Failed to create session: ' . implode(', ', $this->classSessionModel->errors()));
        }
    }

    public function updateSession(int $sessionId, array $sessionData): void
    {
        if (!$this->classSessionModel->update($sessionId, $sessionData)) {
            throw new \Exception('Failed to update session: ' . implode(', ', $this->classSessionModel->errors()));
        }
    }

    public function deleteSession(int $sessionId): void
    {
        if (!$this->classSessionModel->delete($sessionId)) {
            throw new \Exception('Failed to delete session');
        }
    }
}
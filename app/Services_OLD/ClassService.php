<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\ClassSessionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ClassService
{
    protected $classModel;
    protected $classSessionModel;

    public function __construct(
        ClassModel $classModel = null,
        ClassSessionModel $classSessionModel = null,
    ) {
        $this->classModel = $classModel ?? new ClassModel();
        $this->classSessionModel = $classSessionModel ?? new ClassSessionModel();
    }

    public function getTeacherClasses(int $userId): array
    {
        return $this->classModel
            ->select('class.*')
            ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
            ->where('teacher_assignment.teacher_id', $userId)
            ->where('class.deleted_at IS NULL')
            ->findAll();
    }

    public function getStudentClasses(int $userId): array
    {
        return $this->classModel
            ->select('class.*')
            ->join('student_assignment', 'student_assignment.class_id = class.class_id')
            ->where('student_assignment.student_id', $userId)
            ->where('class.deleted_at IS NULL')
            ->findAll();
    }

    public function getClassDetails(int $classId): array
    {
        $class = $this->classModel->find($classId);
        if (!$class) {
            throw new PageNotFoundException('Class not found');
        }
        return $class;
    }

    public function getClassSessions(int $classId): array
    {
        return $this->classSessionModel
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
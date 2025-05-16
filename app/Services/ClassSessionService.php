<?php

namespace App\Services;
use CodeIgniter\Validation\Exceptions\ValidationException;
use App\Models\ClassSessionModel;
use App\Models\StudentAssignmentModel;
class ClassSessionService
{
      protected ClassSessionModel $classSessionModel; 
        public function __construct(
            protected $userRole = null,
            ClassSessionModel $classSessionModel = null
        )
        {
            $this->userRole = $userRole ?? session()->get('role');
            $this->classSessionModel = $classSessionModel ?? new classSessionModel();
        }


    public function getClassSessionsByDateAndUser(int $userId, string $date): array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
        throw new ValidationException('Role must be one of: student, teacher, or admin');
        }
        $builder = $this->classSessionModel
                        ->select('class_session.*, class.class_name')
                        ->join('class', 'class.class_id = class_session.class_id')
                        ->where('DATE(class_session.open_datetime)', $date)
                        ->where('class_session.deleted_at IS NULL');
        if ($this->userRole === 'student') {
            $builder->join('student_assignment', 'student_assignment.class_id = class.class_id')
                    ->where('student_assignment.student_id', $userId)
                    ->where('student_assignment.deleted_at IS NULL');
        } elseif ($this->userRole === 'teacher') {
            $builder->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->where('teacher_assignment.teacher_id', $userId)
                    ->where('teacher_assignment.deleted_at IS NULL');
        } elseif ($this->userRole === 'admin') {
            // Get all sessions for admin
        } else {
            return [];
        }

        return $builder->findAll();
    }

    //Get sessions for a class.
    public function getClassSessionsByUser(int $classId,int $userId): ?array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
        throw new ValidationException('Role must be one of: student, teacher, or admin');
        }

        $builder  = $this->classSessionModel->where('class_session.class_id', $classId);
        if($this->userRole === 'student'){
            $builder->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
                    ->where('student_assignment.student_id', $userId)
                    ->where('student_assignment.deleted_at IS NULL')  
                    ->where('class_session.deleted_at IS NULL');
        } elseif ($this->userRole === 'teacher'){
            $builder->join('teacher_assignment', 'teacher_assignment.class_id = class_session.class_id')
                    ->where('class_session.deleted_at IS NULL')
                    ->where('teacher_assignment.deleted_at IS NULL')  
                    ->where('teacher_assignment.teacher_id', $userId);
        } elseif($this->userRole === 'admin') {
            $builder->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class_session.class_id');
        } else {
            return null;
        }
        return $builder->findAll();
    }

   public function createClassSession(int $classId, array $sessionData, int $teacherId): int
    {
        if (!$this->isTeacherAuthorized($teacherId, $classId)) {
            throw new ValidationException('You are not assigned to this class.');
        }
        log_message('debug', 'Sampple thres: ' . $sessionData['time_in_threshold']);
        log_message('debug', 'Creating class session with data: ' . json_encode($sessionData));
        $data = array_merge($sessionData, ['class_id' => $classId]);
        if (!$this->classSessionModel->insert($data)) {
            $errors = $this->classSessionModel->errors();
            log_message('error', 'Failed to insert class session: ' . json_encode($errors));
            throw new ValidationException('Failed to create class session: ' . implode(', ', $errors));
        }
        return $this->classSessionModel->insertID();
    }

    public function createAutoClassSession(int $scheduleId, array $sessionData): int
    {
        // No teacher authorization needed for cronjob
        $data = array_merge($sessionData, [
            'schedule_id' => $scheduleId,
            'attendance_method' => 'manual',
            'auto_mark_attendance' =>  'yes',
            'time_in_threshold' => '00:00:10',
            'late_threshold' => '00:00:30',
            'time_out_threshold' => '00:00:15'
        ]);
        $this->classSessionModel->insert($data);
        return $this->classSessionModel->insertID();
    }

    public function updateClassSession(int $sessionId, array $sessionData, int $teacherId): bool
    {
        $session = $this->classSessionModel->find($sessionId);
        if (!$session || !$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            throw new ValidationException('Unauthorized or invalid session.');
        }
        log_message('debug', 'Updating class session with data: ' . json_encode($sessionData));
        if (!$this->classSessionModel->update($sessionId, $sessionData)) {
            $errors = $this->classSessionModel->errors();
            log_message('error', 'Failed to update class session: ' . json_encode($errors));
            throw new ValidationException('Failed to create class session: ' . implode(', ', $errors));
        }
        return $this->classSessionModel->insertID();
    }

    public function deleteClassSession(int $sessionId, int $teacherId): bool
    {
        $session = $this->classSessionModel->find($sessionId);
        if (!$session || !$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            throw new ValidationException('Unauthorized or invalid session.');
        }
        return $this->classSessionModel->delete($sessionId);
    }

    private function isTeacherAuthorized(int $teacherId, int $classId): bool
    {
        return (new \App\Models\TeacherAssignmentModel())
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->countAllResults() > 0;
    }
}
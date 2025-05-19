<?php

namespace App\Services;

use CodeIgniter\Validation\Exceptions\ValidationException;
use App\Models\ClassSessionModel;
use App\Traits\ServiceExceptionTrait;

class ClassSessionService
{
    use ServiceExceptionTrait;

    protected ClassSessionModel $classSessionModel;
    protected $validation;

    public function __construct(
        ?ClassSessionModel $classSessionModel = null
    ) {
        $this->validation = \Config\Services::validation();
        $this->classSessionModel = $classSessionModel ?? new ClassSessionModel();
    }

    /**
     * Retrieve class sessions by class ID.
     */
    public function getSessionsByClass(int $classId): array
    {
        $classModel = new \App\Models\ClassModel();
        if (!$classModel->where('class_id', $classId)->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Class', $classId);
        }

        return $this->classSessionModel
            ->select('class_session_id, class_id, room_id, start_time, end_time, day_of_week, session_type')
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Retrieve class sessions by room ID.
     */
    public function getSessionsByRoom(int $roomId): array
    {
        $roomModel = new \App\Models\RoomModel();
        if (!$roomModel->where('room_id', $roomId)->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Room', $roomId);
        }

        return $this->classSessionModel
            ->select('class_session_id, class_id, room_id, start_time, end_time, day_of_week, session_type')
            ->where('room_id', $roomId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Get class sessions by date for a student.
     */
    public function student_getClassSessionsByDate(int $studentId, string $date): array
    {
        return $this->classSessionModel
            ->select('class_session.*, class.class_name')
            ->join('class', 'class.class_id = class_session.class_id')
            ->join('student_assignment', 'student_assignment.class_id = class.class_id')
            ->where('student_assignment.student_id', $studentId)
            ->where('DATE(class_session.open_datetime)', $date)
            ->where('class_session.deleted_at IS NULL')
            ->where('student_assignment.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Get class sessions by date for a teacher.
     */
    public function teacher_getClassSessionsByDate(int $teacherId, string $date): array
    {
        return $this->classSessionModel
            ->select('class_session.*, class.class_name')
            ->join('class', 'class.class_id = class_session.class_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
            ->where('teacher_assignment.teacher_id', $teacherId)
            ->where('DATE(class_session.open_datetime)', $date)
            ->where('class_session.deleted_at IS NULL')
            ->where('teacher_assignment.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Get class sessions by date for an admin.
     */
    public function admin_getClassSessionsByDate(string $date): array
    {
        return $this->classSessionModel
            ->select('class_session.*, class.class_name')
            ->join('class', 'class.class_id = class_session.class_id')
            ->where('DATE(class_session.open_datetime)', $date)
            ->where('class_session.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Get class sessions by class for a student.
     */
    public function student_getClassSessionsByClass(int $classId, int $studentId): array
    {
        return $this->classSessionModel
            ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
            ->where('class_session.class_id', $classId)
            ->where('student_assignment.student_id', $studentId)
            ->where('class_session.deleted_at IS NULL')
            ->where('student_assignment.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Get class sessions by class for a teacher.
     */
    public function teacher_getClassSessionsByClass(int $classId, int $teacherId): array
    {
        return $this->classSessionModel
            ->join('teacher_assignment', 'teacher_assignment.class_id = class_session.class_id')
            ->where('class_session.class_id', $classId)
            ->where('teacher_assignment.teacher_id', $teacherId)
            ->where('class_session.deleted_at IS NULL')
            ->where('teacher_assignment.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Get class sessions by class for an admin.
     */
    public function admin_getClassSessionsByClass(int $classId): array
    {
        return $this->classSessionModel
            ->where('class_session.class_id', $classId)
            ->where('class_session.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Create a class session.
     */
    public function teacher_createClassSession(int $classId, array $postData, int $teacherId): int
    {
        if (!(new \App\Models\ClassModel())->find($classId)) {
            $this->throwNotFound('Class Id',$classId);
        }
        if (!$this->isTeacherAuthorized($teacherId, $classId)) {
            $this->throwUnauthorized('You are not assigned to this class.');
        }

        $rules = [
            'open_datetime' => 'required|valid_date',
            'duration' => 'required|integer|greater_than[0]',
            'class_session_name' => 'required|string|max_length[255]',
            'session_description' => 'permit_empty|string|max_length[1000]',
            'attendance_method' => 'required|in_list[manual,automatic]',
            'auto_mark_attendance' => 'required|in_list[yes,no]'
        ];
        if ($postData['auto_mark_attendance'] === 'yes') {
            $rules['time_in_threshold'] = 'required|integer|greater_than_equal_to[0]';
            $rules['time_out_threshold'] = 'required|integer|greater_than_equal_to[0]';
            $rules['late_threshold'] = 'required|integer|greater_than_equal_to[0]';
        }
        if (!$this->validate($rules, $postData)) {
            $this->throwValidationError(implode(', ', $this->validation->getErrors()));
        }

        $openDatetime = new \DateTime($postData['open_datetime']);
        $duration = (int)$postData['duration'];
        $closeDatetime = (clone $openDatetime)->modify("+{$duration} minutes");
        helper('main');
        $postData['open_datetime'] = $openDatetime->format('Y-m-d H:i:s');
        $postData['close_datetime'] = $closeDatetime->format('Y-m-d H:i:s');
        $postData['time_in_threshold'] = $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['time_in_threshold']) : null;
        $postData['time_out_threshold'] = $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['time_out_threshold']) : null;
        $postData['late_threshold'] = $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['late_threshold']) : null;

        log_message('debug', 'Sample threshold: ' . $postData['time_in_threshold']);
        log_message('debug', 'Creating class session with data: ' . json_encode($postData));
        $data = array_merge($postData, ['class_id' => $classId]);
        $this->classSessionModel->insert($data);
        return $this->classSessionModel->insertID();
    }

    /**
     * Create an automatic class session (e.g., via cronjob).
     */
    public function createAutoClassSession(int $scheduleId, array $sessionData): int
    {
        $data = array_merge($sessionData, [
            'schedule_id' => $scheduleId,
            'attendance_method' => 'manual',
            'auto_mark_attendance' => 'yes',
            'time_in_threshold' => '00:00:10',
            'late_threshold' => '00:00:30',
            'time_out_threshold' => '00:00:15'
        ]);
        $this->classSessionModel->insert($data);
        return $this->classSessionModel->insertID();
    }

    /**
     * Update a class session.
     */
    public function teacher_updateClassSession(int $sessionId, array $postData, int $teacherId): bool
    {
        $session = $this->classSessionModel->find($sessionId);
        if (!$session) {
            $this->throwNotFound('Session Id',$sessionId);
        }

        if (!$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            $this->throwUnauthorized('Teacher is not authorized for this class session');
        }

        if ($postData['status'] === 'active') {
            $this->throwBusinessRule('Cannot change a session that is active');
        }

        $rules = [
            'class_session_id' => 'required|is_natural_no_zero',
            'class_session_name' => 'required|string|max_length[255]',
            'session_description' => 'permit_empty|string|max_length[1000]',
        ];
        if (!$this->validate($rules, $postData)) {
            $this->throwValidationError(implode(', ', $this->validation->getErrors()));
        }

        if (in_array($postData['status'], ['marked', 'finished'])) {
            $selectiveData = [
                'class_session_name' => $postData['class_session_name'],
                'session_description' => $postData['session_description'] ?? null,
            ];
            log_message('debug', 'Updating class session with data: ' . json_encode($selectiveData));
            return $this->classSessionModel->update($sessionId, $selectiveData);
        }

        if ($postData['status'] === 'pending') {
            $rules = [
                'open_datetime' => 'required|valid_date',
                'duration' => 'required|integer|greater_than[0]',
                'attendance_method' => 'required|in_list[manual,automatic]',
                'auto_mark_attendance' => 'required|in_list[yes,no]'
            ];
            if ($postData['auto_mark_attendance'] === 'yes') {
                $rules['time_in_threshold'] = 'required|integer|greater_than_equal_to[0]';
                $rules['time_out_threshold'] = 'required|integer|greater_than_equal_to[0]';
                $rules['late_threshold'] = 'required|integer|greater_than_equal_to[0]';
            }
            if (!$this->validate($rules, $postData)) {
                $this->throwValidationError(implode(', ', $this->validation->getErrors()));
            }

            $openDatetime = new \DateTime($postData['open_datetime']);
            $duration = (int)$postData['duration'];
            $closeDatetime = (clone $openDatetime)->modify("+{$duration} minutes");
            helper('main');
            $postData['session_description'] = $postData['session_description'] ?? null;
            $postData['open_datetime'] = $openDatetime->format('Y-m-d H:i:s');
            $postData['close_datetime'] = $closeDatetime->format('Y-m-d H:i:s');
            $postData['time_in_threshold'] = $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['time_in_threshold']) : null;
            $postData['time_out_threshold'] = $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['time_out_threshold']) : null;
            $postData['late_threshold'] = $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['late_threshold']) : null;
            return $this->classSessionModel->update($sessionId, $postData);
        }

        return false;
    }

    /**
     * Finish a class session.
     */
    public function teacher_finishClassSession(int $sessionId, int $teacherId): bool
    {
        $session = $this->classSessionModel->find($sessionId);
        if (!$session) {
            $this->throwNotFound('Session Id',$sessionId);
        }

        if (!$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            $this->throwUnauthorized('Unauthorized to finish session');
        }

        return $this->classSessionModel->update($sessionId, ['status' => 'finished']);
    }

    
    /**
     * Mark a class session.
     */
    public function teacher_MarkClassSession(int $sessionId, int $teacherId): bool
    {
        $session = $this->classSessionModel->find($sessionId);
        if (!$session) {
            $this->throwNotFound('Session Id',$sessionId);
        }

        if (!$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            $this->throwUnauthorized('Unauthorized to mark session');
        }

        return $this->classSessionModel->update($sessionId, ['status' => 'marked']);
    }

    /**
     * Cancel a class session.
     */
    public function teacher_cancelClassSession(int $sessionId, int $teacherId): bool
    {
        $session = $this->classSessionModel->find($sessionId);
        if (!$session) {
            $this->throwNotFound('Session Id',$sessionId);
        }

        if (!$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            $this->throwUnauthorized('Unauthorized to cancel session');
        }

        return $this->classSessionModel->update($sessionId, ['status' => 'cancelled']);
    }

    /**
     * Delete a class session.
     */
    public function teacher_deleteClassSession(int $sessionId, int $teacherId): bool
    {
        $session = $this->classSessionModel->find($sessionId);
        if (!$session) {
            $this->throwNotFound('Session Id',$sessionId);
        }

        if (!$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            $this->throwUnauthorized('Unauthorized to delete session');
        }

        return $this->classSessionModel->delete($sessionId);
    }

    /**
     * Validate data against rules.
     */
    private function validate(array $rules, array $data): bool
    {
        return $this->validation->setRules($rules)->run($data);
    }

    /**
     * Check if a teacher is authorized for a class.
     */
    private function isTeacherAuthorized(int $teacherId, int $classId): bool
    {
        return (new \App\Models\TeacherAssignmentModel())
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->countAllResults() > 0;
    }
    public function countPending(): int
    {
        return $this->classSessionModel->where('status', 'pending')->where('deleted_at IS NULL')->countAllResults();
    }

    public function getSessions(): array
    {
        return $this->classSessionModel->where('deleted_at IS NULL')->findAll();
    }

    public function createSession(array $postData): int
    {
        return $this->createClassSession($postData); // Alias or adjust signature
    }

    public function updateSession(int $classSessionId, array $postData): bool
    {
        $session = $this->classSessionModel->find($classSessionId);
        if (!$session) {
            $this->throwNotFound('Class Session', $classSessionId);
        }
        return $this->classSessionModel->update($classSessionId, $postData);
    }

    public function deleteSession(int $classSessionId): bool
    {
        $session = $this->classSessionModel->find($classSessionId);
        if (!$session) {
            $this->throwNotFound('Class Session', $classSessionId);
        }
        return $this->classSessionModel->delete($classSessionId);
    }

    public function countOpen(): int
    {
        return $this->classSessionModel->where('status', 'active')->where('deleted_at IS NULL')->countAllResults();
    }
}
<?php

namespace App\Services;

use App\Models\AttendanceModel;
use App\Models\ClassSessionModel;
use App\Models\AttendanceLogsModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Validation\Exceptions\ValidationException;

class AttendanceService
{
    protected $attendanceModel;
    protected $classSessionModel;
    protected $attendanceLogsModel;

    public function __construct(
        AttendanceModel $attendanceModel,
        ClassSessionModel $classSessionModel,
        AttendanceLogsModel $attendanceLogsModel
    ) {
        $this->attendanceModel = $attendanceModel;
        $this->classSessionModel = $classSessionModel;
        $this->attendanceLogsModel = $attendanceLogsModel;
    }

    public function getAttendanceModel()
    {
        return $this->attendanceModel;
    }

    public function updateAttendance(int $sessionId, int $userId, array $attendanceData): void
    {
        $existing = $this->attendanceModel
            ->where('class_session_id', $sessionId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $this->attendanceModel->update($existing['attendance_id'], $attendanceData);
        } else {
            $attendanceData['class_session_id'] = $sessionId;
            $attendanceData['user_id'] = $userId;
            $this->attendanceModel->insert($attendanceData);
        }
    }

    public function getStudentAttendanceLogs(int $userId, array $filters): array
    {
        $builder = $this->attendanceLogsModel
            ->select('attendance_logs.*, class_session.class_session_name')
            ->join('class_session', 'class_session.class_session_id = attendance_logs.class_session_id')
            ->where('attendance_logs.user_id', $userId)
            ->where('attendance_logs.deleted_at IS NULL');

        if (!empty($filters['date'])) {
            $builder->where('DATE(attendance_logs.timestamp)', $filters['date']);
        }
        if (!empty($filters['class_id'])) {
            $builder->where('class_session.class_id', $filters['class_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('attendance_logs.action', $filters['status']);
        }

        return $builder->findAll();
    }

    public function getTeacherAttendanceReport(int $userId, array $filters): array
    {
        $builder = $this->attendanceModel
            ->select('attendance.*, class_session.class_session_name, user.first_name, user.last_name')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('class', 'class.class_id = class_session.class_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
            ->join('user', 'user.user_id = attendance.user_id')
            ->where('teacher_assignment.teacher_id', $userId)
            ->where('attendance.deleted_at IS NULL');

        if (!empty($filters['class_id'])) {
            $builder->where('class.class_id', $filters['class_id']);
        }
        if (!empty($filters['start_date'])) {
            $builder->where('attendance.marked_at >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('attendance.marked_at <=', $filters['end_date']);
        }

        return $builder->findAll();
    }

    public function markAttendance(int $sessionId, int $userId, string $status, bool $isManual = false): bool
    {
        if (!is_numeric($sessionId) || !is_numeric($userId)) {
            throw new ValidationException('Session ID and User ID must be integers.');
        }

        if (!in_array($status, ['present', 'absent', 'late', 'unmared', 'excused'])) {
            throw new ValidationException('Status must be one of: present, absent, late.');
        }

        if (!$this->classSessionModel->find($sessionId)) {
            throw new PageNotFoundException('Class session not found.');
        }

        $this->attendanceModel->db->transBegin();
        $this->attendanceLogsModel->db->transBegin();
        try {
            $existing = $this->attendanceModel->where('class_session_id', $sessionId)
                                              ->where('user_id', $userId)
                                              ->first();

            $data = [
                'class_session_id' => $sessionId,
                'user_id' => $userId,
                'status' => $status,
                'is_manual' => $isManual ? 1 : 0,
                'marked_at' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                $this->attendanceModel->update($existing['attendance_id'], $data);
            } else {
                $this->attendanceModel->insert($data);
            }

            // Log the attendance action
            $this->attendanceLogsModel->insert([
                'user_id' => $userId,
                'class_session_id' => $sessionId,
                'tracker_id' => $isManual ? 0 : 1, // Assume tracker_id=0 for manual, adjust as needed
                'action' => $status === 'late' ? 'late' : ($isManual ? 'manual' : 'time_in'),
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            $this->attendanceModel->db->transCommit();
            $this->attendanceLogsModel->db->transCommit();
            return true;
        } catch (\Exception $e) {
            $this->attendanceModel->db->transRollback();
            $this->attendanceLogsModel->db->transRollback();
            throw $e;
        }
    }

    public function getFilteredAttendance(int $userId, array $filters = []): array
    {
        if (!is_numeric($userId) && $userId !== 0) {
            throw new ValidationException('User ID must be an integer or 0.');
        }

        $query = $this->attendanceModel->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
                                       ->join('class', 'class.class_id = class_session.class_id');

        if ($userId !== 0) {
            $query->where('attendance.user_id', $userId);
        }

        if (!empty($filters['class_id'])) {
            $query->where('class.class_id', $filters['class_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('attendance.marked_at >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('attendance.marked_at <=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('attendance.status', $filters['status']);
        }

        // Join with attendance_logs for detailed actions
        $query->join('attendance_logs', 'attendance_logs.class_session_id = attendance.class_session_id AND attendance_logs.user_id = attendance.user_id', 'left');

        return $query->findAll();
    }

    public function getAttendanceRate(int $userId): float
    {
        if (!is_numeric($userId)) {
            throw new ValidationException('User ID must be an integer.');
        }

        $totalSessions = $this->attendanceModel->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
                                               ->where('attendance.user_id', $userId)
                                               ->countAllResults();

        if ($totalSessions === 0) {
            return 0.0;
        }

        $presentSessions = $this->attendanceModel->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
                                                 ->where('attendance.user_id', $userId)
                                                 ->where('attendance.status', 'present')
                                                 ->countAllResults();

        return ($presentSessions / $totalSessions) * 100;
    }

    public function getAttendanceStats(int $userId, int $classId): array
    {
        if (!is_numeric($userId) || !is_numeric($classId)) {
            throw new ValidationException('User ID and Class ID must be integers.');
        }

        $query = $this->attendanceModel->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
                                       ->where('attendance.user_id', $userId)
                                       ->where('class_session.class_id', $classId);

        $total = $query->countAllResults();
        $present = $query->where('attendance.status', 'present')->countAllResults();
        $absent = $query->where('attendance.status', 'absent')->countAllResults();
        $late = $query->where('attendance.status', 'late')->countAllResults();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'rate' => $total > 0 ? ($present / $total) * 100 : 0.0
        ];
    }

    public function logAttendance(int $userId, int $classSessionId, int $trackerId, string $action, string $timestamp): bool
    {
        if (!is_numeric($userId) || !is_numeric($classSessionId) || !is_numeric($trackerId)) {
            throw new ValidationException('Invalid IDs provided.');
        }

        $tracker = $this->attendanceLogsModel->getTracker($trackerId);
        $isManual = $tracker['tracker_type'] === 'manual' ? 1 : 0;

        $data = [
            'user_id' => $userId,
            'class_session_id' => $classSessionId,
            'tracker_id' => $trackerId,
            'action' => $action,
            'timestamp' => $timestamp
        ];

        $this->attendanceLogsModel->insert($data);

        // If auto_mark_attendance is enabled, trigger processing
        $session = $this->classSessionModel->find($classSessionId);
        if ($session['auto_mark_attendance'] === 'yes') {
            $this->processAttendance($classSessionId, $userId);
        }

        return true;
    }

    public function processAttendance(int $classSessionId, ?int $userId = null): void
    {
        $session = $this->classSessionModel->find($classSessionId);
        if ($session['auto_mark_attendance'] !== 'yes') {
            return;
        }

        $students = $userId ? [$this->attendanceModel->getUser($userId)] : $this->attendanceModel->getEnrolledStudents($session['class_id']);
        $timeInThreshold = strtotime($session['open_datetime']) + $this->timeToSeconds($session['time_in_threshold']);
        $lateThreshold = strtotime($session['open_datetime']) + $this->timeToSeconds($session['late_threshold']);
        $timeOutThreshold = strtotime($session['close_datetime']) - $this->timeToSeconds($session['time_out_threshold']);

        foreach ($students as $student) {
            $logs = $this->attendanceLogsModel->where([
                'user_id' => $student['user_id'],
                'class_session_id' => $classSessionId
            ])->findAll();

            $timeIn = null;
            $timeOut = null;
            foreach ($logs as $log) {
                if ($log['action'] === 'time_in' || $log['action'] === 'auto') {
                    $timeIn = strtotime($log['timestamp']);
                }
                if ($log['action'] === 'time_out') {
                    $timeOut = strtotime($log['timestamp']);
                }
            }

            $status = 'unmarked';
            if ($timeIn) {
                if ($timeIn <= $timeInThreshold) {
                    $status = 'present';
                } elseif ($timeIn <= $lateThreshold) {
                    $status = 'late';
                } else {
                    $status = 'absent';
                }
            } else {
                $status = 'absent';
            }

            if ($status === 'present' && $timeOut && $timeOut < $timeOutThreshold) {
                $status = 'late';
            }

            $this->markAttendance($student['user_id'], $classSessionId, $status, false);
        }
    }

    private function timeToSeconds(string $time): int
    {
        list($hours, $minutes, $seconds) = explode(':', $time);
        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    public function getStudentAttendanceForSessions(int $userId, array $sessionIds): array
    {
        if (empty($sessionIds)) {
            return [];
        }

        return $this->attendanceModel
            ->select('attendance.class_session_id, attendance.status, attendance.marked_at, attendance.is_manual')
            ->where('user_id', $userId)
            ->whereIn('class_session_id', $sessionIds)
            ->where('deleted_at IS NULL')
            ->findAll();
    }
}
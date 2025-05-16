<?php

namespace App\Services;

use CodeIgniter\Validation\Exceptions\ValidationException;
use App\Models\AttendanceModel;
use App\Models\AttendanceLogsModel;
use App\Models\AttendanceLeaveModel;
use App\Models\ClassSessionModel;
use App\Models\TrackerModel;
use App\Models\StudentAssignmentModel;
use App\Models\TeacherAssignmentModel;

class AttendanceService
{
    protected $userRole;
    protected AttendanceModel $attendanceModel;
    protected ?AttendanceLogsModel $attendanceLogsModel;
    protected ?AttendanceLeaveModel $attendanceLeaveModel;
    protected ?ClassSessionModel $classSessionModel;
    protected ?TrackerModel $trackerModel;

    public function __construct(
        ?string $userRole = null,
        ?AttendanceModel $attendanceModel = null,
        ?AttendanceLogsModel $attendanceLogsModel = null,
        ?AttendanceLeaveModel $attendanceLeaveModel = null,
        ?ClassSessionModel $classSessionModel = null,
        ?TrackerModel $trackerModel = null
    ) {
        $this->userRole = $userRole ?? session()->get('role');
        $this->attendanceModel = $attendanceModel ?? new AttendanceModel();
        $this->attendanceLogsModel = $attendanceLogsModel;
        $this->attendanceLeaveModel = $attendanceLeaveModel;
        $this->classSessionModel = $classSessionModel;
        $this->trackerModel = $trackerModel;
    }

    /**
     * Log an attendance action (time_in, time_out, auto) for a user.
     *
     * @param int $userId
     * @param int $classSessionId
     * @param int $trackerId
     * @param string $action
     * @return int
     * @throws \Exception
     */
    public function logAttendance(int $userId, int $classSessionId, int $trackerId, string $action): int
    {
        if (!$this->attendanceLogsModel) {
            $this->attendanceLogsModel = new AttendanceLogsModel();
        }

        $data = [
            'user_id' => $userId,
            'class_session_id' => $classSessionId,
            'tracker_id' => $trackerId,
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($this->attendanceLogsModel->insert($data)) {
            return $this->attendanceLogsModel->insertID();
        }

        throw new \Exception('Failed to log attendance');
    }

    /**
     * Retrieve attendance records for a user based on their role.
     *
     * @param int $userId
     * @return array
     * @throws ValidationException
     */
    public function getAttendanceByUser(int $userId): array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, or admin');
        }

        $builder = $this->attendanceModel
            ->select('attendance.*')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL');

        if ($this->userRole === 'student') {
            $builder->where('attendance.user_id', $userId)
                    ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
                    ->where('student_assignment.student_id', $userId)
                    ->where('student_assignment.deleted_at IS NULL');
        } elseif ($this->userRole === 'teacher') {
            $builder->join('class', 'class.class_id = class_session.class_id')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->where('teacher_assignment.teacher_id', $userId)
                    ->where('teacher_assignment.deleted_at IS NULL');
        } elseif ($this->userRole === 'admin') {
            // Admins see all attendance records
        } else {
            return [];
        }

        return $builder->findAll();
       
    }

    /**
     * Mark attendance for a user in a class session.
     *
     * @param int $userId
     * @param int $classSessionId
     * @param string $status
     * @param bool $isManual
     * @return bool
     */
    public function markAttendance(int $userId, int $classSessionId, string $status, bool $isManual = false): bool
    {
        $data = [
            'user_id' => $userId,
            'class_session_id' => $classSessionId,
            'status' => $status,
            'is_manual' => $isManual ? 1 : 0,
            'marked_at' => date('Y-m-d H:i:s')
        ];

        return $this->attendanceModel->save($data);
    }

    /**
     * Calculate attendance rate for a student across all enrolled classes in a term.
     *
     * @param int $userId
     * @param int $termId
     * @return float
     */
    public function getAttendanceRateByStudent(int $userId, int $termId): float
    {
        if (!$this->classSessionModel) {
            $this->classSessionModel = new ClassSessionModel();
        }

        $totalSessions = $this->classSessionModel
            ->select('class_session.class_session_id')
            ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
            ->where('student_assignment.student_id', $userId)
            ->where('class_session.status', 'marked')
            ->where('class_session.deleted_at IS NULL')
            ->where('student_assignment.deleted_at IS NULL')
            ->where('student_assignment.enrollment_term_id', $termId)
            ->countAllResults();

        if ($totalSessions === 0) {
            return 0.0;
        }

        $presentSessions = $this->attendanceModel
            ->where('user_id', $userId)
            ->where('status', 'present')
            ->where('class_session_id IN (
                SELECT class_session_id 
                FROM class_session 
                JOIN student_assignment 
                ON student_assignment.class_id = class_session.class_id 
                WHERE student_assignment.student_id = ' . $userId . '
                AND class_session.status = "marked"
                AND class_session.deleted_at IS NULL
                AND student_assignment.deleted_at IS NULL
                AND student_assignment.enrollment_term_id = ' . $termId . '
            )')
            ->where('deleted_at IS NULL')
            ->countAllResults();

        return round(($presentSessions / $totalSessions) * 100, 2);
    }

    /**
     * Retrieve attendance logs for a user with optional filters.
     *
     * @param int $userId
     * @param array $filters
     * @return array
     * @throws \Exception
     */
    public function getAttendanceLogs(int $userId, array $filters): array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, or admin');
        }

        $builder = $this->attendanceModel
            ->select('attendance.*, class.class_name, class_session.class_session_name, attendance.marked_at')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('class', 'class.class_id = class_session.class_id')
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->where('class.deleted_at IS NULL');

        // Apply role-based constraints
        if ($this->userRole === 'student') {
            $builder->where('attendance.user_id', $userId)
                    ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
                    ->where('student_assignment.student_id', $userId)
                    ->where('student_assignment.deleted_at IS NULL');
        } elseif ($this->userRole === 'teacher') {
            $builder->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->where('teacher_assignment.teacher_id', $userId)
                    ->where('teacher_assignment.deleted_at IS NULL');
        } elseif ($this->userRole === 'admin') {
            // Admins can view logs for a specific user or all logs
            if ($userId) {
                $builder->where('attendance.user_id', $userId);
            }
        }

        // Apply filters
        if (!empty($filters['date'])) {
            $builder->where('DATE(attendance.marked_at)', $filters['date']);
        }
        if (!empty($filters['class_id'])) {
            $builder->where('class.class_id', $filters['class_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('attendance.status', $filters['status']);
        }

        return $builder->findAll();
    }

    public function getAttendanceChartData(int $userId, ?string $startDate = null, ?string $endDate = null, ?int $classId = null): array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, or admin');
        }

        if (!$this->classSessionModel) {
            $this->classSessionModel = new ClassSessionModel();
        }

        $builder = $this->attendanceModel
            ->select('DATE(class_session.open_datetime) as date, attendance.status, COUNT(*) as count')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->groupBy(['DATE(class_session.open_datetime)', 'attendance.status']);

        // Apply role-based filters
        if ($this->userRole === 'student') {
            $builder->where('attendance.user_id', $userId)
                    ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
                    ->where('student_assignment.student_id', $userId)
                    ->where('student_assignment.deleted_at IS NULL');
        } elseif ($this->userRole === 'teacher') {
            $builder->join('class', 'class.class_id = class_session.class_id')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->where('teacher_assignment.teacher_id', $userId)
                    ->where('teacher_assignment.deleted_at IS NULL');
        } elseif ($this->userRole === 'admin') {
            // Admins can filter by a specific user if provided, else get all
            if ($userId) {
                $builder->where('attendance.user_id', $userId);
            }
        } else {
            return [];
        }

        // Apply optional filters
        if ($startDate) {
            $builder->where('class_session.open_datetime >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('class_session.open_datetime <=', $endDate . ' 23:59:59');
        }
        if ($classId) {
            $builder->where('class_session.class_id', $classId);
        }

        $results = $builder->findAll();

        // Initialize chart data structure
        $chartData = [
            'labels' => [], // Dates
            'datasets' => [
                ['label' => 'Present', 'data' => [], 'backgroundColor' => 'rgba(75, 192, 192, 0.5)'],
                ['label' => 'Absent', 'data' => [], 'backgroundColor' => 'rgba(255, 99, 132, 0.5)'],
                ['label' => 'Late', 'data' => [], 'backgroundColor' => 'rgba(255, 206, 86, 0.5)'],
                ['label' => 'Unmarked', 'data' => [], 'backgroundColor' => 'rgba(128, 128, 128, 0.5)']
            ]
        ];

        // Extract unique dates
        $dates = array_unique(array_column($results, 'date'));
        sort($dates); // Sort dates chronologically
        $chartData['labels'] = $dates;

        // Initialize data arrays
        $statusMap = [
            'present' => 0,
            'absent' => 1,
            'late' => 2,
            'unmarked' => 3
        ];
        foreach ($chartData['datasets'] as &$dataset) {
            $dataset['data'] = array_fill(0, count($dates), 0);
        }

        // Populate data
        foreach ($results as $row) {
            $dateIndex = array_search($row['date'], $dates);
            if ($dateIndex !== false && isset($statusMap[$row['status']])) {
                $chartData['datasets'][$statusMap[$row['status']]]['data'][$dateIndex] = (int)$row['count'];
            }
        }

        return $chartData;
    }

    public function getAttendanceForSession(int $sessionId): array
    {
        return $this->attendanceModel
            ->select('attendance.*, user.first_name, user.last_name')
            ->join('user', 'user.user_id = attendance.user_id')
            ->where('attendance.class_session_id', $sessionId)
            ->where('attendance.deleted_at IS NULL')
            ->findAll();
    }

    public function bulkMarkAttendance(int $sessionId, array $attendanceData, int $teacherId): bool
    {
        $session = $this->classSessionModel->find($sessionId);
        if (!$session || !$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            throw new ValidationException('Unauthorized or invalid session.');
        }
        foreach ($attendanceData as $userId => $status) {
            $this->attendanceModel->save([
                'user_id' => $userId,
                'class_session_id' => $sessionId,
                'status' => $status,
                'is_manual' => 1,
                'marked_at' => date('Y-m-d H:i:s')
            ]);
        }
        return true;
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
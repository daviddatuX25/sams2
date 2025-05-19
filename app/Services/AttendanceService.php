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
use App\Traits\ServiceExceptionTrait;

class AttendanceService
{
    use ServiceExceptionTrait;

    protected AttendanceModel $attendanceModel;
    protected ?AttendanceLogsModel $attendanceLogsModel;
    protected ?AttendanceLeaveModel $attendanceLeaveModel;
    protected ?ClassSessionModel $classSessionModel;
    protected ?TrackerModel $trackerModel;

    public function __construct(
        ?AttendanceModel $attendanceModel = null,
        ?AttendanceLogsModel $attendanceLogsModel = null,
        ?AttendanceLeaveModel $attendanceLeaveModel = null,
        ?ClassSessionModel $classSessionModel = null,
        ?TrackerModel $trackerModel = null
    ) {
        $this->attendanceModel = $attendanceModel ?? new AttendanceModel();
        $this->attendanceLogsModel = $attendanceLogsModel;
        $this->attendanceLeaveModel = $attendanceLeaveModel;
        $this->classSessionModel = $classSessionModel;
        $this->trackerModel = $trackerModel;
    }

    /**
     * Log an attendance action (time_in, time_out, auto) for a user.
     */
    public function logAttendance(int $userId, int $classSessionId, int $trackerId, string $action): int
    {
        $this->attendanceLogsModel ??= new AttendanceLogsModel();

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

        $this->throwBusinessRule('Failed to log attendance');
    }

    /**
     * Retrieve attendance by session ID.
     */
    public function getAttendanceBySession(int $sessionId): array
    {
        $sessionModel = new \App\Models\ClassSessionModel();
        if (!$sessionModel->where('class_session_id', $sessionId)->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Class Session', $sessionId);
        }

        return $this->attendanceModel
            ->select('attendance_id, session_id, student_id, attendance_status, time_in, time_out, remarks')
            ->where('session_id', $sessionId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Retrieve attendance by student ID.
     */
    public function getAttendanceByStudent(int $studentId): array
    {
        $userModel = new \App\Models\UserModel();
        $student = $userModel->where('user_id', $studentId)->where('role', 'student')->where('deleted_at IS NULL')->first();
        if (!$student) {
            $this->throwNotFound('Student', $studentId);
        }

        return $this->attendanceModel
            ->select('attendance_id, session_id, student_id, attendance_status, time_in, time_out, remarks')
            ->where('student_id', $studentId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Bulk update attendance records for a session.
     */
    public function bulkUpdateAttendance(int $sessionId, array $attendanceData): bool
    {
        $sessionModel = new \App\Models\ClassSessionModel();
        if (!$sessionModel->where('class_session_id', $sessionId)->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Class Session', $sessionId);
        }

        $rules = [
            '*.student_id' => 'required|is_natural_no_zero',
            '*.attendance_status' => 'required|in_list[present,absent,late,excused]',
            '*.time_in' => 'permit_empty|valid_time',
            '*.time_out' => 'permit_empty|valid_time',
            '*.remarks' => 'permit_empty|max_length[65535]'
        ];

        if (!\Config\Services::validation()->setRules($rules)->run($attendanceData)) {
            $this->throwValidationError(implode(', ', \Config\Services::validation()->getErrors()));
        }

        $userModel = new \App\Models\UserModel();
        foreach ($attendanceData as $data) {
            // Ensure student exists
            $student = $userModel->where('user_id', $data['student_id'])->where('role', 'student')->where('deleted_at IS NULL')->first();
            if (!$student) {
                $this->throwNotFound('Student', $data['student_id']);
            }

            // Find existing attendance record
            $existing = $this->attendanceModel
                ->where('session_id', $sessionId)
                ->where('student_id', $data['student_id'])
                ->where('deleted_at IS NULL')
                ->first();

            $data['session_id'] = $sessionId;
            if ($existing) {
                // Update existing record
                $this->attendanceModel->update($existing['attendance_id'], $data);
            } else {
                // Create new record
                $this->attendanceModel->insert($data);
            }
        }

        return true;
    }

    /**
     * Retrieve attendance records for a student.
     */
    public function student_getAttendanceByUser(int $studentId): array
    {
        return $this->attendanceModel
            ->select('attendance.*')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
            ->where('attendance.user_id', $studentId)
            ->where('student_assignment.student_id', $studentId)
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->where('student_assignment.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Retrieve attendance records for a teacher.
     */
    public function teacher_getAttendanceByUser(int $teacherId): array
    {
        return $this->attendanceModel
            ->select('attendance.*')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('class', 'class.class_id = class_session.class_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
            ->where('teacher_assignment.teacher_id', $teacherId)
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->where('teacher_assignment.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Retrieve all attendance records for an admin.
     */
    public function admin_getAttendanceByUser(?int $userId = null): array
    {
        $builder = $this->attendanceModel
            ->select('attendance.*')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL');

        if ($userId) {
            $builder->where('attendance.user_id', $userId);
        }

        return $builder->findAll();
    }

    /**
     * Mark attendance for a user in a class session.
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
     * Calculate attendance rate for a student in a term.
     */
    public function student_getAttendanceRate(int $studentId, int $termId): float
    {
        $this->classSessionModel ??= new ClassSessionModel();

        $totalSessions = $this->classSessionModel
            ->select('class_session.class_session_id')
            ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
            ->where('student_assignment.student_id', $studentId)
            ->where('class_session.status', 'marked')
            ->where('class_session.deleted_at IS NULL')
            ->where('student_assignment.deleted_at IS NULL')
            ->where('student_assignment.enrollment_term_id', $termId)
            ->countAllResults();

        if ($totalSessions === 0) {
            return 0.0;
        }

        $presentSessions = $this->attendanceModel
            ->where('user_id', $studentId)
            ->where('status', 'present')
            ->where('class_session_id IN (
                SELECT class_session_id 
                FROM class_session 
                JOIN student_assignment 
                ON student_assignment.class_id = class_session.class_id 
                WHERE student_assignment.student_id = ' . $studentId . '
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
     * Retrieve attendance logs for a student.
     */
    public function student_getAttendanceLogs(int $studentId, array $filters): array
    {
        $builder = $this->attendanceModel
            ->select('attendance.*, class.class_name, class_session.class_session_name, attendance.marked_at')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('class', 'class.class_id = class_session.class_id')
            ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
            ->where('attendance.user_id', $studentId)
            ->where('student_assignment.student_id', $studentId)
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->where('class.deleted_at IS NULL')
            ->where('student_assignment.deleted_at IS NULL');

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

    /**
     * Retrieve attendance logs for a teacher.
     */
    public function teacher_getAttendanceLogs(int $teacherId, array $filters): array
    {
        $builder = $this->attendanceModel
            ->select('attendance.*, class.class_name, class_session.class_session_name, attendance.marked_at')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('class', 'class.class_id = class_session.class_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
            ->where('teacher_assignment.teacher_id', $teacherId)
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->where('class.deleted_at IS NULL')
            ->where('teacher_assignment.deleted_at IS NULL');

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

    /**
     * Retrieve attendance logs for an admin.
     */
    public function admin_getAttendanceLogs(?int $userId, array $filters): array
    {
        if($this->isAdmin($userId)){
            $this->throwUnauthorized();
        }

        $builder = $this->attendanceModel
            ->select('attendance.*, class.class_name, class_session.class_session_name, attendance.marked_at')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('class', 'class.class_id = class_session.class_id')
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->where('class.deleted_at IS NULL');

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

    /**
     * Retrieve chart data for a student's attendance.
     */
    public function student_getAttendanceChartData(int $studentId, ?string $startDate = null, ?string $endDate = null, ?int $classId = null): array
    {
        $this->classSessionModel ??= new ClassSessionModel();

        $builder = $this->attendanceModel
            ->select('DATE(class_session.open_datetime) as date, attendance.status, COUNT(*) as count')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('student_assignment', 'student_assignment.class_id = class_session.class_id')
            ->where('attendance.user_id', $studentId)
            ->where('student_assignment.student_id', $studentId)
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->where('student_assignment.deleted_at IS NULL')
            ->groupBy(['DATE(class_session.open_datetime)', 'attendance.status']);

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

        return $this->buildChartData($builder->findAll());
    }

    /**
     * Retrieve chart data for a teacher's attendance records.
     */
    public function teacher_getAttendanceChartData(int $teacherId, ?string $startDate = null, ?string $endDate = null, ?int $classId = null): array
    {
        $this->classSessionModel ??= new ClassSessionModel();

        $builder = $this->attendanceModel
            ->select('DATE(class_session.open_datetime) as date, attendance.status, COUNT(*) as count')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->join('class', 'class.class_id = class_session.class_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
            ->where('teacher_assignment.teacher_id', $teacherId)
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->where('teacher_assignment.deleted_at IS NULL')
            ->groupBy(['DATE(class_session.open_datetime)', 'attendance.status']);

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

        return $this->buildChartData($builder->findAll());
    }

    /**
     * Retrieve chart data for an admin.
     */
    public function admin_getAttendanceChartData(?int $userId = null, ?string $startDate = null, ?string $endDate = null, ?int $classId = null): array
    {
        if($this->isAdmin($userId)){
            $this->throwUnauthorized();
        }

        $this->classSessionModel ??= new ClassSessionModel();

        $builder = $this->attendanceModel
            ->select('DATE(class_session.open_datetime) as date, attendance.status, COUNT(*) as count')
            ->join('class_session', 'class_session.class_session_id = attendance.class_session_id')
            ->where('attendance.deleted_at IS NULL')
            ->where('class_session.deleted_at IS NULL')
            ->groupBy(['DATE(class_session.open_datetime)', 'attendance.status']);

        if ($userId) {
            $builder->where('attendance.user_id', $userId);
        }
        if ($startDate) {
            $builder->where('class_session.open_datetime >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('class_session.open_datetime <=', $endDate . ' 23:59:59');
        }
        if ($classId) {
            $builder->where('class_session.class_id', $classId);
        }

        return $this->buildChartData($builder->findAll());
    }

    /**
     * Retrieve attendance for a session.
     */
    public function getAttendanceForSession(int $sessionId): array
    {
        return $this->attendanceModel
            ->select('attendance.*, user.first_name, user.last_name')
            ->join('user', 'user.user_id = attendance.user_id')
            ->where('attendance.class_session_id', $sessionId)
            ->where('attendance.deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Bulk mark attendance for a session by a teacher.
     */
   public function teacher_bulkMarkAttendance(int $sessionId, array $attendanceData, int $teacherId): bool 
    {
        $this->classSessionModel ??= new ClassSessionModel();
        $session = $this->classSessionModel->find($sessionId);

        if (!$session) {
            $this->throwNotFound('Session Id', $sessionId);
        }

        if (!$this->isTeacherAuthorized($teacherId, $session['class_id'])) {
            $this->throwUnauthorized('Unauthorized to mark attendance.');
        }

        foreach ($attendanceData as $userId => $status) {
            // Check if attendance already exists
            $existing = $this->attendanceModel
                ->where('user_id', $userId)
                ->where('class_session_id', $sessionId)
                ->first();

            if ($existing) {
                // Optional: skip or update depending on business rule
                // Example: Update existing status
                $this->attendanceModel->update($existing['attendance_id'], [
                    'status' => $status,
                    'is_manual' => 1,
                    'marked_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Insert new
                $this->attendanceModel->save([
                    'user_id' => $userId,
                    'class_session_id' => $sessionId,
                    'status' => $status,
                    'is_manual' => 1,
                    'marked_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return true;
    }


    /**
     * Build chart data structure from query results.
     */
    private function buildChartData(array $results): array
    {
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
        sort($dates);
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

    private function isAdmin(int $adminId): bool
    {
        return (new \App\Models\UserModel)->find($adminId)['role'] === 'admin';
    }


    public function getTodayAttendanceRate(): float
    {
        $today = date('Y-m-d');
        $this->classSessionModel ??= new ClassSessionModel();
        $sessionsToday = $this->classSessionModel->where('DATE(open_datetime)', $today)->where('status', 'marked')->findAll();
        $totalExpected = 0;
        $totalPresent = 0;
        foreach ($sessionsToday as $session) {
            $studentsInClass = (new \App\Models\StudentAssignmentModel())->where('class_id', $session['class_id'])->countAllResults();
            $presentInSession = $this->attendanceModel->where('class_session_id', $session['class_session_id'])->where('status', 'present')->countAllResults();
            $totalExpected += $studentsInClass;
            $totalPresent += $presentInSession;
        }
        return $totalExpected == 0 ? 0.0 : round(($totalPresent / $totalExpected) * 100, 2);
    }

    public function getAttendance(): array
    {
        return $this->attendanceModel->where('deleted_at IS NULL')->findAll();
    }

    public function createAttendance(array $data): int
    {
        $validation = \Config\Services::validation();
        $rules = [
            'user_id' => 'required|is_natural_no_zero',
            'class_session_id' => 'required|is_natural_no_zero',
            'status' => 'required|in_list[present,absent,late,excused]'
        ];
        if (!$validation->setRules($rules)->run($data)) {
            throw new \CodeIgniter\Validation\Exceptions\ValidationException(implode(', ', $validation->getErrors()));
        }
        $data['is_manual'] = 1;
        $data['marked_at'] = date('Y-m-d H:i:s');
        $this->attendanceModel->insert($data);
        return $this->attendanceModel->insertID();
    }

    public function updateAttendance(int $attendanceId, array $data): bool
    {
        return $this->attendanceModel->update($attendanceId, $data);
    }

    public function deleteAttendance(int $attendanceId): bool
    {
        return $this->attendanceModel->delete($attendanceId);
    }
}
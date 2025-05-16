<?php

namespace App\Services;

use CodeIgniter\Exceptions\PageNotFoundException;

class StudentService
{
    protected $attendanceService;
    protected $classService;
    protected $scheduleService;
    protected $notificationService;
    protected $leaveService;
    protected $userService;
    protected $enrollmentService;
    protected $studentService;

    public function __construct(
        AttendanceService $attendanceService,
        ClassService $classService,
        ScheduleService $scheduleService,
        NotificationService $notificationService,
        LeaveService $leaveService,
        UserService $userService,
        EnrollmentService $enrollmentService,
        ClassSessionService $classSessionService
    ) {
        $this->attendanceService = $attendanceService;
        $this->classService = $classService;
        $this->scheduleService = $scheduleService;
        $this->notificationService = $notificationService;
        $this->leaveService = $leaveService;
        $this->userService = $userService;
        $this->enrollmentService = $enrollmentService;
    }

    public function getDashboardData(int $userId): array
    {
        $classes = $this->classService->getStudentClasses($userId);
        $schedules = $this->scheduleService->getScheduleByUser($userId, 'student');
        // $notifications = $this->notificationService->getUnreadNotifications($userId); HAS ERRIR
        // $todaySessions = $this->scheduleService->getStudentSessionsForToday($userId);
        return [
            'classes' => $classes,
            'schedules' => $schedules,
            'todaySessions' => [],
            'attendanceRate' => 85,
            'unreadCount' => 12,
            // 'notifications' => $notifications
        ];
    }

    public function getClasses(int $userId): array
    {
        return $this->classService->getStudentClasses($userId);
    }

    public function getClassDetails(int $userId, int $classId): array
    {
        if (!$this->enrollmentService->isStudentEnrolled($userId, $classId)) {
            throw new PageNotFoundException('Class not found or unauthorized');
        }
        $class = $this->classService->getClassDetails($classId);
        $sessions = $this->classService->getClassSessions($classId);

        // Fetch attendance for the student for all sessions in the class
        $attendance = [];
        if (!empty($sessions)) {
            $sessionIds = array_column($sessions, 'class_session_id');
            $attendanceRecords = $this->attendanceService->getStudentAttendanceForSessions($userId, $sessionIds);
            foreach ($attendanceRecords as $record) {
                $attendance[$record['class_session_id']] = [
                    'status' => $record['status'],
                    'marked_at' => $record['marked_at'],
                    'is_manual' => $record['is_manual']
                ];
            }
        }

        return [
            'class' => $class,
            'sessions' => $sessions,
            'attendance' => $attendance
        ];
    }


    public function getAttendanceLogs(int $userId, array $filters): array
    {
        return $this->attendanceService->getStudentAttendanceLogs($userId, $filters);
    }

    public function getSchedule(int $userId, string $viewMode): array
    {
        return $this->scheduleService->getStudentSchedule($userId, $viewMode);
    }

    public function updateProfile(int $userId, array $userData): void
    {
        $this->userService->updateProfile($userId, $userData);
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): void
    {
        $this->userService->changePassword($userId, $oldPassword, $newPassword);
    }

    public function updateProfilePicture(int $userId, string $photoPath): void
    {
        $this->userService->updateProfilePicture($userId, $photoPath);
    }

    public function createLeaveRequest(int $userId, array $leaveData): void
    {
        $leaveData['user_id'] = $userId;
        $leaveData['status'] = 'pending';
        $leaveData['datetimestamp_created'] = date('Y-m-d H:i:s');
        $this->leaveService->createLeave($leaveData);
        $this->notificationService->notifyTeacher($leaveData['class_id'], 'New leave request submitted', 'info');
    }

    public function cancelLeaveRequest(int $userId, int $leaveId): void
    {
        $this->leaveService->cancelLeave($userId, $leaveId);
    }

    public function getLeaveRequestsForStudent(int $userId): array
    {
        return $this->leaveService->getStudentLeaveRequests($userId);
    }

    public function getUser(int $userId): array
    {
        return $this->userService->getUser($userId);
    }
}
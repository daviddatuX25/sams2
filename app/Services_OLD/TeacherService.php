<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\ClassSessionModel;
use App\Models\AttendanceModel;
use App\Models\AttendanceLeaveModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class TeacherService
{
    protected $attendanceService;
    protected $classService;
    protected $scheduleService;
    protected $notificationService;
    protected $leaveService;
    protected $userService;
    protected $enrollmentService;
    protected $reportService;

    public function __construct(
        AttendanceService $attendanceService,
        ClassService $classService,
        ScheduleService $scheduleService,
        NotificationService $notificationService,
        LeaveService $leaveService,
        UserService $userService,
        EnrollmentService $enrollmentService,
        ReportService $reportService
    ) {
        $this->attendanceService = $attendanceService;
        $this->classService = $classService;
        $this->scheduleService = $scheduleService;
        $this->notificationService = $notificationService;
        $this->leaveService = $leaveService;
        $this->userService = $userService;
        $this->enrollmentService = $enrollmentService;
        $this->reportService = $reportService;
    }

    public function getDashboardData(int $userId): array
    {
        $classes = $this->classService->getTeacherClasses($userId);
        $schedules = $this->scheduleService->getTeacherSchedule($userId, 'week');
        $notifications = $this->notificationService->getUnreadNotifications($userId);
        return [
            'classes' => $classes,
            'schedules' => $schedules,
            'notifications' => $notifications
        ];
    }

    public function getClasses(int $userId): array
    {
        return $this->classService->getTeacherClasses($userId);
    }

    public function getClassDetails(int $userId, int $classId): array
    {
        $class = $this->classService->getClassDetails($classId);
        if (!$this->enrollmentService->isTeacherAssigned($userId, $classId)) {
            throw new PageNotFoundException('Class not found or unauthorized');
        }
        $sessions = $this->classService->getClassSessions($classId);
        $students = $this->enrollmentService->getClassStudents($classId);
        return [
            'class' => $class,
            'sessions' => $sessions,
            'students' => $students
        ];
    }

    public function startSession(int $classId, array $sessionData): void
    {
        $sessionData['class_id'] = $classId;
        $sessionData['status'] = 'pending';
        $this->classService->createSession($sessionData);
        $this->notificationService->notifyClassStudents($classId, 'New session started: ' . $sessionData['class_session_name'], 'info');
    }

    public function updateSession(int $sessionId, array $sessionData): void
    {
        $this->classService->updateSession($sessionId, $sessionData);
    }

    public function deleteSession(int $sessionId): void
    {
        $this->classService->deleteSession($sessionId);
    }

    public function updateAttendance(int $sessionId, array $attendanceData): void
    {
        foreach ($attendanceData as $userId => $status) {
            $this->attendanceService->updateAttendance($sessionId, $userId, [
                'status' => $status,
                'is_manual' => 1,
                'marked_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function getLeaveRequests(int $userId, string $statusFilter = 'all'): array
    {
        return $this->leaveService->getTeacherLeaveRequests($userId, $statusFilter);
    }

    public function approveLeave(int $leaveId): void
    {
        $this->leaveService->updateLeaveStatus($leaveId, 'approved', date('Y-m-d H:i:s'));
    }

    public function rejectLeave(int $leaveId): void
    {
        $this->leaveService->updateLeaveStatus($leaveId, 'rejected', date('Y-m-d H:i:s'));
    }

    public function getSchedule(int $userId, string $viewMode): array
    {
        return $this->scheduleService->getTeacherSchedule($userId, $viewMode);
    }

    public function getReports(int $userId, array $filters): array
    {
        $reportData = $this->reportService->getTeacherAttendanceReport($userId, $filters);
        $chartData = $this->getReportsChartData($userId, $filters);
        return [
            'classes' => $reportData['classes'],
            'attendance' => $reportData['attendance'],
            'filters' => $filters,
            'chartData' => $chartData
        ];
    }

    public function getReportsChartData(int $userId, array $filters): array
    {
        $attendanceData = $this->reportService->getTeacherAttendanceReport($userId, $filters)['attendance'];
        
        // Initialize chart data structure
        $labels = [];
        $datasets = [
            'present' => ['label' => 'Present', 'data' => [], 'backgroundColor' => 'rgba(75, 192, 192, 0.5)'],
            'absent' => ['label' => 'Absent', 'data' => [], 'backgroundColor' => 'rgba(255, 99, 132, 0.5)'],
            'late' => ['label' => 'Late', 'data' => [], 'backgroundColor' => 'rgba(255, 206, 86, 0.5)'],
            'unmarked' => ['label' => 'Unmarked', 'data' => [], 'backgroundColor' => 'rgba(153, 102, 255, 0.5)']
        ];

        // Aggregate data by date
        $dateCounts = [];
        foreach ($attendanceData as $record) {
            $date = date('Y-m-d', strtotime($record['marked_at']));
            if (!isset($dateCounts[$date])) {
                $dateCounts[$date] = ['present' => 0, 'absent' => 0, 'late' => 0, 'unmarked' => 0];
            }
            $dateCounts[$date][$record['status']]++;
        }

        // Populate labels and datasets
        ksort($dateCounts); // Sort by date
        foreach ($dateCounts as $date => $counts) {
            $labels[] = $date;
            $datasets['present']['data'][] = $counts['present'];
            $datasets['absent']['data'][] = $counts['absent'];
            $datasets['late']['data'][] = $counts['late'];
            $datasets['unmarked']['data'][] = $counts['unmarked'];
        }

        return [
            'labels' => $labels,
            'datasets' => array_values($datasets)
        ];
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

    public function getUser(int $userId): array
    {
        return $this->userService->getUser($userId);
    }
}
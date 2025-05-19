<?php
namespace App\Controllers;

use App\Services\AttendanceLeaveService;
use App\Services\AttendanceService;
use App\Services\ClassService;
use App\Services\ClassSessionService;
use App\Services\NotificationService;
use App\Services\ScheduleService;
use App\Services\UserService;
use CodeIgniter\Validation\Exceptions\ValidationException;

class TeacherController extends BaseController
{
    protected $attendanceLeaveService;
    protected $attendanceService;
    protected $classService;
    protected $classSessionService;
    protected $notificationService;
    protected $scheduleService;
    protected $userService;

    public function __construct()
    {
        $this->attendanceLeaveService = new AttendanceLeaveService();
        $this->attendanceService = new AttendanceService();
        $this->classSessionService = new ClassSessionService();
        $this->notificationService = new NotificationService();
        $this->scheduleService = new ScheduleService();
    }

    public function index()
    {   
        $userId = session()->get('user_id');
        try {
            $data = [
                'todaySessions' => $this->classSessionService->teacher_getClassSessionsByDate($userId, date('Y-m-d')),
                'pendingLeaveCount' => count($this->attendanceLeaveService->teacher_getLeaveRequests($userId, 'pending')),
                'unreadCount' => count($this->notificationService->getNotifications($userId, 'unread')),
                'navbar' => 'teacher',
                'currentSegment' => 'dashboard'
            ];
            return view('teacher/dashboard', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }

    public function classes()
    {
        $userId = session()->get('user_id');
        $termId = session()->get('activeTerm')['enrollment_term_id'];
        try {
            $data = [
                'classes' => (new ClassService)->teacher_getUserClasses($userId, $termId),
                'navbar' => 'teacher',
                'currentSegment' => 'classes'
            ];
            return view('teacher/classes', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }

    public function classDetail($classId)
    {
    return $this->handleAction(function() use($classId){
        $userId = session()->get('user_id');
            if ($this->request->getMethod() === 'POST') {
                $action = $this->request->getPost('action');
                if ($action === 'create_session') {
                    $postData = $this->request->getPost();
                    (new ClassSessionService)->teacher_createClassSession($classId, $postData, $userId);
                    session()->setFlashdata(['success' => 'Custom class session created.', 'subNavActive' => 'sessions']);
                } elseif ($action === 'update_session') {
                    $postData = $this->request->getPost();
                    $this->classSessionService->teacher_updateClassSession((int)$postData['class_session_id'], $postData, $userId);
                    session()->setFlashdata(['success' => 'Class session updated.', 'subNavActive' => 'sessions']);
                } elseif ($action === 'mark_finished'){
                    $this->classSessionService->teacher_finishClassSession($this->request->getPost('session_id'), $userId);
                    session()->setFlashdata(['success' => 'Session marked as finished.', 'subNavActive' => 'sessions']);
                } else if ($action === 'cancel_session'){
                    $this->classSessionService->teacher_cancelClassSession($this->request->getPost('session_id'), $userId);
                    session()->setFlashdata(['success' => 'Session cancelled.', 'subNavActive' => 'sessions']);
                } elseif ($action === 'delete_session') {
                    $this->classSessionService->teacher_deleteClassSession($this->request->getPost('session_id'), $userId);
                    session()->setFlashdata(['success' => 'Session deleted.', 'subNavActive' => 'sessions']);
                } elseif ($action === 'update_attendance') {
                    $this->attendanceService->teacher_bulkMarkAttendance($this->request->getPost('session_id'), $this->request->getPost('attendance'), $userId);
                    $this->classSessionService->teacher_markClassSession($this->request->getPost('session_id'), $userId);
                    session()->setFlashdata(['success' => 'Attendance updated and class session is marked.', 'subNavActive' => 'attendance']);
                }
                return redirect()->back()->withInput();
            }

            $selectedSessionId = $this->request->getGet('session_id');
            $attendance = $selectedSessionId ? $this->attendanceService->getAttendanceForSession($selectedSessionId) : [];
            $attendanceStats = ['present' => 0, 'absent' => 0, 'late' => 0, 'unmarked' => 0];
            foreach ($attendance as $record) {
                $attendanceStats[$record['status']]++;
            }

            $data = [
                'class' => (new ClassService)->teacher_getClassInfoByUser($userId, $classId),
                'roster' => (new ClassService)->getClassRosterByUser((int)$classId),
                'sessions' => $this->classSessionService->teacher_getClassSessionsByClass($classId, $userId) ?? [],
                'attendance' => array_column($attendance, 'status', 'user_id'),
                'selected_session_id' => $selectedSessionId,
                'attendanceStats' => $attendanceStats,
                'navbar' => 'teacher',
                'currentSegment' => 'classes'
            ];
            return view('teacher/class_detail', $data);
        });
    }

    public function leaveRequests()
    {
        $attendanceLeaveService = (new AttendanceLeaveService);
        $userId = session()->get('user_id');
        $statusFilter = $this->request->getGet('status') ?? 'all';
        try {
            if ($this->request->getMethod() === 'POST') {
                $leaveId = $this->request->getPost('leave_id');
                $action = $this->request->getPost('action');
                if ($action === 'approve') {
                    $attendanceLeaveService->teacher_approveLeaveRequest($leaveId, $userId);
                    session()->setFlashdata('success', 'Leave approved.');
                } elseif ($action === 'reject') {
                    $attendanceLeaveService->teacher_rejectLeaveRequest($leaveId, $userId);
                    session()->setFlashdata('success', 'Leave rejected.');
                }
                return redirect()->to('/teacher/leave_requests');
            }

            $data = [
                'leaveRequests' => $attendanceLeaveService->teacher_getLeaveRequests($userId, $statusFilter === 'all' ? null : $statusFilter),
                'statusFilter' => $statusFilter,
                'navbar' => 'teacher',
                'currentSegment' => 'leave_requests'
            ];
            return view('teacher/leave_requests', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }

    public function schedule()
    {
        $userId = session()->get('user_id');
        $viewMode = $this->request->getGet('view') ?? 'week';
        try {
            $scheduleData = (new scheduleService)->getUserSchedule($userId);
            $data = [
                'events' => json_encode($scheduleData['events']),
                'termStart' => $scheduleData['termStart'],
                'termEnd' => $scheduleData['termEnd'],
                'viewMode' => $viewMode,
                'navbar' => 'teacher',
                'currentSegment' => 'schedule'
            ];
            return view('teacher/schedule', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }

    public function reports()
    {
        $userId = session()->get('user_id');
        $filters = $this->request->getGet(['class_id', 'start_date', 'end_date']);
        try {
            $termId = session()->get('activeTerm')['enrollment_term_id'];
            $chartData = $this->attendanceService->teacher_getAttendanceChartData(
                $userId,
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null,
                $filters['class_id'] ?? null
            );
            $data = [
                'chartData' => json_encode($chartData),
                'classes' => (new ClassService)->teacher_getUserClasses($userId, $termId),
                'filters' => $filters,
                'navbar' => 'teacher',
                'currentSegment' => 'reports'
            ];
            return view('teacher/reports', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }
}
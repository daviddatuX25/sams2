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
        $this->classService = new ClassService();
        $this->classSessionService = new ClassSessionService();
        $this->notificationService = new NotificationService();
        $this->scheduleService = new ScheduleService();
        $this->userService = new UserService();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        try {
            $data = [
                'todaySessions' => $this->classSessionService->getClassSessionsByDateAndUser($userId, date('Y-m-d')),
                'pendingLeaveCount' => count($this->attendanceLeaveService->getLeaveRequestsForTeacher($userId, 'pending')),
                'unreadCount' => count($this->notificationService->getUnreadNotificationsByUser($userId)),
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
                'classes' => $this->classService->getUserClasses($userId, $termId),
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
        $userId = session()->get('user_id');
        try {
            if ($this->request->getMethod() === 'POST') {
                $action = $this->request->getPost('action');
                if ($action === 'start_session') {
                    $postData = $this->request->getPost();
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
                    if (!$this->validate($rules)) {
                        throw new ValidationException(implode(', ', $this->validator->getErrors()));
                    }

                    $openDatetime = new \DateTime($postData['open_datetime']);
                    $duration = (int)$postData['duration'];
                    $closeDatetime = (clone $openDatetime)->modify("+{$duration} minutes");
                    helper('main');
                    $sessionData = [
                        'class_session_name' => $postData['class_session_name'],
                        'class_session_description' => $postData['session_description'] ?? null,
                        'open_datetime' => $openDatetime->format('Y-m-d H:i:s'),
                        'close_datetime' => $closeDatetime->format('Y-m-d H:i:s'),
                        'status' => 'active', // Default for creation
                        'attendance_method' => $postData['attendance_method'],
                        'auto_mark_attendance' => $postData['auto_mark_attendance'],
                        'time_in_threshold' => $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['time_in_threshold']) : null,
                        'time_out_threshold' => $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['time_out_threshold']) : null,
                        'late_threshold' => $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['late_threshold']) : null,
                    ];
                    $this->classSessionService->createClassSession($classId, $sessionData, $userId);
                    session()->setFlashdata('success', 'Custom class session created.');
                } elseif ($action === 'update_session') {
                    $postData = $this->request->getPost();
                    $rules = [
                        'class_session_id' => 'required|is_natural_no_zero',
                        'open_datetime' => 'required|valid_date',
                        'duration' => 'required|integer|greater_than[0]',
                        'class_session_name' => 'required|string|max_length[255]',
                        'session_description' => 'permit_empty|string|max_length[1000]',
                        'status' => 'required|in_list[active,marked,cancelled]',
                        'attendance_method' => 'required|in_list[manual,automatic]',
                        'auto_mark_attendance' => 'required|in_list[yes,no]'
                    ];
                    if ($postData['auto_mark_attendance'] === 'yes') {
                        $rules['time_in_threshold'] = 'required|integer|greater_than_equal_to[0]';
                        $rules['time_out_threshold'] = 'required|integer|greater_than_equal_to[0]';
                        $rules['late_threshold'] = 'required|integer|greater_than_equal_to[0]';
                    }
                    if (!$this->validate($rules)) {
                        throw new ValidationException(implode(', ', $this->validator->getErrors()));
                    }

                    $openDatetime = new \DateTime($postData['open_datetime']);
                    $duration = (int)$postData['duration'];
                    $closeDatetime = (clone $openDatetime)->modify("+{$duration} minutes");
                    helper('main');
                    $sessionData = [
                        'class_session_name' => $postData['class_session_name'],
                        'class_session_description' => $postData['session_description'] ?? null,
                        'open_datetime' => $openDatetime->format('Y-m-d H:i:s'),
                        'close_datetime' => $closeDatetime->format('Y-m-d H:i:s'),
                        'status' => $postData['status'],
                        'attendance_method' => $postData['attendance_method'],
                        'auto_mark_attendance' => $postData['auto_mark_attendance'],
                        'time_in_threshold' => $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['time_in_threshold']) : null,
                        'time_out_threshold' => $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['time_out_threshold']) : null,
                        'late_threshold' => $postData['auto_mark_attendance'] === 'yes' ? minutes_to_time((int)$postData['late_threshold']) : null,
                    ];
                    $this->classSessionService->updateClassSession((int)$postData['class_session_id'], $sessionData, $userId);
                    session()->setFlashdata('success', 'Class session updated.');
                } elseif ($action === 'delete_session') {
                    $this->classSessionService->deleteClassSession($this->request->getPost('session_id'), $userId);
                    session()->setFlashdata('success', 'Session deleted.');
                } elseif ($action === 'update_attendance') {
                    $this->attendanceService->bulkMarkAttendance($this->request->getPost('session_id'), $this->request->getPost('attendance'), $userId);
                    session()->setFlashdata('success', 'Attendance updated.');
                }
                return redirect()->to("/teacher/classes/$classId");
            }

            $selectedSessionId = $this->request->getGet('session_id');
            $attendance = $selectedSessionId ? $this->attendanceService->getAttendanceForSession($selectedSessionId) : [];
            $attendanceStats = ['present' => 0, 'absent' => 0, 'late' => 0, 'unmarked' => 0];
            foreach ($attendance as $record) {
                $attendanceStats[$record['status']]++;
            }

            $data = [
                'class' => $this->classService->getClassInfoByUser($userId, $classId),
                'roster' => $this->classService->getClassRosterByUser($classId),
                'sessions' => $this->classSessionService->getClassSessionsByUser($classId, $userId),
                'attendance' => array_column($attendance, 'status', 'user_id'),
                'selected_session_id' => $selectedSessionId,
                'attendanceStats' => $attendanceStats,
                'navbar' => 'teacher',
                'currentSegment' => 'classes'
            ];
            return view('teacher/class_detail', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher/classes');
        }
    }

    public function leaveRequests()
    {
        $userId = session()->get('user_id');
        $statusFilter = $this->request->getGet('status') ?? 'all';
        try {
            if ($this->request->getMethod() === 'POST') {
                $leaveId = $this->request->getPost('leave_id');
                $action = $this->request->getPost('action');
                if ($action === 'approve') {
                    $this->attendanceLeaveService->approveLeaveRequest($leaveId, $userId);
                    session()->setFlashdata('success', 'Leave approved.');
                } elseif ($action === 'reject') {
                    $this->attendanceLeaveService->rejectLeaveRequest($leaveId, $userId);
                    session()->setFlashdata('success', 'Leave rejected.');
                }
                return redirect()->to('/teacher/leave_requests');
            }

            $data = [
                'leaveRequests' => $this->attendanceLeaveService->getLeaveRequestsForTeacher($userId, $statusFilter === 'all' ? null : $statusFilter),
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
            $scheduleData = $this->scheduleService->getUserSchedule($userId);
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
            $chartData = $this->attendanceService->getAttendanceChartData(
                $userId,
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null,
                $filters['class_id'] ?? null
            );
            $data = [
                'chartData' => json_encode($chartData),
                'classes' => $this->classService->getUserClasses($userId, $termId),
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

    public function profile()
    {
        $userId = session()->get('user_id');
        try {
            if ($this->request->getMethod() === 'POST') {
                $action = $this->request->getPost('action');
                $postData = $this->request->getPost();
                $file = $this->request->getFile('profile_picture');
                $result = $this->userService->handleProfileAction($userId, $action, $postData, $file, $this->request->isAJAX());
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON($result);
                }
                session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
                return redirect()->to('/teacher/profile');
            }

            $data = [
                'user' => $this->userService->getUser($userId),
                'navbar' => 'teacher',
                'currentSegment' => 'profile',
                'validation' => \Config\Services::validation()
            ];
            return view('shared/profile', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }

    public function logout()
    {
        try {
            $userService = new UserService(session()->get('role'));
            if ($userService->logout()) {
                return redirect()->to('auth/teacher/login');
            }
            throw new \Exception('Logout failed.');
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }
}
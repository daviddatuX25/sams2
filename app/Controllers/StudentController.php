<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Services\ClassService;
use App\Services\ClassSessionService;
use App\Services\AttendanceLeaveService;
use App\Services\AttendanceService;
use App\Services\ScheduleService;
use App\Services\NotificationService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;

class StudentController extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');
        $activeTermId = session()->get('activeTerm')['enrollment_term_id'];
        try {
            $classService = new ClassService(session()->get('role'));
            $classSessionService = new ClassSessionService(session()->get('role'));
            $notificationService = new NotificationService(session()->get('role'));
            $attendanceService = new AttendanceService(session()->get('role'));

            $classes = $classService->getUserClasses($userId, $activeTermId);
            $todaySessions = $classSessionService->getClassSessionsByDateAndUser($userId, date('Y-m-d'));
            $notifUnread = $notificationService->getUnreadNotificationsByUser($userId);
            $attendanceRate = $attendanceService->getAttendanceRateByStudent($userId, $activeTermId);
            $data = [
                'classes' => $classes,
                'todaySessions' => $todaySessions,
                'attendanceRate' => $attendanceRate,
                'unreadCount' => count($notifUnread),
                'currentSegment' => 'dashboard',
                'navbar' => 'student'
            ];

            return view('student/dashboard', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function classes()
    {
        $userId = session()->get('user_id');
        $activeTermId = session()->get('activeTerm')['enrollment_term_id'];
        try {
            $classService = new ClassService(session()->get('role'));
            $data = [
                'classes' => $classService->getUserClasses($userId, $activeTermId),
                'navbar' => 'student',
                'currentSegment' => 'classes'
            ];
            return view('student/classes', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function classDetail($classId)
    {
        $userId = session()->get('user_id');
        $activeTermId = session()->get('activeTerm')['enrollment_term_id'];
        try {
            $classService = new ClassService(session()->get('role'));
            $classSessionService = new ClassSessionService(session()->get('role'));
            $attendanceLeaveService = new AttendanceLeaveService(session()->get('role'));
            $attendanceService = new AttendanceService(session()->get('role'));
            if ($this->request->getMethod() === 'POST') {
                $action = $this->request->getPost('action');
                if ($action === 'submit_leave_request') {
                    $postData = $this->request->getPost(['class_id', 'leave_date', 'reason']);
                    $result = $attendanceLeaveService->submitLeaveRequest($userId, $postData, $this->request->isAJAX());
                } elseif ($action === 'cancel_leave_request') {
                    $leaveRequestId = $this->request->getPost('attendance_leave_id');
                    $result = $attendanceLeaveService->cancelLeaveRequest($userId, $leaveRequestId, $this->request->isAJAX());
                } else {
                    throw new ValidationException('Invalid action.');
                }

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON($result);
                }

                if ($result['success']) {
                    session()->setFlashdata('success', $result['message']);
                } else {
                    session()->setFlashdata('error', $result['message']);
                }
                return redirect()->to('/student/classes/' . $classId);
            }

            $classInfo = $classService->getClassInfoByUser($userId, $classId);
            $classRoster = $classService->getClassRosterByUser($classId);
            $classSessions = $classSessionService->getClassSessionsByUser($classId, $userId);
            $leaveRequests = $attendanceLeaveService->getLeaveRequestsByUser($userId);
            $attendanceHistory = $attendanceService->getAttendanceByUser($userId);
            $chartData = $attendanceService->getAttendanceChartData($userId, $classId ?: null);
            $data = [
                'class' => $classInfo,
                'roster' => $classRoster,
                'sessions' => $classSessions,
                'leaveRequests' => $leaveRequests,
                'attendanceHistory' => $attendanceHistory,
                'chartData' => $chartData,
                'navbar' => 'student',
                'currentSegment' => 'classes',
                'validation' => \Config\Services::validation()
            ];
            return view('student/class_detail', $data);
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => []
                ]);
            }
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student/classes');
        }
    }

    public function attendance()
    {
        $userId = session()->get('user_id');
        $filters = $this->request->getGet(['date', 'class_id', 'status']);
        try {
            $classService = new ClassService(session()->get('role'));
            $attendanceService = new AttendanceService(session()->get('role'));

            $data = [
                'attendanceLogs' => $attendanceService->getAttendanceLogs($userId, $filters),
                'filters' => $filters,
                'classes' => $classService->getUserClasses($userId, session()->get('activeTerm')['enrollment_term_id']),
                'currentSegment' => 'attendance',
                'navbar' => 'student'
            ];
            return view('student/attendance', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function schedule()
    {
        $userId = session()->get('user_id');
        $viewMode = $this->request->getGet('view') ?? 'week';
        try {
            $scheduleService = new ScheduleService(session()->get('role'));
            $scheduleData = $scheduleService->getUserSchedule($userId);
            $data = [
                'events' => json_encode($scheduleData['events']),
                'viewMode' => $viewMode,
                'termStart' => $scheduleData['termStart'],
                'termEnd' => $scheduleData['termEnd'],
                'navbar' => 'student',
                'currentSegment' => 'schedule'
            ];
            return view('student/schedule', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function profile()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/auth/student/login')->with('error', 'Please log in.');
        }

        try {
            $userService = new UserService(session()->get('role'));
            if ($this->request->getMethod() === 'POST') {
                $action = $this->request->getPost('action');
                $postData = $this->request->getPost();
                $file = $this->request->getFile('profile_picture');
                if ($file && !$file->isValid()) {
                    $file = null;
                }
                $result = $userService->handleProfileAction($userId, $action, $postData, $file, $this->request->isAJAX());

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON($result);
                }

                if ($result['success']) {
                    session()->setFlashdata('success', $result['message']);
                } else {
                    session()->setFlashdata('error', $result['message']);
                }
                return redirect()->to('student/profile');
            }

            $user = $userService->getUser($userId);
            if (!$user) {
                session()->setFlashdata('error', 'User not found.');
                return redirect()->to('/auth/student/login');
            }

            $data = [
                'user' => $user,
                'navbar' => 'student',
                'currentSegment' => 'profile',
                'validation' => \Config\Services::validation()
            ];
            return view('shared/profile', $data);
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => [],
                    'error_code' => 'server_error'
                ]);
            }
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function changePassword()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/auth/student/login')->with('error', 'Please log in.');
        }

        try {
            $userService = new UserService(session()->get('role'));

            if ($this->request->getMethod() === 'post') {
                $action = $this->request->getPost('action');
                $postData = $this->request->getPost();
                $result = $userService->handleProfileAction($userId, $action, $postData, null, $this->request->isAJAX());

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON($result);
                }

                if ($result['success']) {
                    session()->setFlashdata('success', $result['message']);
                } else {
                    session()->setFlashdata('error', $result['message']);
                }
                return redirect()->to('/student/profile');
            }

            $data = [
                'navbar' => 'student',
                'currentSegment' => 'profile',
                'validation' => \Config\Services::validation()
            ];
            return view('shared/change_password', $data);
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => [],
                    'error_code' => 'server_error'
                ]);
            }
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function notifications()
    {
        $userId = session()->get('user_id');
        $validation = \Config\Services::validation();

        try {
            $notificationService = new NotificationService(session()->get('role'));

            if ($this->request->getMethod() === 'post') {
                $action = $this->request->getPost('action');
                $notificationId = $this->request->getPost('notification_id');
                try {
                    if ($action === 'mark_read') {
                        $notificationService->markNotificationRead($userId, $notificationId);
                        session()->setFlashdata('success', 'Notification marked as read.');
                    } elseif ($action === 'mark_unread') {
                        $notificationService->markNotificationUnread($userId, $notificationId);
                        session()->setFlashdata('success', 'Notification marked as unread.');
                    } else {
                        throw new ValidationException('Invalid action.');
                    }
                } catch (\Exception $e) {
                    session()->setFlashdata('error', $e->getMessage());
                }
                return redirect()->to('/student/notifications');
            }

            $data = [
                'notifications' => $notificationService->getNotifications($userId),
                'unreadCount' => count($notificationService->getUnreadNotificationsByUser($userId)),
                'navbar' => 'student',
                'currentSegment' => 'notifications',
                'validation' => $validation
            ];
            return view('shared/notifications', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function logout()
    {
        try {
            $userService = new UserService(session()->get('role'));
            if ($userService->logout()) {
                return redirect()->to('auth/student/login');
            }
            throw new \Exception('Logout failed.');
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }
}
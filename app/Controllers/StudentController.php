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
        return $this->handleAction(function(){
            $userId = session()->get('user_id');
            $activeTermId = session()->get('activeTerm')['enrollment_term_id'];

            $classes = (new ClassService)->student_getClassInfoByUser($userId, $activeTermId);
            $todaySessions = (new ClassSessionService)->student_getClassSessionsByDate($userId, date('Y-m-d'));
            $notifUnread = (new NotificationService)->getNotifications($userId, 'unread');
            $attendanceRate = (new AttendanceService)->student_getAttendanceRate($userId, $activeTermId);
            $data = [
                'classes' => $classes,
                'todaySessions' => $todaySessions,
                'attendanceRate' => $attendanceRate,
                'unreadCount' => count($notifUnread),
                'currentSegment' => 'dashboard',
                'navbar' => 'student'
            ];
            return view('student/dashboard', $data);
        });
    }

    public function classes()
    {
        $userId = session()->get('user_id');
        $activeTermId = session()->get('activeTerm')['enrollment_term_id'];
        try {
            $data = [
                'classes' => (new ClassService)->student_getUserClasses($userId, $activeTermId),
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
            $classSessionService = new ClassSessionService();
            $attendanceLeaveService = new AttendanceLeaveService();
            $attendanceService = new AttendanceService();
            if ($this->request->getMethod() === 'POST') {
                $action = $this->request->getPost('action');
                if ($action === 'submit_leave_request') {
                    $postData = $this->request->getPost(['class_id', 'leave_date', 'reason']);
                    $result = $attendanceLeaveService->student_submitLeaveRequest($userId, $postData, $this->request->isAJAX());
                } elseif ($action === 'cancel_leave_request') {
                    $leaveRequestId = $this->request->getPost('attendance_leave_id');
                    $result = $attendanceLeaveService->student_cancelLeaveRequest($userId, $leaveRequestId, $this->request->isAJAX());
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
            $classService = new ClassService;
            $classInfo = $classService->student_getClassInfoByUser($userId, $classId);
            $classRoster = $classService->getClassRosterByUser($classId);
            $classSessions = $classSessionService->student_getClassSessionsByClass($classId, $userId);
            $leaveRequests = $attendanceLeaveService->student_getLeaveRequests($userId);
            $attendanceHistory = $attendanceService->student_getAttendanceByUser($userId);
            $chartData = $attendanceService->student_getAttendanceChartData($userId, $classId ?: null);
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
            $data = [
                'attendanceLogs' => (new AttendanceService)->student_getAttendanceLogs($userId, $filters),
                'filters' => $filters,
                'classes' => (new ClassService)->student_getUserClasses($userId, session()->get('activeTerm')['enrollment_term_id']),
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
            $scheduleData = (new ScheduleService)->getUserSchedule($userId);
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

    public function logout()
    {
        try {
            if ((new UserService())->logout()) {
                return redirect()->to('auth/student/login');
            }
            throw new \Exception('Logout failed.');
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }
}
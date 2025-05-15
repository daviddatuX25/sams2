<?php

namespace App\Controllers;

use App\Services\TeacherService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;

class TeacherController extends BaseController
{
    protected $teacherService;

    public function __construct()
    {
        $this->teacherService = new TeacherService(
            new \App\Services\AttendanceService(
                new \App\Models\AttendanceModel(),
                new \App\Models\ClassSessionModel(),
                new \App\Models\AttendanceLogsModel()
            ),
            new \App\Services\ClassService(
                new \App\Models\ClassModel(),
                new \App\Models\ClassSessionModel(),
                new \App\Models\StudentAssignmentModel(),
                new \App\Models\TeacherAssignmentModel()
            ),
            new \App\Services\ScheduleService(
                new \App\Models\ScheduleModel(),
                new \App\Models\ClassModel(),
                new \App\Models\RoomModel()
            ),
            new \App\Services\NotificationService(new \App\Models\NotificationsModel()),
            new \App\Services\LeaveService(
                new \App\Models\AttendanceLeaveModel(),
                new \App\Models\StudentAssignmentModel(),
                new \App\Models\TeacherAssignmentModel()
            ),
            new \App\Services\UserService(new \App\Models\UserModel()),
            new \App\Services\EnrollmentService(
                new \App\Models\StudentAssignmentModel(),
                new \App\Models\TeacherAssignmentModel(),
                new \App\Models\EnrollmentTermModel()
            )
        );
    }

    public function index()
    {
        $userId = session()->get('user_id');
        try {
            $data = $this->teacherService->getDashboardData($userId);
            $data['navbar'] = 'teacher';
            $data['currentSegment'] = 'dashboard';
            return view('teacher/dashboard', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }

    public function classes()
    {
        $userId = session()->get('user_id');
        try {
            $data = ['classes' => $this->teacherService->getClasses($userId)];
            $data['navbar'] = 'teacher';
            $data['currentSegment'] = 'classes';
            return view('teacher/classes', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }

    public function classDetail($classId)
    {
        $userId = session()->get('user_id');
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            try {
                if ($action === 'start_session' || $action === 'update_session') {
                    $sessionData = $this->request->getPost([
                        'class_session_name',
                        'open_datetime',
                        'duration',
                        'attendance_method',
                        'auto_mark_attendance',
                        'time_in_threshold',
                        'time_out_threshold',
                        'late_threshold'
                    ]);
                    if ($action === 'start_session') {
                        $this->teacherService->startSession($classId, $sessionData);
                    } else {
                        $sessionId = $this->request->getPost('class_session_id');
                        $this->teacherService->updateSession($sessionId, $sessionData);
                    }
                } elseif ($action === 'update_attendance') {
                    $sessionId = $this->request->getPost('session_id');
                    $attendanceData = $this->request->getPost('attendance');
                    $this->teacherService->updateAttendance($sessionId, $attendanceData);
                } elseif ($action === 'delete_session') {
                    $sessionId = $this->request->getPost('session_id');
                    $this->teacherService->deleteSession($sessionId);
                }
                session()->setFlashdata('success', 'Action completed successfully.');
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
            }
            return redirect()->to('/teacher/classes/' . $classId);
        }

        try {
            $data = $this->teacherService->getClassDetails($userId, $classId);
            $data['navbar'] = 'teacher';
            $data['currentSegment'] = 'classes';
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
        if ($this->request->getMethod() === 'POST') {
            try {
                $leaveId = $this->request->getPost('leave_id');
                $action = $this->request->getPost('action');
                if ($action === 'approve') {
                    $this->teacherService->approveLeave($leaveId);
                } elseif ($action === 'reject') {
                    $this->teacherService->rejectLeave($leaveId);
                }
                session()->setFlashdata('success', 'Action completed successfully.');
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
            }
            return redirect()->to('/teacher/leave_requests');
        }

        try {
            $data = ['leaveRequests' => $this->teacherService->getLeaveRequests($userId, $statusFilter)];
            $data['navbar'] = 'teacher';
            $data['currentSegment'] = 'leave_requests';
            $data['statusFilter'] = $statusFilter;
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
            $data = $this->teacherService->getSchedule($userId, $viewMode);
            $data['navbar'] = 'teacher';
            $data['currentSegment'] = 'schedule';
            $data['viewMode'] = $viewMode;
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
            $data = $this->teacherService->getReports($userId, $filters);
            $data['navbar'] = 'teacher';
            $data['currentSegment'] = 'reports';
            $data['filters'] = $filters;
            return view('teacher/reports', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }

    public function profile()
    {
        $userId = session()->get('user_id');
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            try {
                if ($action === 'update_profile') {
                    $userData = $this->request->getPost(['user_key', 'first_name', 'last_name', 'middle_name', 'birthday', 'gender', 'bio']);
                    $this->teacherService->updateProfile($userId, $userData);
                    session()->set('first_name', $userData['first_name']);
                } elseif ($action === 'change_password') {
                    $oldPassword = $this->request->getPost('old_password');
                    $newPassword = $this->request->getPost('new_password');
                    $confirmPassword = $this->request->getPost('confirm_password');
                    if ($newPassword !== $confirmPassword) {
                        throw new ValidationException('Passwords do not match.');
                    }
                    $this->teacherService->changePassword($userId, $oldPassword, $newPassword);
                } elseif ($action === 'update_photo') {
                    $file = $this->request->getFile('profile_picture');
                    if ($file->isValid()) {
                        $newName = 'user_' . $userId . '_' . time() . '.' . $file->getExtension();
                        $file->move('public/uploads/profile_pictures', $newName);
                        $photoPath = 'uploads/profile_pictures/' . $newName;
                        $this->teacherService->updateProfilePicture($userId, $photoPath);
                        session()->set('profile_picture', $photoPath);
                    } else {
                        throw new ValidationException('Invalid file.');
                    }
                }
                session()->setFlashdata('success', 'Action completed successfully.');
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
            }
            return redirect()->to('/teacher/profile');
        }

        try {
            $data = ['user' => $this->teacherService->getUser($userId)];
            $data['navbar'] = 'teacher';
            $data['currentSegment'] = 'profile';
            $data['validation'] = \Config\Services::validation();
            return view('shared/profile', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/teacher');
        }
    }
}
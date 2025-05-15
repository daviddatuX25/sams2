<?php

namespace App\Controllers;

use App\Services\StudentService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;

class StudentController extends BaseController
{
    protected $studentService;

    public function __construct()
    {
        $this->studentService = new StudentService(
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
            ),
            new \App\Services\ClassSessionService(
                new \App\Models\ClassModel(),
                new \App\Models\ClassSessionModel(),
                new \App\Models\StudentAssignmentModel(),
                new \App\Models\TeacherAssignmentModel()
            )
        );
    }

    public function index()
    {
        $userId = session()->get('user_id');
        try {
            $data = $this->studentService->getDashboardData($userId);
            $data['navbar'] = 'student';
            return view('student/dashboard', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function classes()
    {
        $userId = session()->get('user_id');
        try {
            $data = ['classes' => $this->studentService->getClasses($userId)];
            $data['navbar'] = 'student';
            return view('student/classes', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function classDetail($classId)
    {
        $userId = session()->get('user_id');
        try {
            $data = $this->studentService->getClassDetails($userId, $classId);
            $data['navbar'] = 'student';
            return view('student/class_detail', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student/classes');
        }
    }

    public function attendance()
    {
        $userId = session()->get('user_id');
        $filters = $this->request->getGet(['date', 'class_id', 'status']);
        try {
            $data = $this->studentService->getAttendanceLogs($userId, $filters);
            $data['navbar'] = 'student';
            $data['filters'] = $filters;
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
            $data = $this->studentService->getSchedule($userId, $viewMode);
            $data['navbar'] = 'student';
            $data['viewMode'] = $viewMode;
            return view('student/schedule', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
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
                    $this->studentService->updateProfile($userId, $userData);
                    session()->set('first_name', $userData['first_name']);
                } elseif ($action === 'change_password') {
                    $oldPassword = $this->request->getPost('old_password');
                    $newPassword = $this->request->getPost('new_password');
                    $confirmPassword = $this->request->getPost('confirm_password');
                    if ($newPassword !== $confirmPassword) {
                        throw new ValidationException('Passwords do not match.');
                    }
                    $this->studentService->changePassword($userId, $oldPassword, $newPassword);
                } elseif ($action === 'update_photo') {
                    $file = $this->request->getFile('profile_picture');
                    if ($file->isValid()) {
                        $newName = 'user_' . $userId . '_' . time() . '.' . $file->getExtension();
                        $file->move('public/uploads/profile_pictures', $newName);
                        $photoPath = 'uploads/profile_pictures/' . $newName;
                        $this->studentService->updateProfilePicture($userId, $photoPath);
                        session()->set('profile_picture', $photoPath);
                    } else {
                        throw new ValidationException('Invalid file.');
                    }
                }
                session()->setFlashdata('success', 'Action completed successfully.');
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
            }
            return redirect()->to('/student/profile');
        }

        try {
            $data = ['user' => $this->studentService->getUser($userId)];
            $data['navbar'] = 'student';
            $data['validation'] = \Config\Services::validation();
            return view('shared/profile', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function leaveRequests()
    {
        $userId = session()->get('user_id');
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            try {
                if ($action === 'create') {
                    $leaveData = $this->request->getPost(['letter', 'datetimestamp_created', 'class_id']);
                    $this->studentService->createLeaveRequest($userId, $leaveData);
                    session()->setFlashdata('success', 'Leave request submitted successfully.');
                } elseif ($action === 'cancel') {
                    $leaveId = $this->request->getPost('leave_id');
                    $this->studentService->cancelLeaveRequest($userId, $leaveId);
                    session()->setFlashdata('success', 'Leave request canceled successfully.');
                }
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
            }
            return redirect()->to('/student/leave_requests');
        }

        try {
            $data = ['leaveRequests' => $this->studentService->getLeaveRequestsForStudent($userId)];
            $data['navbar'] = 'student';
            return view('student/leave_requests', $data);
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/student');
        }
    }

    public function getUser($userId)
    {
        return $this->studentService->getUser($userId);
    }
}
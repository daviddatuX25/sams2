<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentAssignmentModel;
use App\Models\ClassSessionModel;
use App\Models\AttendanceModel;
use App\Models\ScheduleModel;
use App\Models\NotificationsModel;
use App\Models\ClassModel;
use App\Models\AttendanceLeaveModel;
use CodeIgniter\Controller;

class StudentController extends Controller
{
    protected $userModel;
    protected $studentAssignmentModel;
    protected $classSessionModel;
    protected $attendanceModel;
    protected $notificationsModel;
    protected $classModel;
    protected $scheduleModel;
    protected $attendanceLeaveModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->studentAssignmentModel = new StudentAssignmentModel();
        $this->classSessionModel = new ClassSessionModel();
        $this->attendanceModel = new AttendanceModel();
        $this->notificationsModel = new NotificationsModel();
        $this->classModel = new ClassModel();
        $this->attendanceLeaveModel = new AttendanceLeaveModel();
        $this->scheduleModel = new ScheduleModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Please log in.');
        }

        $user = $this->userModel->find($userId);
        if ($user && $user['is_password_temporary'] == 1) {
            session()->setFlashdata('error_notification', 'Your password has not been changed since reset. Please change it.');
        }

        $classIds = $this->studentAssignmentModel->getClassIdsForStudent($userId);
        $todaySessions = !empty($classIds) ? $this->classSessionModel->whereIn('class_id', $classIds)
            ->where('DATE(open_datetime)', date('Y-m-d'))
            ->findAll() : [];

        $attendanceRate = $this->attendanceModel->getAttendanceRate($userId);
        $unreadCount = $this->notificationsModel->getUnreadCount($userId);

        $data = [
            'navbar' => 'student',
            'todaySessions' => $todaySessions,
            'attendanceRate' => $attendanceRate,
            'unreadCount' => $unreadCount,
        ];

        return view('student/dashboard', $data);
    }

    public function classes()
    {
        $userId = session()->get('user_id');

        $classes = $this->studentAssignmentModel->select('class.class_id, class.class_name, class.section, users.first_name, users.last_name')
            ->join('class', 'class.class_id = student_assignment.class_id')
            ->join('users', 'users.user_id = class.teacher_id')
            ->where('student_assignment.student_id', $userId)
            ->findAll();

        $data = [
            'navbar' => 'student',
            'classes' => $classes,
        ];

        return view('student/classes', $data);
    }

    public function classDetail($classId)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Please log in.');
        }

        if (!$this->studentAssignmentModel->isStudentEnrolled($userId, $classId)) {
            session()->setFlashdata('error_notification', 'You are not enrolled in this class.');
            return redirect()->to('/student/classes');
        }

        $class = $this->classModel->select('class.*, users.first_name, users.last_name, subject.subject_name')
            ->join('users', 'users.user_id = class.teacher_id')
            ->join('subject', 'subject.subject_id = class.subject_id')
            ->find($classId);

        if (!$class) {
            session()->setFlashdata('error_notification', 'Class not found.');
            return redirect()->to('/student/classes');
        }

        $sessions = $this->classSessionModel->where('class_id', $classId)->findAll();
        $attendance = $this->attendanceModel->where('user_id', $userId)
            ->where('class_session_id IN (SELECT class_session_id FROM class_sessions WHERE class_id = ' . $classId . ')')
            ->findAll();
        $attendanceStats = $this->attendanceModel->getAttendanceStats($userId, $classId);
        $leaveRequests = $this->attendanceLeaveModel->where('user_id', $userId)->findAll();

        $data = [
            'navbar' => 'student',
            'class' => $class,
            'sessions' => $sessions,
            'attendance' => $attendance,
            'attendanceStats' => $attendanceStats,
            'leaveRequests' => $leaveRequests,
        ];

        return view('student/class_detail', $data);
    }

    public function attendance()
    {
        $userId = session()->get('user_id');

        $filters = $this->request->getGet(['date', 'class_id', 'status']);
        $builder = $this->attendanceModel->select('attendance.*, class.class_name, class_sessions.class_session_name')
            ->join('class_sessions', 'class_sessions.class_session_id = attendance.class_session_id')
            ->join('class', 'class.class_id = class_sessions.class_id')
            ->where('attendance.user_id', $userId);

        if (!empty($filters['date'])) {
            $timestamp = strtotime($filters['date']);
            if ($timestamp) {
                $filters['date'] = date('Y-m-d', $timestamp);
                $builder->where('attendance.marked_at >=', $filters['date'] . ' 00:00:00');
                $builder->where('attendance.marked_at <=', $filters['date'] . ' 23:59:59');
            }
        }

        if (!empty($filters['class_id'])) {
            $builder->where('class_sessions.class_id', $filters['class_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('attendance.status', $filters['status']);
        }

        $attendanceLogs = $builder->findAll();

        $classes = $this->studentAssignmentModel->select('class.class_id, class.class_name')
            ->join('class', 'class.class_id = student_assignment.class_id')
            ->where('student_assignment.student_id', $userId)
            ->findAll();

        $data = [
            'navbar' => 'student',
            'attendanceLogs' => $attendanceLogs,
            'classes' => $classes,
            'filters' => $filters,
        ];

        return view('student/attendance', $data);
    }

    public function schedule()
    {
        $userId = session()->get('user_id');

        $viewMode = $this->request->getGet('view') ?? 'week';

        $classIds = $this->studentAssignmentModel->where('student_id', $userId)->findColumn('class_id') ?? [];

        $term = $this->studentAssignmentModel->select('MIN(enrollment_term.term_start) as term_start, MAX(enrollment_term.term_end) as term_end')
            ->join('enrollment_term', 'enrollment_term.enrollment_term_id = student_assignment.enrollment_term_id')
            ->where('student_assignment.student_id', $userId)
            ->where('enrollment_term.status', 'active')
            ->where('student_assignment.deleted_at IS NULL')
            ->where('enrollment_term.deleted_at IS NULL')
            ->first();
        $termStart = $term['term_start'] ?? '2025-01-01';
        $termEnd = $term['term_end'] ?? '2025-05-31';

        $schedules = [];
        if (!empty($classIds)) {
            $schedules = $this->scheduleModel->select('schedule.*, class.class_name, rooms.room_name')
                ->join('class', 'class.class_id = schedule.class_id')
                ->join('rooms', 'rooms.room_id = schedule.room_id')
                ->whereIn('schedule.class_id', $classIds)
                ->findAll();
        }

        $events = [];
        foreach ($schedules as $schedule) {
            $daysOfWeek = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 0];
            $dayIndex = $daysOfWeek[strtolower($schedule['week_day'])];

            $events[] = [
                'title' => $schedule['class_name'] . ' (' . $schedule['room_name'] . ')',
                'daysOfWeek' => [$dayIndex],
                'startTime' => $schedule['time_start'],
                'endTime' => $schedule['time_end'],
                'startRecur' => $termStart,
                'endRecur' => $termEnd,
                'url' => site_url('student/classes/' . $schedule['class_id']),
            ];
        }

        $data = [
            'navbar' => 'student',
            'events' => json_encode($events),
            'termStart' => $termStart,
            'termEnd' => $termEnd,
            'viewMode' => $viewMode,
        ];

        return view('student/schedule', $data);
    }

    public function profile()
    {
        $userId = session()->get('user_id');
        $validation = \Config\Services::validation();

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');

            try {
                if ($action === 'update_profile') {
                    $userData = $this->request->getPost(['user_key', 'first_name', 'last_name', 'middle_name', 'birthday', 'gender', 'bio']);
                    $this->userModel->updateProfile($userId, $userData);
                    session()->set('first_name', $userData['first_name']);
                    session()->setFlashdata('success', 'Profile updated successfully.');
                } elseif ($action === 'change_password') {
                    $oldPassword = $this->request->getPost('old_password');
                    $newPassword = $this->request->getPost('new_password');
                    $confirmPassword = $this->request->getPost('confirm_password');

                    if ($newPassword !== $confirmPassword) {
                        throw new \Exception('New password and confirmation do not match.');
                    }

                    if (!$this->userModel->changePassword($userId, $oldPassword, $newPassword)) {
                        throw new \Exception('Incorrect old password.');
                    }
                    session()->setFlashdata('success', 'Password changed successfully.');
                } elseif ($action === 'update_photo') {
                    $file = $this->request->getFile('profile_picture');
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = 'user_' . $userId . '_' . time() . '.' . $file->getClientExtension();
                        $file->move(ROOTPATH . 'public/uploads/profile_pictures', $newName);
                        $photoPath = 'uploads/profile_pictures/' . $newName;
                        $this->userModel->updateProfilePicture($userId, $photoPath);
                        session()->set('profile_picture', $photoPath);
                        session()->setFlashdata('success', 'Profile photo updated successfully.');
                    } else {
                        throw new \Exception('Failed to upload photo.');
                    }
                }
            } catch (\Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
            }
        }

        $user = $this->userModel->find($userId);

        $data = [
            'navbar' => 'student',
            'user' => $user,
            'validation' => $validation,
        ];

        return view('shared/profile', $data);
    }

    public function leaveRequests()
    {
        $userId = session()->get('user_id');

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            if ($action === 'create') {
                $leaveData = $this->request->getPost(['letter', 'datetimestamp_created']);
                $leaveData['user_id'] = $userId;
                $leaveData['status'] = 'pending';
                try {
                    $this->attendanceLeaveModel->createLeave($leaveData);
                    session()->setFlashdata('success', 'Leave request submitted.');
                } catch (\Exception $e) {
                    session()->setFlashdata('error', $e->getMessage());
                }
            } elseif ($action === 'cancel') {
                $leaveId = $this->request->getPost('leave_id');
                try {
                    $this->attendanceLeaveModel->softDelete($leaveId);
                    session()->setFlashdata('success', 'Leave request cancelled.');
                } catch (\Exception $e) {
                    session()->setFlashdata('error', $e->getMessage());
                }
            }
            return redirect()->back();
        }

        $leaveRequests = $this->attendanceLeaveModel->where('user_id', $userId)->findAll();

        $data = [
            'navbar' => 'student',
            'leaveRequests' => $leaveRequests,
        ];

        return view('student/leave_requests', $data);
    }
}
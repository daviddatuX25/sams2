<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TeacherAssignmentModel;
use App\Models\ClassSessionModel;
use App\Models\AttendanceModel;
use App\Models\ScheduleModel;
use App\Models\NotificationsModel;
use App\Models\ClassModel;
use App\Models\AttendanceLeaveModel;
use App\Models\StudentAssignmentModel;
use CodeIgniter\Controller;

class TeacherController extends Controller
{
    protected $userModel;
    protected $teacherAssignmentModel;
    protected $classSessionModel;
    protected $attendanceModel;
    protected $notificationsModel;
    protected $classModel;
    protected $scheduleModel;
    protected $attendanceLeaveModel;
    protected $studentAssignmentModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->teacherAssignmentModel = new TeacherAssignmentModel();
        $this->classSessionModel = new ClassSessionModel();
        $this->attendanceModel = new AttendanceModel();
        $this->notificationsModel = new NotificationsModel();
        $this->classModel = new ClassModel();
        $this->scheduleModel = new ScheduleModel();
        $this->attendanceLeaveModel = new AttendanceLeaveModel();
        $this->studentAssignmentModel = new StudentAssignmentModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId || session()->get('role') !== 'teacher') {
            return redirect()->to('/auth')->with('error', 'Please log in as a teacher.');
        }

        $todaySessions = $this->classSessionModel->getTodaySessionsForUser($userId, 'teacher');
        $pendingLeaveCount = $this->attendanceLeaveModel->getPendingLeaveRequestsCountForTeacher($userId);
        $unreadCount = $this->notificationsModel->getUnreadCount($userId);

        $data = [
            'navbar' => 'teacher',
            'todaySessions' => $todaySessions,
            'pendingLeaveCount' => $pendingLeaveCount,
            'unreadCount' => $unreadCount,
            'currentSegment' => '',
        ];

        return view('teacher/dashboard', $data);
    }

    public function classes()
    {
        $userId = session()->get('user_id');
        if (!$userId || session()->get('role') !== 'teacher') {
            return redirect()->to('/auth')->with('error', 'Please log in as a teacher.');
        }

        $classes = $this->teacherAssignmentModel->select('class.class_id, class.class_name, class.section, subject.subject_name')
            ->join('class', 'class.class_id = teacher_assignment.class_id')
            ->join('subject', 'subject.subject_id = class.subject_id')
            ->where('teacher_assignment.teacher_id', $userId)
            ->findAll();

        $data = [
            'navbar' => 'teacher',
            'classes' => $classes,
            'currentSegment' => 'dashboard',
        ];

        return view('teacher/classes', $data);
    }

    public function classDetail($classId)
    {
        $userId = session()->get('user_id');
        if (!$userId || session()->get('role') !== 'teacher') {
            return redirect()->to('/auth')->with('error', 'Please log in as a teacher.');
        }

        if (!$this->teacherAssignmentModel->where('teacher_id', $userId)->where('class_id', $classId)->first()) {
            return redirect()->to('/teacher/classes')->with('error', 'You are not assigned to this class.');
        }

        $class = $this->classModel->select('class.*, subject.subject_name')
            ->join('subject', 'subject.subject_id = class.subject_id')
            ->find($classId);

        if (!$class) {
            return redirect()->to('/teacher/classes')->with('error', 'Class not found.');
        }

        $roster = $this->studentAssignmentModel->select('users.user_id, users.first_name, users.last_name')
            ->join('users', 'users.user_id = student_assignment.student_id')
            ->where('student_assignment.class_id', $classId)
            ->findAll();

        $sessions = $this->classSessionModel->where('class_id', $classId)->findAll();

        $attendanceStats = $this->attendanceModel->select('attendance.status, COUNT(*) as count')
            ->join('class_sessions', 'class_sessions.class_session_id = attendance.class_session_id')
            ->where('class_sessions.class_id', $classId)
            ->groupBy('attendance.status')
            ->findAll();
        $attendanceStats = array_column($attendanceStats, 'count', 'status');

        $data = [
            'navbar' => 'teacher',
            'class' => $class,
            'roster' => $roster,
            'sessions' => $sessions,
            'attendanceStats' => $attendanceStats,
            'currentSegment' => 'classes'
        ];

        return view('teacher/classes', $data);
    }

    public function leaveRequests()
    {
        $userId = session()->get('user_id');
        if (!$userId || session()->get('role') !== 'teacher') {
            return redirect()->to('/auth')->with('error', 'Please log in as a teacher.');
        }

        if ($this->request->getMethod() === 'post') {
            $action = $this->request->getPost('action');
            $leaveId = $this->request->getPost('leave_id');

            try {
                if ($action === 'approve' || $action === 'reject') {
                    $this->attendanceLeaveModel->update($leaveId, [
                        'status' => $action === 'approve' ? 'approved' : 'rejected',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    session()->setFlashdata('success', 'Leave request ' . $action . 'd successfully.');
                }
            } catch (\Exception $e) {
                session()->setFlashdata('error', 'Failed to process leave request: ' . $e->getMessage());
            }

            return redirect()->to('/teacher/leave-requests');
        }

        $leaveRequests = $this->attendanceLeaveModel->select('attendance_leave.*, users.first_name, users.last_name, class.class_name')
            ->join('student_assignment', 'student_assignment.student_id = attendance_leave.user_id')
            ->join('users', 'users.user_id = attendance_leave.user_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = student_assignment.class_id')
            ->join('class', 'class.class_id = student_assignment.class_id')
            ->where('teacher_assignment.teacher_id', $userId)
            ->findAll();

        $data = [
            'navbar' => 'teacher',
            'leaveRequests' => $leaveRequests,
            'currentSegment' => 'leave-requests'
        ];

        return view('teacher/leave_requests', $data);
    }

    public function schedule()
    {
        $userId = session()->get('user_id');
        if (!$userId || session()->get('role') !== 'teacher') {
            return redirect()->to('/auth')->with('error', 'Please log in as a teacher.');
        }

        $viewMode = $this->request->getGet('view') ?? 'week';

        $classIds = $this->teacherAssignmentModel->where('teacher_id', $userId)->findColumn('class_id') ?? [];

        $term = $this->teacherAssignmentModel->select('MIN(enrollment_term.term_start) as term_start, MAX(enrollment_term.term_end) as term_end')
            ->join('enrollment_term', 'enrollment_term.enrollment_term_id = teacher_assignment.enrollment_term_id')
            ->where('teacher_assignment.teacher_id', $userId)
            ->where('enrollment_term.status', 'active')
            ->where('teacher_assignment.deleted_at IS NULL')
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
                'url' => site_url('teacher/classes/' . $schedule['class_id']),
            ];
        }

        $data = [
            'navbar' => 'teacher',
            'events' => json_encode($events),
            'termStart' => $termStart,
            'termEnd' => $termEnd,
            'viewMode' => $viewMode,
            'currentSegment' => 'schedule'

        ];

        return view('teacher/schedule', $data);
    }

    public function reports()
    {
        $userId = session()->get('user_id');
        if (!$userId || session()->get('role') !== 'teacher') {
            return redirect()->to('/auth')->with('error', 'Please log in as a teacher.');
        }

        $filters = $this->request->getGet(['class_id', 'start_date', 'end_date']);
        $classIds = $this->teacherAssignmentModel->where('teacher_id', $userId)->findColumn('class_id') ?? [];

        $classes = [];
        if (!empty($classIds)) {
            $classes = $this->classModel->select('class_id, class_name')->whereIn('class_id', $classIds)->findAll();
        }

        $attendanceData = [];
        if (!empty($classIds)) {
            $builder = $this->attendanceModel->select('attendance.status, COUNT(*) as count, class.class_name')
                ->join('class_sessions', 'class_sessions.class_session_id = attendance.class_session_id')
                ->join('class', 'class.class_id = class_sessions.class_id')
                ->whereIn('class_sessions.class_id', $classIds)
                ->groupBy('attendance.status, class.class_id');

            if (!empty($filters['class_id'])) {
                $builder->where('class_sessions.class_id', $filters['class_id']);
            }
            if (!empty($filters['start_date'])) {
                $builder->where('attendance.marked_at >=', $filters['start_date'] . ' 00:00:00');
            }
            if (!empty($filters['end_date'])) {
                $builder->where('attendance.marked_at <=', $filters['end_date'] . ' 23:59:59');
            }

            $attendanceData = $builder->findAll();
        }

        $chartData = [
            'labels' => [],
            'datasets' => [
                ['label' => 'Present', 'data' => [], 'backgroundColor' => 'rgba(16, 185, 129, 0.7)'],
                ['label' => 'Absent', 'data' => [], 'backgroundColor' => 'rgba(239, 68, 68, 0.7)'],
                ['label' => 'Late', 'data' => [], 'backgroundColor' => 'rgba(246, 173, 85, 0.7)'],
            ],
        ];

        $classNames = array_unique(array_column($attendanceData, 'class_name'));
        $chartData['labels'] = $classNames;

        foreach ($classNames as $className) {
            $present = 0;
            $absent = 0;
            $late = 0;
            foreach ($attendanceData as $row) {
                if ($row['class_name'] === $className) {
                    if ($row['status'] === 'present') {
                        $present = $row['count'];
                    } elseif ($row['status'] === 'absent') {
                        $absent = $row['count'];
                    } elseif ($row['status'] === 'late') {
                        $late = $row['count'];
                    }
                }
            }
            $chartData['datasets'][0]['data'][] = $present;
            $chartData['datasets'][1]['data'][] = $absent;
            $chartData['datasets'][2]['data'][] = $late;
        }

        $data = [
            'navbar' => 'teacher',
            'classes' => $classes,
            'filters' => $filters,
            'chartData' => json_encode($chartData),
            'attendanceData' => $attendanceData,
            'currentSegment' => 'reports',
        ];

        return view('teacher/reports', $data);
    }

    public function profile()
    {
        $userId = session()->get('user_id');
        $validation = \Config\Services::validation();
        $user = $this->userModel->find($userId);
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');

            try {
                if ($action === 'update_profile') {
                    $userData = $this->request->getPost(['user_key', 'first_name', 'last_name', 'middle_name', 'birthday', 'gender', 'bio']);
                    $changes = array_diff_assoc($userData, $user);
                    if(!empty($changes)){
                        $this->userModel->updateProfile($userId, $userData);
                        session()->set('first_name', $userData['first_name']);
                        session()->setFlashdata('success', 'Profile updated successfully.');
                    } else {
                        session()->setFlashdata('error', 'No changes were made.');
                    }
                   
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

        $data = [
            'currentSegment' => 'reports',
            'navbar' => 'teacher',
            'user' => $user,
            'validation' => $validation,
        ];

        return view('shared/profile', $data);
    }
}
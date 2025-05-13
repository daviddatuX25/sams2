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

    /**
     * Dashboard: Display today's sessions, pending leave requests, and unread notifications.
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $todaySessions = $this->classSessionModel->getTodaySessionsForUser($userId, 'teacher');
        $pendingLeaveCount = $this->attendanceLeaveModel->getPendingLeaveRequestsCountForTeacher($userId);
        $unreadCount = $this->notificationsModel->getUnreadCount($userId);

        $data = [
            'navbar' => 'teacher',
            'currentSegment' => 'dashboard',
            'todaySessions' => $todaySessions,
            'pendingLeaveCount' => $pendingLeaveCount,
            'unreadCount' => $unreadCount
        ];

        return view('teacher/dashboard', $data);
    }

    /**
     * Classes: Display assigned classes as tiles.
     */
    public function classes()
    {
        $userId = session()->get('user_id');
        $classes = $this->teacherAssignmentModel->select('class.class_id, class.class_name, class.section, subject.subject_name')
            ->join('class', 'class.class_id = teacher_assignment.class_id')
            ->join('subject', 'subject.subject_id = class.subject_id')
            ->where('teacher_assignment.teacher_id', $userId)
            ->findAll();

        $data = [
            'navbar' => 'teacher',
            'currentSegment' => 'classes',
            'classes' => $classes
        ];

        return view('teacher/classes', $data);
    }

    /**
     * Class Detail: Display roster, sessions, and attendance for a specific class.
     */
    public function classDetail($classId)
    {

        $userId = session()->get('user_id');
        if (!$this->teacherAssignmentModel->isTeacherAssigned($userId, $classId)) {
            return redirect()->to('/teacher/classes')->with('error', 'You are not assigned to this class.');
        }

        $class = $this->classModel->getClassDetails($classId);
        if (!$class) {
            return redirect()->to('/teacher/classes')->with('error', 'Class not found.');
        }

        $roster = $this->studentAssignmentModel->select('users.user_id, users.first_name, users.last_name')
            ->join('users', 'users.user_id = student_assignment.student_id')
            ->where('student_assignment.class_id', $classId)
            ->findAll();

        $sessions = $this->classSessionModel->getSessionsByClass($classId);

        $selected_session_id = $this->request->getGet('session_id');
        $attendance = [];
        $attendanceStats = ['present' => 0, 'absent' => 0, 'late' => 0];
        if ($selected_session_id) {
            $attendance = $this->attendanceModel->select('user_id, status')
                ->where('class_session_id', $selected_session_id)
                ->findAll();
            $attendance = array_column($attendance, 'status', 'user_id');

            $stats = $this->attendanceModel->select('status, COUNT(*) as count')
                ->where('class_session_id', $selected_session_id)
                ->groupBy('status')
                ->findAll();
            foreach ($stats as $stat) {
                $attendanceStats[$stat['status']] = $stat['count'];
            }
        }

        if ($this->request->getMethod() === 'POST') {
            
            $action = $this->request->getPost('action') ?? 'update_attendance';
            try {
                if ($action === 'start_session' || $action === 'update_session') {
                    $session_id = $this->request->getPost('class_session_id');
                    $data = $this->request->getPost([
                        'class_session_name', 'open_datetime', 'duration', 'attendance_method',
                        'time_in_threshold', 'time_out_threshold', 'late_threshold', 'auto_mark_attendance'
                    ]);

                    // Validate required fields
                    if (empty($data['class_session_name']) || empty($data['open_datetime']) || empty($data['duration']) || empty($data['attendance_method'])) {
                        throw new \Exception('Missing required fields: class_session_name, open_datetime, duration, or attendance_method.');
                    }

                    // Convert thresholds to time strings if attendance_method is automatic
                    if ($data['attendance_method'] === 'automatic') {
                        foreach (['time_in_threshold', 'time_out_threshold', 'late_threshold'] as $key) {
                            if (!empty($data[$key]) && is_numeric($data[$key])) {
                                $mins = (int)$data[$key] * 60;
                                $data[$key] = gmdate("H:i:s", $mins);
                            } else {
                                $data[$key] = '00:00:00'; // Default if not provided
                            }
                        }
                        $data['auto_mark_attendance'] = $data['auto_mark_attendance'] ?? 'yes';
                    } else {
                        // Set defaults for manual attendance
                        $data['time_in_threshold'] = '00:00:00';
                        $data['time_out_threshold'] = '00:00:00';
                        $data['late_threshold'] = '00:00:00';
                        $data['auto_mark_attendance'] = 'no';
                    }

                    if ($action === 'update_session') {

                        if (empty($session_id) || !$this->classSessionModel->find($session_id)) {
                            throw new \Exception('Session not found.');
                        }

                        $this->classSessionModel->updateSession($session_id, $data);
                        session()->setFlashdata('success', 'Session updated successfully.');
                    } else {
                        $this->classSessionModel->startSession($classId, $data);
                        log_message('debug','Hi' . json_encode($data));
                        session()->setFlashdata('success', 'Session started successfully.');
                    }
                } elseif ($action === 'delete_session') {
                    $session_id = $this->request->getPost('session_id');
                    if (!$this->classSessionModel->find($session_id)) {
                        throw new \Exception('Session not found.');
                    }
                    $this->classSessionModel->deleteSession($session_id);
                    session()->setFlashdata('success', 'Session deleted successfully.');
                } elseif ($action === 'update_attendance') {
                    $session_id = $this->request->getPost('session_id');
                    if (!$this->classSessionModel->find($session_id)) {
                        throw new \Exception('Invalid session.');
                    }

                    $attendanceData = $this->request->getPost('attendance');

                    foreach ($attendanceData as $student_id => $status) {
                        if (in_array($status, ['present', 'absent', 'late'])) {
                            $this->attendanceModel->markAttendance($session_id, $student_id, $status, $forceUpdate = true);
                        }
                    }
                    session()->setFlashdata('success', 'Attendance updated successfully.');
                }
            } catch (\Exception $e) {
                session()->setFlashdata('error', 'Failed to process request: ' . $e->getMessage());
            }
        }

        $data = [
            'navbar' => 'teacher',
            'currentSegment' => 'classes',
            'class' => $class,
            'roster' => $roster,
            'sessions' => $sessions,
            'selected_session_id' => $selected_session_id,
            'attendance' => $attendance,
            'attendanceStats' => $attendanceStats
        ];

        return view('teacher/class_detail', $data);
    }

    /**
     * Leave Requests: Display and process leave requests from students.
     */
    public function leaveRequests()
    {
        $userId = session()->get('user_id');
        $statusFilter = $this->request->getGet('status') ?? 'all';

        if ($this->request->getMethod() === 'POST') {
            $leaveId = $this->request->getPost('leave_id');
            $action = $this->request->getPost('action');

            if (!$this->attendanceLeaveModel->leaveExists($leaveId)) {
                session()->setFlashdata('error', 'Invalid leave request.');
                return redirect()->to('/teacher/leave_requests');
            }
            
            try {
                if ($action === 'approve') {
                    $this->attendanceLeaveModel->approveLeave($leaveId);
                    session()->setFlashdata('success', 'Leave request approved.');
                } elseif ($action === 'reject') {
                    $this->attendanceLeaveModel->rejectLeave($leaveId);
                    session()->setFlashdata('success', 'Leave request rejected.');
                } else {
                    session()->setFlashdata('error', 'Invalid action.');
                }
            } catch (\Exception $e) {
                session()->setFlashdata('error', 'Failed to process leave request: ' . $e->getMessage());
            }

            return redirect()->to('teacher/leave_requests');
        }

        $builder = $this->attendanceLeaveModel->select('attendance_leave.*, users.first_name, users.last_name, class.class_name')
            ->join('student_assignment', 'student_assignment.student_id = attendance_leave.user_id')
            ->join('users', 'users.user_id = attendance_leave.user_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = student_assignment.class_id')
            ->join('class', 'class.class_id = student_assignment.class_id')
            ->where('teacher_assignment.teacher_id', $userId);

        if ($statusFilter !== 'all') {
            $builder->where('attendance_leave.status', $statusFilter);
        }

        $leaveRequests = $builder->findAll();
        $data = [
            'navbar' => 'teacher',
            'currentSegment' => 'leave_requests',
            'leaveRequests' => $leaveRequests,
            'statusFilter' => $statusFilter
        ];

        return view('teacher/leave_requests', $data);
    }

    /**
     * Schedule: Display teaching schedule with day/week view.
     */
    public function schedule()
    {
        $userId = session()->get('user_id');
        $viewMode = $this->request->getGet('view') ?? 'week';

        $schedules = $this->scheduleModel->getScheduleByTeacher($userId);

        $term = $this->teacherAssignmentModel->select('MIN(enrollment_term.term_start) as term_start, MAX(enrollment_term.term_end) as term_end')
            ->join('enrollment_term', 'enrollment_term.enrollment_term_id = teacher_assignment.enrollment_term_id')
            ->where('teacher_assignment.teacher_id', $userId)
            ->where('enrollment_term.status', 'active')
            ->where('teacher_assignment.deleted_at IS NULL')
            ->where('enrollment_term.deleted_at IS NULL')
            ->first();

        $termStart = $term['term_start'] ?? date('Y-m-d');
        $termEnd = $term['term_end'] ?? date('Y-m-d', strtotime('+6 months'));

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
                'url' => site_url('teacher/classes/' . $schedule['class_id'])
            ];
        }


        $data = [
            'navbar' => 'teacher',
            'currentSegment' => 'schedule',
            'events' => json_encode($events),
            'termStart' => $termStart,
            'termEnd' => $termEnd,
            'viewMode' => $viewMode
        ];

        return view('teacher/schedule', $data);
    }

    /**
     * Reports: Display attendance reports with filters and charts.
     */
    public function reports()
    {
        $userId = session()->get('user_id');
        $filters = $this->request->getGet(['class_id', 'start_date', 'end_date']);
        $classIds = $this->teacherAssignmentModel->where('teacher_id', $userId)->findColumn('class_id') ?? [];

        $classes = !empty($classIds) ? $this->classModel->select('class_id, class_name')->whereIn('class_id', $classIds)->findAll() : [];

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
                ['label' => 'Late', 'data' => [], 'backgroundColor' => 'rgba(246, 173, 85, 0.7)']
            ]
        ];

        $classNames = array_unique(array_column($attendanceData, 'class_name'));
        $chartData['labels'] = $classNames;

        foreach ($classNames as $className) {
            $present = $absent = $late = 0;
            foreach ($attendanceData as $row) {
                if ($row['class_name'] === $className) {
                    if ($row['status'] === 'present') $present = $row['count'];
                    elseif ($row['status'] === 'absent') $absent = $row['count'];
                    elseif ($row['status'] === 'late') $late = $row['count'];
                }
            }
            $chartData['datasets'][0]['data'][] = $present;
            $chartData['datasets'][1]['data'][] = $absent;
            $chartData['datasets'][2]['data'][] = $late;
        }

        $data = [
            'navbar' => 'teacher',
            'currentSegment' => 'reports',
            'classes' => $classes,
            'filters' => $filters,
            'chartData' => json_encode($chartData),
            'attendanceData' => $attendanceData
        ];

        return view('teacher/reports', $data);
    }

    /**
     * Profile: Manage teacher profile, password, and photo.
     */
    public function profile()
    {
        $userId = session()->get('user_id');
        $validation = \Config\Services::validation();

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');

            try {
                if ($action === 'update_profile') {
                    $rules = [
                        'user_key' => 'required|is_unique[users.user_key,user_id,' . $userId . ']|max_length[255]',
                        'first_name' => 'required|max_length[255]',
                        'last_name' => 'required|max_length[255]',
                        'middle_name' => 'permit_empty|max_length[255]',
                        'birthday' => 'permit_empty|valid_date',
                        'gender' => 'required|in_list[male,female,other]',
                        'bio' => 'permit_empty|max_length[500]'
                    ];

                    if (!$this->validate($rules)) {
                        return view('shared/profile', [
                            'currentSegment' => 'profile',
                            'navbar' => 'teacher',
                            'user' => $this->userModel->find($userId),
                            'validation' => $this->validator
                        ]);
                    }

                    $userData = $this->request->getPost(array_keys($rules));
                    $this->userModel->updateProfile($userId, $userData);
                    session()->set('first_name', $userData['first_name']);
                    session()->setFlashdata('success', 'Profile updated successfully.');
                } elseif ($action === 'change_password') {
                    $rules = [
                        'old_password' => 'required',
                        'new_password' => 'required|min_length[8]',
                        'confirm_password' => 'required|matches[new_password]'
                    ];

                    if (!$this->validate($rules)) {
                        return view('shared/profile', [
                            'currentSegment' => 'profile',
                            'navbar' => 'teacher',
                            'user' => $this->userModel->find($userId),
                            'validation' => $this->validator
                        ]);
                    }

                    $oldPassword = $this->request->getPost('old_password');
                    $newPassword = $this->request->getPost('new_password');
                    if (!$this->userModel->changePassword($userId, $oldPassword, $newPassword)) {
                        throw new \Exception('Incorrect old password.');
                    }
                    session()->setFlashdata('success', 'Password changed successfully.');
                } elseif ($action === 'update_photo') {
                    $rules = [
                        'profile_picture' => 'uploaded[profile_picture]|max_size[profile_picture,2048]|is_image[profile_picture]'
                    ];

                    if (!$this->validate($rules)) {
                        return view('shared/profile', [
                            'currentSegment' => 'profile',
                            'navbar' => 'teacher',
                            'user' => $this->userModel->find($userId),
                            'validation' => $this->validator
                        ]);
                    }

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

            return redirect()->to('/teacher/profile');
        }

        $user = $this->userModel->find($userId);

        $data = [
            'currentSegment' => 'profile',
            'navbar' => 'teacher',
            'user' => $user,
            'validation' => $validation
        ];

        return view('shared/profile', $data);
    }
}
<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\SubjectService;
use App\Services\ClassService;
use App\Services\EnrollmentTermService;
use App\Services\StudentAssignmentService;
use App\Services\TeacherAssignmentService;
use App\Services\RoomService;
use App\Services\TrackerService;
use App\Services\NotificationService;
use App\Services\ClassSessionService;
use App\Services\ClassSessionSettingsService;
use App\Services\AttendanceService;
use App\Services\TrackerLogService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Traits\ExceptionHandlingTrait;

class AdminController extends BaseController
{
    use ExceptionHandlingTrait;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    public function dashboard()
    {
        return $this->handleAction(function () {
            $userService = new UserService();
            $classService = new ClassService();
            $leaveService = new \App\Services\AttendanceLeaveService();
            $attendanceService = new AttendanceService();
            $data = [
                'stats' => [
                    'admins' => $userService->countByRole('admin'),
                    'teachers' => $userService->countByRole('teacher'),
                    'students' => $userService->countByRole('student'),
                    'active_classes' => $classService->countActive(),
                    'pending_leaves' => $leaveService->countPending(),
                    'pending_sessions' => (new ClassSessionService())->countPending(),
                    'attendance_rate' => $attendanceService->getTodayAttendanceRate()
                ],
                'recent_leaves' => $leaveService->getRecent(),
                'navbar' => 'admin',
                'currentSegment' => 'dashboard',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/dashboard', $data);
        });
    }


    /**
     * Users view: Manage user records.
     */
    public function users($userId = null)
    {
        return $this->handleAction(function () {

            $userService = new UserService();
            $userId = session()->get('user_id');

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                // if ($this->request->isAJAX()) {
                //     $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                // }
                if ($action === 'get') {
                    $userData = $userService->getUser($postData['user_id']);
                    return $this->response->setJSON(['success' => true, 'message' => 'Successfully retrieved user data', 'data' => $userData]);
                } elseif ($action === 'create') {
                    $userService->createUser($postData);
                    $message = 'User created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/users');
                } elseif ($action === 'update' && isset($postData['user_id'])) {
                    $userService->updateUser((int)$postData['user_id'], $postData);
                    $message = 'User updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/users');
                } elseif ($action === 'delete' && isset($postData['user_id'])) {
                    $userService->deleteUser((int)$postData['user_id']);
                    $message = 'User deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/users');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }
            $data = [
                'users' => $userService->getUsers(),
                'navbar' => 'admin',
                'currentSegment' => 'users',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/users', $data);
        });
    }

    /**
     * Subjects view: Manage subject records.
     */
    public function subjects()
    {
        return $this->handleAction(function () {

            $subjectService = new SubjectService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $subjectService->createSubject($postData);
                    $message = 'Subject created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/subjects');
                } elseif ($action === 'update' && isset($postData['subject_id'])) {
                    $subjectService->updateSubject((int)$postData['subject_id'], $postData);
                    $message = 'Subject updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/subjects');
                } elseif ($action === 'delete' && isset($postData['subject_id'])) {
                    $subjectService->deleteSubject((int)$postData['subject_id']);
                    $message = 'Subject deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/subjects');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'subjects' => $subjectService->getSubjects(),
                'navbar' => 'admin',
                'currentSegment' => 'subjects',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/subjects', $data);
        });
    }

    /**
     * Classes view: Manage class records.
     */
    public function classes()
    {
        return $this->handleAction(function () {

            $classService = new ClassService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $classService->createClass($postData);
                    $message = 'Class created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/classes');
                } elseif ($action === 'update' && isset($postData['class_id'])) {
                    $classService->updateClass((int)$postData['class_id'], $postData);
                    $message = 'Class updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/classes');
                } elseif ($action === 'delete' && isset($postData['class_id'])) {
                    $classService->deleteClass((int)$postData['class_id']);
                    $message = 'Class deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/classes');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'classes' => $classService->getClasses(),
                'navbar' => 'admin',
                'currentSegment' => 'classes',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/classes', $data);
        });
    }

    /**
     * Enrollment Terms view: Manage enrollment term records.
     */
    public function enrollmentTerms($enrollmentTermId = null, $action = null)
    {
        return $this->handleAction(function () 
        use($enrollmentTermId, $action)
        {

            $enrollmentTermService = new EnrollmentTermService();
            
            if ($this->request->getMethod() === 'GET' && $action === 'get'){
                $data = $enrollmentTermService->getTerm($enrollmentTermId);
                $message = 'Enrollment term fetched successfully.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => true, 'message' => $message, 'data' => $data]);
                }
            } else

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $enrollmentTermService->createTerm($postData);
                    $message = 'Enrollment term created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/enrollment-terms');
                } elseif ($action === 'update' && isset($postData['enrollment_term_id'])) {
                    $enrollmentTermService->updateTerm((int)$postData['enrollment_term_id'], $postData);
                    $message = 'Enrollment term updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/enrollment-terms');
                } elseif ($action === 'delete' && isset($postData['enrollment_term_id'])) {
                    $enrollmentTermService->deleteTerm((int)$postData['enrollment_term_id']);
                    $message = 'Enrollment term deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/enrollment-terms');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'terms' => $enrollmentTermService->getTerms(),
                'navbar' => 'admin',
                'currentSegment' => 'enrollment-terms',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/enrollment_terms', $data);
        });
    }

    /**
     * Student Assignment view: Manage student assignment records.
     */
    public function studentAssignments()
    {
        return $this->handleAction(function () {

            $studentAssignmentService = new StudentAssignmentService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $studentAssignmentService->createAssignment($postData);
                    $message = 'Student assignment created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/student-assignments');
                } elseif ($action === 'update' && isset($postData['enrollment_id'])) {
                    $studentAssignmentService->updateAssignment((int)$postData['enrollment_id'], $postData);
                    $message = 'Student assignment updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/student-assignments');
                } elseif ($action === 'delete' && isset($postData['enrollment_id'])) {
                    $studentAssignmentService->deleteAssignment((int)$postData['enrollment_id']);
                    $message = 'Student assignment deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/student-assignments');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
            'assignments' => $studentAssignmentService->getAssignments(),
            'students' => (new UserService())->getStudents(),
            'classes' => (new ClassService())->getClasses(),
            'terms' => (new EnrollmentTermService())->getTerms(),
            'navbar' => 'admin',
            'currentSegment' => 'student-assignments',
            'validation' => \Config\Services::validation()
        ];
            return view('admin/student_assignments', $data);
        });
    }

    /**
     * Teacher Assignment view: Manage teacher assignment records.
     */
    public function teacherAssignments()
    {
        return $this->handleAction(function () {

            $teacherAssignmentService = new TeacherAssignmentService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $teacherAssignmentService->createAssignment($postData);
                    $message = 'Teacher assignment created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/teacher-assignments');
                } elseif ($action === 'update' && isset($postData['assignment_id'])) {
                    $teacherAssignmentService->updateAssignment((int)$postData['assignment_id'], $postData);
                    $message = 'Teacher assignment updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/teacher-assignments');
                } elseif ($action === 'delete' && isset($postData['assignment_id'])) {
                    $teacherAssignmentService->deleteAssignment((int)$postData['assignment_id']);
                    $message = 'Teacher assignment deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/teacher-assignments');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'assignments' => $teacherAssignmentService->getAssignments(),
                'teachers' => (new UserService())->getTeachers(),
                'classes' => (new ClassService())->getClasses(),
                'terms' => (new EnrollmentTermService())->getTerms(),
                'navbar' => 'admin',
                'currentSegment' => 'teacher-assignments',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/teacher_assignments', $data);
        });
    }

    /**
     * Rooms view: Manage room records.
     */
    public function rooms()
    {
        return $this->handleAction(function () {

            $roomService = new RoomService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $roomService->createRoom($postData);
                    $message = 'Room created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/rooms');
                } elseif ($action === 'update' && isset($postData['room_id'])) {
                    $roomService->updateRoom((int)$postData['room_id'], $postData);
                    $message = 'Room updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/rooms');
                } elseif ($action === 'delete' && isset($postData['room_id'])) {
                    $roomService->deleteRoom((int)$postData['room_id']);
                    $message = 'Room deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/rooms');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'rooms' => $roomService->getRooms(),
                'navbar' => 'admin',
                'currentSegment' => 'rooms',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/rooms', $data);
        });
    }

    /**
     * Trackers view: Manage tracker records.
     */
    public function trackers()
    {
        return $this->handleAction(function () {

            $trackerService = new TrackerService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $trackerService->createTracker($postData);
                    $message = 'Tracker created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/trackers');
                } elseif ($action === 'update' && isset($postData['tracker_id'])) {
                    $trackerService->updateTracker((int)$postData['tracker_id'], $postData);
                    $message = 'Tracker updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/trackers');
                } elseif ($action === 'delete' && isset($postData['tracker_id'])) {
                    $trackerService->deleteTracker((int)$postData['tracker_id']);
                    $message = 'Tracker deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/trackers');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'trackers' => $trackerService->getTrackers(),
                'navbar' => 'admin',
                'currentSegment' => 'trackers',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/trackers', $data);
        });
    }

    /**
     * Notifications view: Manage notification records.
     */
    public function notifications()
    {
        return $this->handleAction(function () {

            $notificationService = new NotificationService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $notificationService->createNotification($postData);
                    $message = 'Notification created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/notifications');
                } elseif ($action === 'update' && isset($postData['notification_id'])) {
                    $notificationService->updateNotification((int)$postData['notification_id'], $postData);
                    $message = 'Notification updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/notifications');
                } elseif ($action === 'delete' && isset($postData['notification_id'])) {
                    $notificationService->deleteNotification((int)$postData['notification_id']);
                    $message = 'Notification deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/notifications');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'notifications' => $notificationService->getNotifications(),
                'navbar' => 'admin',
                'currentSegment' => 'notifications',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/notifications', $data);
        });
    }

    /**
     * Class Sessions view: Manage class session records.
     */
    public function classSessions()
    {
        return $this->handleAction(function () {

            $classSessionService = new ClassSessionService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $classSessionService->createSession($postData);
                    $message = 'Class session created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/class-sessions');
                } elseif ($action === 'update' && isset($postData['class_session_id'])) {
                    $classSessionService->updateSession((int)$postData['class_session_id'], $postData);
                    $message = 'Class session updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/class-sessions');
                } elseif ($action === 'delete' && isset($postData['class_session_id'])) {
                    $classSessionService->deleteSession((int)$postData['class_session_id']);
                    $message = 'Class session deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/class-sessions');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }
            $data = [
                'sessions' => $classSessionService->getSessions(),
                'classes' => (new ClassService())->getClasses(),
                'stats' => [
                    'attendance_rate' => (new AttendanceService())->getTodayAttendanceRate(),
                    'open_sessions' => $classSessionService->countOpen()
                ],
                'navbar' => 'admin',
                'currentSegment' => 'class-sessions',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/class_sessions', $data);
        });
    }

    /**
     * Class Session Settings view: Manage class session settings records.
     */
    public function classSessionSettings()
    {
        return $this->handleAction(function () {

            $classSessionSettingsService = new ClassSessionSettingsService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $classSessionSettingsService->createSetting($postData);
                    $message = 'Class session setting created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/class-session-settings');
                } elseif ($action === 'update' && isset($postData['id'])) {
                    $classSessionSettingsService->updateSetting((int)$postData['id'], $postData);
                    $message = 'Class session setting updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/class-session-settings');
                } elseif ($action === 'delete' && isset($postData['id'])) {
                    $classSessionSettingsService->deleteSetting((int)$postData['id']);
                    $message = 'Class session setting deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/class-session-settings');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'settings' => $classSessionSettingsService->getSettings(),
                'navbar' => 'admin',
                'currentSegment' => 'class-session-settings',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/class_session_settings', $data);
        });
    }

    /**
     * Attendance view: Manage attendance records.
     */
    public function attendance()
    {
        return $this->handleAction(function () {

            $attendanceService = new AttendanceService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $attendanceService->createAttendance($postData);
                    $message = 'Attendance record created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/attendance');
                } elseif ($action === 'update' && isset($postData['attendance_id'])) {
                    $attendanceService->updateAttendance((int)$postData['attendance_id'], $postData);
                    $message = 'Attendance record updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/attendance');
                } elseif ($action === 'delete' && isset($postData['attendance_id'])) {
                    $attendanceService->deleteAttendance((int)$postData['attendance_id']);
                    $message = 'Attendance record deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/attendance');
                } elseif ($action === 'bulk_update' && isset($postData['session_id']) && isset($postData['attendance_data'])) {
                    $attendanceService->bulkUpdateAttendance((int)$postData['session_id'], $postData['attendance_data']);
                    $message = 'Attendance records updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/attendance');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'attendance' => $attendanceService->getAttendance(),
                'stats' => [
                    'attendance_rate' => $attendanceService->getTodayAttendanceRate(),
                    'open_sessions' => (new ClassSessionService())->countOpen()
                ],
                'classes' => (new ClassService())->getClasses(),
                'sessions' => (new ClassSessionService())->getSessions(),
                'students' => (new UserService())->getStudents(),
                'navbar' => 'admin',
                'currentSegment' => 'attendance',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/attendance', $data);
        });
    }

    /**
     * Tracker Logs view: Manage tracker log records.
     */
    public function trackerLogs()
    {
        return $this->handleAction(function () {

            $trackerLogService = new TrackerLogService();

            if ($this->request->getMethod() === 'POST') {
                $postData = $this->request->getPost();
                $action = $postData['action'] ?? '';

                if ($this->request->isAJAX()) {
                    $result = ['success' => false, 'message' => 'Invalid action', 'errors' => []];
                }

                if ($action === 'create') {
                    $trackerLogService->createLog($postData);
                    $message = 'Tracker log created successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/tracker-logs');
                } elseif ($action === 'update' && isset($postData['tracker_log_id'])) {
                    $trackerLogService->updateLog((int)$postData['tracker_log_id'], $postData);
                    $message = 'Tracker log updated successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/tracker-logs');
                } elseif ($action === 'delete' && isset($postData['tracker_log_id'])) {
                    $trackerLogService->deleteLog((int)$postData['tracker_log_id']);
                    $message = 'Tracker log deleted successfully.';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => true, 'message' => $message]);
                    }
                    session()->setFlashdata('success', $message);
                    return redirect()->to('/admin/tracker-logs');
                } else {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
                    }
                    throw new \CodeIgniter\Validation\Exceptions\ValidationException('Invalid action.');
                }
            }

            $data = [
                'logs' => $trackerLogService->getLogs(),
                'navbar' => 'admin',
                'currentSegment' => 'tracker-logs',
                'validation' => \Config\Services::validation()
            ];
            return view('admin/tracker_logs', $data);
        });
    }
}
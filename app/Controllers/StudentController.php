<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SubjectModel;
use App\Models\AttendanceLeaveModel;
use App\Models\ClassModel; // Assumed model
use App\Models\ClassSessionModel; // Assumed model
use App\Models\AttendanceModel; // Assumed model
use App\Models\StudentAssignmentModel; // Assumed model

class StudentController extends BaseController
{
    protected $userModel;
    protected $subjectModel;
    protected $attendanceLeaveModel;
    protected $classModel;
    protected $classSessionModel;
    protected $attendanceModel;
    protected $studentAssignmentModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->subjectModel = new SubjectModel();
        $this->attendanceLeaveModel = new AttendanceLeaveModel();
        $this->classModel = new ClassModel();
        $this->classSessionModel = new ClassSessionModel();
        $this->attendanceModel = new AttendanceModel();
        $this->studentAssignmentModel = new StudentAssignmentModel();

        if (session()->get('role') !== 'student') {
            throw new \Exception('Access denied.');
        }
    }

    public function classes()
    {
        $userId = session()->get('user_id');
        $classes = $this->studentAssignmentModel->select('class.class_id, class.class_name, class.section, subject.subject_name, users.first_name as teacher_name')
            ->join('class', 'class.class_id = student_assignment.class_id')
            ->join('subject', 'subject.subject_id = class.subject_id')
            ->join('users', 'users.user_id = class.teacher_id')
            ->where('student_assignment.student_id', $userId)
            ->findAll();

        return view('student/classes/index', ['classes' => $classes]);
    }

    public function classDetails($className)
    {
        $class = $this->classModel->where('class_name', $className)->first();
        if (!$class) {
            throw new \Exception('Class not found.');
        }
        $userId = session()->get('user_id');
        $isEnrolled = $this->studentAssignmentModel->where('student_id', $userId)
            ->where('class_id', $class['class_id'])
            ->first();
        if (!$isEnrolled) {
            throw new \Exception('You are not enrolled in this class.');
        }

        $teacher = $this->userModel->find($class['teacher_id']);
        $subject = $this->subjectModel->find($class['subject_id']);
        $classmates = $this->studentAssignmentModel->select('users.first_name, users.last_name')
            ->join('users', 'users.user_id = student_assignment.student_id')
            ->where('student_assignment.class_id', $class['class_id'])
            ->findAll();

        return view('student/layout', [
            'content' => view('student/classes/details', [
                'class' => $class,
                'teacher' => $teacher,
                'subject' => $subject,
                'classmates' => $classmates
            ])
        ]);
    }

    public function classSessions($className)
    {
        $class = $this->classModel->where('class_name', $className)->first();
        if (!$class) {
            throw new \Exception('Class not found.');
        }
        $userId = session()->get('user_id');
        $isEnrolled = $this->studentAssignmentModel->where('student_id', $userId)
            ->where('class_id', $class['class_id'])
            ->first();
        if (!$isEnrolled) {
            throw new \Exception('You are not enrolled in this class.');
        }

        $sessions = $this->classSessionModel->where('class_id', $class['class_id'])
            ->orderBy('open_datetime', 'ASC')
            ->findAll();

        return view('student/layout', [
            'content' => view('student/classes/sessions', [
                'class' => $class,
                'sessions' => $sessions
            ])
        ]);
    }

    public function classLeaveRequests($className)
    {
        $class = $this->classModel->where('class_name', $className)->first();
        if (!$class) {
            throw new \Exception('Class not found.');
        }
        $userId = session()->get('user_id');
        $isEnrolled = $this->studentAssignmentModel->where('student_id', $userId)
            ->where('class_id', $class['class_id'])
            ->first();
        if (!$isEnrolled) {
            throw new \Exception('You are not enrolled in this class.');
        }

        $leaveRequests = $this->attendanceLeaveModel->where('user_id', $userId)
            ->orderBy('datetimestamp_created', 'DESC')
            ->findAll();

        return view('student/layout', [
            'content' => view('student/classes/leave_requests', [
                'class' => $class,
                'leaveRequests' => $leaveRequests
            ])
        ]);
    }

    public function classAttendance($className)
    {
        $class = $this->classModel->where('class_name', $className)->first();
        if (!$class) {
            throw new \Exception('Class not found.');
        }
        $userId = session()->get('user_id');
        $isEnrolled = $this->studentAssignmentModel->where('student_id', $userId)
            ->where('class_id', $class['class_id'])
            ->first();
        if (!$isEnrolled) {
            throw new \Exception('You are not enrolled in this class.');
        }

        $attendance = $this->attendanceModel->select('attendance.*, class_sessions.open_datetime')
            ->join('class_sessions', 'class_sessions.class_session_id = attendance.class_session_id')
            ->where('attendance.user_id', $userId)
            ->where('class_sessions.class_id', $class['class_id'])
            ->orderBy('class_sessions.open_datetime', 'DESC')
            ->findAll();

        return view('student/layout', [
            'content' => view('student/classes/attendance', [
                'class' => $class,
                'attendance' => $attendance
            ])
        ]);
    }
}
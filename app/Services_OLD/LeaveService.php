<?php

namespace App\Services;

use App\Models\AttendanceLeaveModel;
use App\Models\StudentAssignmentModel;
use App\Models\TeacherAssignmentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Validation\Exceptions\ValidationException;

class LeaveService
{
    protected $attendanceLeaveModel;
    protected $studentAssignmentModel;
    protected $teacherAssignmentModel;

    public function __construct(
        AttendanceLeaveModel $attendanceLeaveModel,
        StudentAssignmentModel $studentAssignmentModel,
        TeacherAssignmentModel $teacherAssignmentModel
    ) {
        $this->attendanceLeaveModel = $attendanceLeaveModel;
        $this->studentAssignmentModel = $studentAssignmentModel;
        $this->teacherAssignmentModel = $teacherAssignmentModel;
    }

    public function createLeave(int $userId, array $leaveData): int
    {
        if (!is_numeric($userId)) {
            throw new ValidationException('User ID must be an integer.');
        }

        $rules = [
            'letter' => 'required',
            'datetimestamp_created' => 'required|valid_date',
            'class_id' => 'required|integer'
        ];

        $validation = \Config\Services::validation();
        if (!$validation->setRules($rules)->run($leaveData)) {
            throw new ValidationException(implode(', ', $validation->getErrors()));
        }

        $leaveData['user_id'] = $userId;
        $leaveData['status'] = 'pending';

        return $this->attendanceLeaveModel->insert($leaveData);
    }

    public function getLeaveRequestsForTeacher(int $teacherId, string $statusFilter = 'all'): array
    {
        if (!is_numeric($teacherId)) {
            throw new ValidationException('Teacher ID must be an integer.');
        }

        $query = $this->attendanceLeaveModel->join('student_assignment', 'student_assignment.student_id = attendance_leave.user_id')
                                            ->join('teacher_assignment', 'teacher_assignment.class_id = attendance_leave.class_id')
                                            ->join('user', 'user.user_id = attendance_leave.user_id')
                                            ->join('class', 'class.class_id = attendance_leave.class_id')
                                            ->where('teacher_assignment.teacher_id', $teacherId);

        if ($statusFilter !== 'all') {
            $query->where('attendance_leave.status', $statusFilter);
        }

        return $query->findAll();
    }

    public function approveLeave(int $leaveId): bool
    {
        if (!is_numeric($leaveId)) {
            throw new ValidationException('Leave ID must be an integer.');
        }

        if (!$this->attendanceLeaveModel->find($leaveId)) {
            throw new PageNotFoundException('Leave request not found.');
        }

        return $this->attendanceLeaveModel->update($leaveId, [
            'status' => 'approved',
            'datetimestamp_resolved' => date('Y-m-d H:i:s')
        ]);
    }

    public function rejectLeave(int $leaveId): bool
    {
        if (!is_numeric($leaveId)) {
            throw new ValidationException('Leave ID must be an integer.');
        }

        if (!$this->attendanceLeaveModel->find($leaveId)) {
            throw new PageNotFoundException('Leave request not found.');
        }

        return $this->attendanceLeaveModel->update($leaveId, [
            'status' => 'rejected',
            'datetimestamp_resolved' => date('Y-m-d H:i:s')
        ]);
    }

    public function cancelLeave(int $leaveId, int $userId): bool
    {
        if (!is_numeric($leaveId) || !is_numeric($userId)) {
            throw new ValidationException('Leave ID and User ID must be integers.');
        }

        $leave = $this->attendanceLeaveModel->where('attendance_leave_id', $leaveId)
                                            ->where('user_id', $userId)
                                            ->first();

        if (!$leave) {
            throw new PageNotFoundException('Leave request not found.');
        }

        if ($leave['status'] !== 'pending') {
            throw new ValidationException('Only pending leave requests can be canceled.');
        }

        return $this->attendanceLeaveModel->delete($leaveId);
    }

    public function getLeaveRequestsForStudent(int $userId): array
    {
        if (!is_numeric($userId)) {
            throw new ValidationException('User ID must be an integer.');
        }

        return $this->attendanceLeaveModel->findByUser($userId);
    }
}
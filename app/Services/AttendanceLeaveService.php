<?php

namespace App\Services;

use App\Models\AttendanceLeaveModel;
use CodeIgniter\Validation\Exceptions\ValidationException;

class AttendanceLeaveService
{
    protected $userRole;
    protected ?AttendanceLeaveModel $attendanceLeaveModel;

    public function __construct(
        ?string $userRole = null,
        ?AttendanceLeaveModel $attendanceLeaveModel = null
    ) {
        $this->userRole = $userRole ?? session()->get('role');
        $this->attendanceLeaveModel = $attendanceLeaveModel ?? new AttendanceLeaveModel();
    }

    public function getLeaveRequestsByUser(int $userId): array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }

        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only view your own leave requests.');
        }
        return $this->attendanceLeaveModel
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    public function submitLeaveRequest(int $userId, array $postData, bool $isAjax = false): array
    {
        if (!in_array($this->userRole, ['student', 'teacher'])) {
            throw new ValidationException('Only students and teachers can submit leave requests.');
        }

        if ($userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only submit leave requests for yourself.');
        }

        $validation = \Config\Services::validation();
        $response = ['success' => false, 'message' => '', 'errors' => []];

        $rules = [
            'class_id' => 'required|is_natural_no_zero',
            'leave_date' => 'required|valid_date',
            'reason' => 'required|min_length[5]|max_length[500]'
        ];

        if (!$validation->setRules($rules)->run($postData)) {
            $response['errors'] = $validation->getErrors();
            $response['message'] = 'Leave request submission failed.';
            if (!$isAjax) {
                throw new ValidationException(implode(', ', $response['errors']));
            }
            return $response;
        }

        $data = [
            'user_id' => $userId,
            'class_id' => $postData['class_id'],
            'leave_date' => $postData['leave_date'],
            'reason' => $postData['reason'],
            'status' => 'pending'
        ];
        if ($this->attendanceLeaveModel->insert($data)) {
            $response['success'] = true;
            $response['message'] = 'Leave request submitted successfully.';
            $response['data'] = ['attendance_leave_id' => $this->attendanceLeaveModel->insertID()];
        } else {
            $response['message'] = 'Failed to submit leave request.';
            if (!$isAjax) {
                throw new ValidationException($response['message']);
            }
        }

        return $response;
    }

    public function cancelLeaveRequest(int $userId, int $leaveRequestId, bool $isAjax = false): array
    {
        if (!in_array($this->userRole, ['student', 'teacher'])) {
            throw new ValidationException('Only students and teachers can cancel leave requests.');
        }

        if ($userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only cancel your own leave requests.');
        }

        $response = ['success' => false, 'message' => '', 'errors' => []];

        $request = $this->attendanceLeaveModel
            ->where('attendance_leave_id', $leaveRequestId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->where('deleted_at IS NULL')
            ->first();

        if (!$request) {
            $response['message'] = 'Leave request not found or cannot be canceled.';
            if (!$isAjax) {
                throw new ValidationException($response['message']);
            }
            return $response;
        }

        if ($this->attendanceLeaveModel->delete($leaveRequestId)) {
            $response['success'] = true;
            $response['message'] = 'Leave request canceled successfully.';
        } else {
            $response['message'] = 'Failed to cancel leave request.';
            if (!$isAjax) {
                throw new ValidationException($response['message']);
            }
        }

        return $response;
    }

    public function getLeaveRequestsForTeacher(int $teacherId, ?string $status = null): array
    {
        $builder = $this->attendanceLeaveModel
            ->select('attendance_leave.*, user.first_name, user.last_name, class.class_name')
            ->join('user', 'user.user_id = attendance_leave.user_id')
            ->join('class', 'class.class_id = attendance_leave.class_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
            ->where('teacher_assignment.teacher_id', $teacherId)
            ->where('attendance_leave.deleted_at IS NULL');

        if ($status) {
            $builder->where('attendance_leave.status', $status);
        }

        return $builder->findAll();
    }

    public function approveLeaveRequest(int $leaveId, int $teacherId): bool
    {
        $request = $this->attendanceLeaveModel
            ->where('attendance_leave_id', $leaveId)
            ->where('deleted_at IS NULL')
            ->first();
        if (!$request || !$this->isTeacherAuthorized($teacherId, $request['class_id'])) {
            throw new ValidationException('Unauthorized or invalid leave request.');
        }
        return $this->attendanceLeaveModel->update($leaveId, [
            'status' => 'approved',
            'datetimestamp_resolved' => date('Y-m-d H:i:s')
        ]);
    }

    public function rejectLeaveRequest(int $leaveId, int $teacherId): bool
    {
        $request = $this->attendanceLeaveModel
            ->where('attendance_leave_id', $leaveId)
            ->where('deleted_at IS NULL')
            ->first();
        if (!$request || !$this->isTeacherAuthorized($teacherId, $request['class_id'])) {
            throw new ValidationException('Unauthorized or invalid leave request.');
        }
        return $this->attendanceLeaveModel->update($leaveId, [
            'status' => 'rejected',
            'datetimestamp_resolved' => date('Y-m-d H:i:s')
        ]);
    }

    private function isTeacherAuthorized(int $teacherId, int $classId): bool
    {
        return (new \App\Models\TeacherAssignmentModel())
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->countAllResults() > 0;
    }
}
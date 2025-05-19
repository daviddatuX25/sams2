<?php

namespace App\Services;

use App\Models\AttendanceLeaveModel;
use App\Traits\ServiceExceptionTrait;

class AttendanceLeaveService
{
    use ServiceExceptionTrait;

    protected ?AttendanceLeaveModel $attendanceLeaveModel;

    public function __construct(
        ?AttendanceLeaveModel $attendanceLeaveModel = null
    ) {
        $this->attendanceLeaveModel = $attendanceLeaveModel ?? new AttendanceLeaveModel();
    }

    /**
     * Retrieve leave requests for a student.
     */
    public function student_getLeaveRequests(int $studentId): array
    {
        if ($studentId !== (int)session()->get('user_id')) {
            $this->throwUnauthorized('You can only view your own leave requests.');
        }

        return $this->attendanceLeaveModel
            ->where('user_id', $studentId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Retrieve leave requests for a teacher.
     */
    public function teacher_getLeaveRequests(int $teacherId, ?string $status = null): array
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

    /**
     * Retrieve leave requests for an admin.
     */
    public function admin_getLeaveRequests(?int $userId = null): array
    {
        $builder = $this->attendanceLeaveModel
            ->where('deleted_at IS NULL');

        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->findAll();
    }

    /**
     * Submit a leave request for a student.
     */
    public function student_submitLeaveRequest(int $studentId, array $postData, bool $isAjax = false): array
    {
        if ($studentId !== (int)session()->get('user_id')) {
            $this->throwUnauthorized('You can only submit leave requests for yourself.');
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
                $this->throwValidationError(implode(', ', $response['errors']));
            }
            return $response;
        }

        $data = [
            'user_id' => $studentId,
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
                $this->throwBusinessRule($response['message']);
            }
        }

        return $response;
    }

    /**
     * Cancel a leave request for a student.
     */
    public function student_cancelLeaveRequest(int $studentId, int $leaveRequestId, bool $isAjax = false): array
    {
        if ($studentId !== (int)session()->get('user_id')) {
            $this->throwUnauthorized('You can only cancel your own leave requests.');
        }

        $response = ['success' => false, 'message' => '', 'errors' => []];

        $request = $this->attendanceLeaveModel
            ->where('attendance_leave_id', $leaveRequestId)
            ->where('user_id', $studentId)
            ->where('status', 'pending')
            ->where('deleted_at IS NULL')
            ->first();

        if (!$request) {
            $response['message'] = 'Leave request not found or cannot be canceled.';
            if (!$isAjax) {
                $this->throwNotFound($response['message']);
            }
            return $response;
        }

        if ($this->attendanceLeaveModel->delete($leaveRequestId)) {
            $response['success'] = true;
            $response['message'] = 'Leave request canceled successfully.';
        } else {
            $response['message'] = 'Failed to cancel leave request.';
            if (!$isAjax) {
                $this->throwBusinessRule($response['message']);
            }
        }

        return $response;
    }

    /**
     * Approve a leave request by a teacher.
     */
    public function teacher_approveLeaveRequest(int $leaveId, int $teacherId): bool
    {
        $request = $this->attendanceLeaveModel
            ->where('attendance_leave_id', $leaveId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$request) {
            $this->throwNotFound('Invalid leave request.');
        }
        if (!$this->isTeacherAuthorized($teacherId, $request['class_id'])) {
            $this->throwUnauthorized('Unauthorized to approve this leave request.');
        }

        return $this->attendanceLeaveModel->update($leaveId, [
            'status' => 'approved',
            'datetimestamp_resolved' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reject a leave request by a teacher.
     */
    public function teacher_rejectLeaveRequest(int $leaveId, int $teacherId): bool
    {
        $request = $this->attendanceLeaveModel
            ->where('attendance_leave_id', $leaveId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$request) {
            $this->throwNotFound('Invalid leave request.');
        }
        if (!$this->isTeacherAuthorized($teacherId, $request['class_id'])) {
            $this->throwUnauthorized('Unauthorized to reject this leave request.');
        }

        return $this->attendanceLeaveModel->update($leaveId, [
            'status' => 'rejected',
            'datetimestamp_resolved' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Approve a leave request by an admin.
     */
    public function admin_approveLeaveRequest(int $leaveId): bool
    {
        $request = $this->attendanceLeaveModel
            ->where('attendance_leave_id', $leaveId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$request) {
            $this->throwNotFound('Invalid leave request.');
        }

        return $this->attendanceLeaveModel->update($leaveId, [
            'status' => 'approved',
            'datetimestamp_resolved' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reject a leave request by an admin.
     */
    public function admin_rejectLeaveRequest(int $leaveId): bool
    {
        $request = $this->attendanceLeaveModel
            ->where('attendance_leave_id', $leaveId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$request) {
            $this->throwNotFound('Invalid leave request.');
        }

        return $this->attendanceLeaveModel->update($leaveId, [
            'status' => 'rejected',
            'datetimestamp_resolved' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if a teacher is authorized for a class.
     */
    private function isTeacherAuthorized(int $teacherId, int $classId): bool
    {
        return (new \App\Models\TeacherAssignmentModel())
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->countAllResults() > 0;
    }

    public function countPending(): int
    {
        return $this->attendanceLeaveModel->where('status', 'pending')->where('deleted_at IS NULL')->countAllResults();
    }

    public function getRecent(int $limit = 5): array
    {
        return $this->attendanceLeaveModel->where('deleted_at IS NULL')->orderBy('created_at', 'DESC')->findAll($limit);
}
}
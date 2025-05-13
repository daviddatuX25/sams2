<?php
namespace App\Models;

use CodeIgniter\Model;

class AttendanceLeaveModel extends Model
{
    protected $table = 'attendance_leave';
    protected $primaryKey = 'attendance_leave_id';
    protected $allowedFields = ['user_id', 'status', 'letter', 'datetimestamp_created', 'datetimestamp_reviewed', 'datetimestamp_resolved', 'deleted_at'];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'user_id' => 'required|is_not_unique[users.user_id]',
        'status' => 'required|in_list[pending,approved,rejected]',
        'letter' => 'required',
        'datetimestamp_created' => 'permit_empty|valid_date',
        'datetimestamp_reviewed' => 'permit_empty|valid_date',
        'datetimestamp_resolved' => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'The user ID is required.',
            'is_not_unique' => 'The user ID must reference an existing user.'
        ],
        'status' => [
            'required' => 'The status is required.',
            'in_list' => 'The status must be one of: pending, approved, rejected.'
        ],
        'letter' => [
            'required' => 'The leave letter is required.'
        ],
        'datetimestamp_created' => [
            'valid_date' => 'The creation date must be a valid date.'
        ],
        'datetimestamp_reviewed' => [
            'valid_date' => 'The review date must be a valid date.'
        ],
        'datetimestamp_resolved' => [
            'valid_date' => 'The resolution date must be a valid date.'
        ]
    ];

    public function getLeave($leaveId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($leaveId);
        }
        return $this->find($leaveId);
    }

    public function leaveExists($leaveId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('attendance_leave_id', $leaveId)->get()->getRow() !== null;
    }

    public function createLeave($leaveData)
    {
        if (!$this->validate($leaveData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($leaveData);
        $leaveId = $this->getInsertID();
        return $this->select('attendance_leave_id, user_id, status, letter')->find($leaveId);
    }

    public function updateLeave($leaveId, $leaveData)
    {
        $leaveData['attendance_leave_id'] = $leaveId;
        if (!$this->validate($leaveData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($leaveData['attendance_leave_id']);
        return $this->update($leaveId, $leaveData);
    }

    public function getLeavesByUser($userId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('user_id', $userId)->findAll();
        }
        return $this->where('user_id', $userId)->findAll();
    }

    public function getLeavesByStatus($status, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('status', $status)->findAll();
        }
        return $this->where('status', $status)->findAll();
    }

    public function approveLeave($leaveId)
    {
        return $this->update($leaveId, ['status' => 'approved', 'datetimestamp_resolved' => date('Y-m-d H:i:s')]);
    }

    public function rejectLeave($leaveId)
    {
        return $this->update($leaveId, ['status' => 'rejected', 'datetimestamp_resolved' => date('Y-m-d H:i:s')]);
    }

    public function getPendingLeaveRequestsCountForTeacher($teacherId)
    {
        return $this->select('attendance_leave.*')
            ->join('student_assignment', 'student_assignment.student_id = attendance_leave.user_id')
            ->join('teacher_assignment', 'teacher_assignment.class_id = student_assignment.class_id')
            ->where('teacher_assignment.teacher_id', $teacherId)
            ->where('attendance_leave.status', 'pending')
            ->countAllResults();
    }

    public function searchLeaves($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('letter', $searchTerm)
                       ->orLike('status', $searchTerm)
                       ->get()->getResultArray();
    }

    public function getPendingLeaves($withDeleted = false) 
    {
        $builder = $this->where('status', 'pending');
        if ($withDeleted) $builder->withDeleted();
        return $builder->findAll();
    }

    public function softDelete($leaveId)
    {
        try {
            $this->delete($leaveId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function hardDelete($leaveId)
    {
        try {
            $this->onlyDeleted()->delete($leaveId, true);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($leaveId)
    {
        try {
            $this->onlyDeleted()->update($leaveId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
?>
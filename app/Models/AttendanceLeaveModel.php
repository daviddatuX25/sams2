<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceLeaveModel extends Model
{
    protected $table = 'attendance_leave';
    protected $primaryKey = 'attendance_leave_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'user_id',
        'class_id',
        'status',
        'reason',
        'leave_date',
        'datetimestamp_reviewed',
        'datetimestamp_resolved',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'class_id' => 'required|is_natural_no_zero',
        'status' => 'required|in_list[pending,approved,rejected]',
        'reason' => 'required',
        'leave_date' => 'required|valid_date',
        'datetimestamp_reviewed' => 'permit_empty|valid_date',
        'datetimestamp_resolved' => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required.',
            'integer' => 'User ID must be an integer.'
        ],
        'class_id' => [
            'required' => 'Class ID is required.',
            'integer' => 'Class ID must be an integer.'
        ],
        'reason' => [
            'required' => 'Leave letter is required.'
        ],
        'leave_date' => [
            'required' => 'Creation timestamp is required.',
            'valid_date' => 'Creation timestamp must be a valid date.'
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be one of: pending, approved, rejected.'
        ]
    ];

    // Find leaves by user_id
    public function findByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    // Find leaves by class_id
    public function findByClass($classId)
    {
        return $this->where('class_id', $classId)->findAll();
    }
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassSessionModel extends Model
{
    protected $table = 'class_session';
    protected $primaryKey = 'class_session_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'class_session_name',
        'class_session_description',
        'class_id',
        'open_datetime',
        'close_datetime',
        'status',
        'attendance_method',
        'auto_mark_attendance',
        'time_in_threshold',
        'time_out_threshold',
        'late_threshold',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'class_session_name' => 'required|max_length[255]',
        'class_session_description' => 'permit_empty',
        'class_id' => 'required|is_natural_no_zero',
        'open_datetime' => 'required|valid_date',
        'close_datetime' => 'required|valid_date',
        'status' => 'required|in_list[marked,cancelled,pending]',
        'attendance_method' => 'required|in_list[manual,automatic]',
        'auto_mark_attendance' => 'required|in_list[yes,no]',
        'time_in_threshold' => 'required|valid_time',
        'time_out_threshold' => 'required|valid_time',
        'late_threshold' => 'required|valid_time'
    ];

    protected $validationMessages = [
        'class_id' => [
            'required' => 'Class ID is required.',
            'integer' => 'Class ID must be an integer.'
        ],
        'class_session_name' => [
            'required' => 'Session name is required.',
            'max_length' => 'Session name cannot exceed 100 characters.'
        ],
        'open_datetime' => [
            'required' => 'Open datetime is required.',
            'valid_date' => 'Open datetime must be a valid date.'
        ],
        'close_datetime' => [
            'required' => 'Close datetime is required.',
            'valid_date' => 'Close datetime must be a valid date.'
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be one of: pending, cancelled, marked.'
        ],
        'attendance_method' => [
            'required' => 'Attendance method is required.',
            'in_list' => 'Attendance method must be one of: manual, automatic.'
        ]
    ];

    // Find sessions by class_id
    public function findByClass($classId)
    {
        return $this->where('class_id', $classId)->findAll();
    }
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassSessionModel extends BaseModel
{
    protected $table = 'class_session';
    protected $primaryKey = 'class_session_id';
    protected $useAutoIncrement = true;
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
    protected $validationRules = [
        'class_session_name' => 'required|max_length[255]',
        'class_session_description' => 'permit_empty',
        'class_id' => 'required|is_natural_no_zero',
        'open_datetime' => 'required|valid_date',
        'close_datetime' => 'required|valid_date',
        'status' => 'required|in_list[pending,marked,cancelled,active,finished]',
        'attendance_method' => 'required|in_list[manual,automatic]',
        'auto_mark_attendance' => 'required|in_list[yes,no]',
        'time_in_threshold' => ['label' => 'Time in Threshold', 'rules' => 'permit_empty|regex_match[/^(?:[01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/]'],
        'time_out_threshold' => ['label' => 'Time Out Threshold', 'rules' => 'permit_empty|regex_match[/^(?:[01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/]'],
        'late_threshold' => ['label' => 'Late Threshold', 'rules' => 'permit_empty|regex_match[/^(?:[01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/]']
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
            'in_list' => 'Status must be one of: pending, active, cancelled, finished, marked.'
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
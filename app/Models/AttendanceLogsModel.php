<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceLogsModel extends Model
{
    protected $table = 'attendance_logs';
    protected $primaryKey = 'log_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'user_id',
        'class_session_id',
        'tracker_id',
        'action',
        'timestamp',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'class_session_id' => 'required|is_natural_no_zero',
        'tracker_id' => 'required|is_natural_no_zero',
        'action' => 'required|in_list[time_in,time_out,auto]',
        'timestamp' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required.',
            'integer' => 'User ID must be an integer.'
        ],
        'class_session_id' => [
            'required' => 'Class session ID is required.',
            'integer' => 'Class session ID must be an integer.'
        ],
        'tracker_id' => [
            'required' => 'Tracker ID is required.',
            'integer' => 'Tracker ID must be an integer.'
        ],
        'action' => [
            'required' => 'Action is required.',
            'in_list' => 'Action must be one of: time_in, time_out, late, auto.'
        ],
        'timestamp' => [
            'required' => 'Timestamp is required.',
            'valid_date' => 'Timestamp must be a valid date.'
        ]
    ];

    // Find logs by user_id
    public function findByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    // Find logs by class_session_id
    public function findBySession($sessionId)
    {
        return $this->where('class_session_id', $sessionId)->findAll();
    }

    // Find logs by tracker_id
    public function findByTracker($trackerId)
    {
        return $this->where('tracker_id', $trackerId)->findAll();
    }
}
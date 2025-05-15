<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'user_id',
        'class_session_id',
        'status',
        'is_manual',
        'marked_at',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'class_session_id' => 'required|is_natural_no_zero',
        'status' => 'required|in_list[present,absent,late,unmarked]',
        'is_manual' => 'required|in_list[0,1]',
        'marked_at' => 'required|valid_date'
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
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be one of: present, absent, late, unmarked.'
        ],
        'marked_at' => [
            'required' => 'Marked at timestamp is required.',
            'valid_date' => 'Marked at must be a valid date.'
        ]
    ];

    // Basic find by user_id and class_session_id
    public function findByUserAndSession($userId, $sessionId)
    {
        return $this->where('user_id', $userId)
                    ->where('class_session_id', $sessionId)
                    ->first();
    }
}
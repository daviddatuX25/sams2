<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends BaseModel
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'user_id',
        'message',
        'type',
        'is_read',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'message' => 'required',
        'type' => 'required|in_list[info,success,warning,error]',
        'is_read' => 'required|in_list[0,1]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required.',
            'integer' => 'User ID must be an integer.'
        ],
        'message' => [
            'required' => 'Message is required.',
            'max_length' => 'Message cannot exceed 500 characters.'
        ],
        'type' => [
            'required' => 'Type is required.',
            'in_list' => 'Type must be one of the following: info, success, warning, error'
        ],
        'created_at' => [
            'required' => 'Creation datetime is required.',
            'valid_date' => 'Creation datetime must be a valid date.'
        ]
    ];

    // Find notifications by user_id
    public function findByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    // Find unread notifications by user_id
    public function findUnreadByUser($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('read_datetime IS NULL')
                    ->findAll();
    }
}
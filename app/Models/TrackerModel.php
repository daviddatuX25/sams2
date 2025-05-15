<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackerModel extends Model
{
    protected $table = 'tracker';
    protected $primaryKey = 'tracker_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'tracker_name',
        'tracker_description',
        'tracker_type',
        'status',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'tracker_name' => 'required|is_unique[tracker.tracker_name,tracker_id,{tracker_id}]|max_length[255]',
        'tracker_description' => 'permit_empty',
        'tracker_type' => 'required|in_list[face,rfid,manual]',
        'status' => 'required|in_list[active,inactive]'
    ];
    protected $validationMessages = [
        'tracker_name' => [
            'required' => 'Tracker name is required.',
            'max_length' => 'Tracker name cannot exceed 100 characters.'
        ],
        'tracker_type' => [
            'required' => 'Tracker type is required.',
            'in_list' => 'Tracker type must be one of: face, rfid, manual.'
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be one of: active, inactive.'
        ]
    ];

    // Find active trackers
    public function findActive()
    {
        return $this->where('status', 'active')->findAll();
    }
}
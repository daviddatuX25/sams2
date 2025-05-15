<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomModel extends Model
{
    protected $table = 'room';
    protected $primaryKey = 'room_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'room_name',
        'room_description',
        'room_capacity',
        'room_type',
        'room_status',
        'tracker_id',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'room_name' => 'required|is_unique[room.room_name,room_id,{room_id}]|max_length[255]',
        'room_description' => 'permit_empty',
        'room_capacity' => 'required|is_natural',
        'room_type' => 'required|in_list[classroom,laboratory,office]',
        'room_status' => 'required|in_list[active,inactive]',
        'tracker_id' => 'permit_empty|is_natural_no_zero'
    ];
    protected $validationMessages = [
        'room_name' => [
            'required' => 'Room name is required.',
            'max_length' => 'Room name cannot exceed 100 characters.',
            'is_unique' => 'Room name already exists.'
        ],
        'room_status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be one of: active, inactive.'
        ],
        'room_type' => [
            'required' => 'Room type is required.',
            'in_list' => 'Room type must be one of: classroom, laboratory, office.'
        ],
        'room_capacity' => [
            'required' => 'Room capacity is required.',
            'is_natural' => 'Room capacity must be a positive integer.'
        ],
        'tracker_id' => [
            'is_natural_no_zero' => 'Tracker ID must be a positive integer.'
        ]
    ];

    // Find active rooms
    public function findActive()
    {
        return $this->where('status', 'active')->findAll();
    }
}
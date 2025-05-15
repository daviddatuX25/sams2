<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table = 'schedule';
    protected $primaryKey = 'schedule_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'room_id',
        'class_id',
        'time_start',
        'time_end',
        'week_day',
        'status',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'room_id' => 'required|is_natural_no_zero',
        'class_id' => 'required|is_natural_no_zero',
        'time_start' => 'required|valid_time',
        'time_end' => 'required|valid_time',
        'week_day' => 'required|in_list[mon,tue,wed,thu,fri,sat]',
        'status' => 'required|in_list[active,archived]'
    ];

    protected $validationMessages = [
        'class_id' => [
            'required' => 'Class ID is required.',
            'integer' => 'Class ID must be an integer.'
        ],
        'room_id' => [
            'required' => 'Room ID is required.',
            'integer' => 'Room ID must be an integer.'
        ],
        'week_day' => [
            'required' => 'Week day is required.',
            'in_list' => 'Week day must be one of: mon, tue, wed, thu, fri, sat.'
        ],
        'time_start' => [
            'required' => 'Start time is required.',
            'valid_time' => 'Start time must be a valid time (HH:MM:SS).'
        ],
        'time_end' => [
            'required' => 'End time is required.',
            'valid_time' => 'End time must be a valid time (HH:MM:SS).'
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be one of: active, archived.'
        ]
    ];

    // Find schedules by class_id
    public function findByClass($classId)
    {
        return $this->where('class_id', $classId)->findAll();
    }

    // Find schedules by room_id
    public function findByRoom($roomId)
    {
        return $this->where('room_id', $roomId)->findAll();
    }
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomModel extends Model
{
    protected $table = 'rooms';
    protected $primaryKey = 'room_id';
    protected $allowedFields = [
        'room_name', 'room_description', 'room_capacity', 'room_type', 'room_status', 'tracker_id', 'deleted_at'
    ];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'room_name' => 'required|is_unique[rooms.room_name,room_id,{room_id}]|max_length[255]',
        'room_description' => 'permit_empty',
        'room_capacity' => 'required|integer|greater_than_equal_to[0]',
        'room_type' => 'required|in_list[classroom,laboratory,office]',
        'room_status' => 'required|in_list[active,inactive]',
        'tracker_id' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'room_name' => [
            'required' => 'The room name is required.',
            'is_unique' => 'The room name must be unique.',
            'max_length' => 'The room name cannot exceed 255 characters.'
        ],
        'room_capacity' => [
            'required' => 'The room capacity is required.',
            'integer' => 'The room capacity must be an integer.',
            'greater_than_equal_to' => 'The room capacity must be at least 0.'
        ],
        'room_type' => [
            'required' => 'The room type is required.',
            'in_list' => 'The room type must be one of: classroom, laboratory, office.'
        ],
        'room_status' => [
            'required' => 'The room status is required.',
            'in_list' => 'The room status must be one of: active, inactive.'
        ],
        'tracker_id' => [
            'integer' => 'The tracker ID must be an integer.'
        ]
    ];

    public function getRoom($roomId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($roomId);
        }
        return $this->find($roomId);
    }

    public function getRoomsByTracker($trackerId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('tracker_id', $trackerId)->findAll();
        }
        return $this->where('tracker_id', $trackerId)->findAll();
    }

    public function updateRoom($roomId, $roomData)
    {
        $roomData['room_id'] = $roomId;
        if (!$this->validate($roomData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($roomData['room_id']);
        return $this->update($roomId, $roomData);
    }

    public function getRoomsByType($roomType, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('room_type', $roomType)->findAll();
        }
        return $this->where('room_type', $roomType)->findAll();
    }

    public function getAvailableRooms($weekDay, $timeStart, $timeEnd, $withDeleted = false) 
    {
        $scheduleModel = new ScheduleModel();
        $occupiedRooms = $scheduleModel->where('week_day', $weekDay)
                                    ->where('time_start <', $timeEnd)
                                    ->where('time_end >', $timeStart)
                                    ->findColumn('room_id');
        $builder = $this->where('room_status', 'active');
        if ($occupiedRooms) $builder->whereNotIn('room_id', $occupiedRooms);
        if ($withDeleted) $builder->withDeleted();
        return $builder->findAll();
    }

    public function softDelete($roomId)
    {
        try {
            $this->delete($roomId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($roomId)
    {
        try {
            $this->onlyDeleted()->update($roomId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
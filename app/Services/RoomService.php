<?php

namespace App\Services;

use App\Models\RoomModel;
use App\Traits\ServiceExceptionTrait;

class RoomService
{
    use ServiceExceptionTrait;

    protected ?RoomModel $roomModel;

    public function __construct(?RoomModel $roomModel = null)
    {
        $this->roomModel = $roomModel ?? new RoomModel();
    }

    /**
     * Create a new room.
     */
    public function createRoom(array $data): int
    {

        // Ensure tracker exists if provided
        if (!empty($data['tracker_id'])) {
            $trackerModel = new \App\Models\TrackerModel();
            if (!$trackerModel->where('tracker_id', $data['tracker_id'])->where('deleted_at IS NULL')->first()) {
                $this->throwNotFound('Tracker', $data['tracker_id']);
            }
        }

        $this->roomModel->insert($data);
        return $this->roomModel->insertID();
    }

    /**
     * Retrieve rooms.
     */
    public function getRooms(): array
    {
        return $this->roomModel
            ->select('room_id, room_name, room_description, room_capacity, room_type, room_status, tracker_id')
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Update a room.
     */
    public function updateRoom(int $roomId, array $data): bool
    {
        $room = $this->roomModel->where('room_id', $roomId)->where('deleted_at IS NULL')->first();
        if (!$room) {
            $this->throwNotFound('Room', $roomId);
        }

        if (!\Config\Services::validation()->setRules($rules)->run($data)) {
            $this->throwValidationError(implode(', ', \Config\Services::validation()->getErrors()));
        }

        // Ensure tracker exists if provided
        if (!empty($data['tracker_id'])) {
            $trackerModel = new \App\Models\TrackerModel();
            if (!$trackerModel->where('tracker_id', $data['tracker_id'])->where('deleted_at IS NULL')->first()) {
                $this->throwNotFound('Tracker', $data['tracker_id']);
            }
        }

        return $this->roomModel->update($roomId, $data);
    }

    /**
     * Soft delete a room.
     */
    public function deleteRoom(int $roomId): bool
    {
        $room = $this->roomModel->where('room_id', $roomId)->where('deleted_at IS NULL')->first();
        if (!$room) {
            $this->throwNotFound('Room', $roomId);
        }

        return $this->roomModel->delete($roomId);
    }
}
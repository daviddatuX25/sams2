<?php

namespace App\Services;

use App\Models\RoomModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class RoomService
{
    protected $roomModel;

    public function __construct(RoomModel $roomModel)
    {
        $this->roomModel = $roomModel;
    }

    public function getRoom(int $roomId): array
    {
        $room = $this->roomModel->find($roomId);
        if (!$room) {
            throw new PageNotFoundException('Room not found');
        }
        return $room;
    }

    public function getActiveRooms(): array
    {
        return $this->roomModel
            ->where('room_status', 'active')
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    public function createRoom(array $roomData): void
    {
        if (!$this->roomModel->insert($roomData)) {
            throw new \Exception('Failed to create room: ' . implode(', ', $this->roomModel->errors()));
        }
    }

    public function updateRoom(int $roomId, array $roomData): void
    {
        if (!$this->roomModel->update($roomId, $roomData)) {
            throw new \Exception('Failed to update room: ' . implode(', ', $this->roomModel->errors()));
        }
    }

    public function deleteRoom(int $roomId): void
    {
        if (!$this->roomModel->delete($roomId)) {
            throw new \Exception('Failed to delete room');
        }
    }
    
    public function getAvailableRooms(string $weekDay, string $timeStart, string $timeEnd): array
    {
        if (!in_array($weekDay, ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'])) {
            throw new ValidationException('Week day must be one of: mon, tue, wed, thu, fri, sat.');
        }

        $occupiedRooms = $this->scheduleModel->where('week_day', $weekDay)
                                             ->where('time_start <', $timeEnd)
                                             ->where('time_end >', $timeStart)
                                             ->findColumn('room_id') ?? [];

        return $this->roomModel->where('status', 'active')
                               ->whereNotIn('room_id', $occupiedRooms)
                               ->findAll();
    }
}
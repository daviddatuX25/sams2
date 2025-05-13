<?php
namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table = 'schedule';
    protected $primaryKey = 'rts_id';
    protected $allowedFields = ['room_id', 'time_start', 'time_end', 'week_day', 'class_id', 'status', 'deleted_at'];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'room_id' => 'required|is_not_unique[rooms.room_id]',
        'time_start' => 'required',
        'time_end' => 'required',
        'week_day' => 'required|in_list[mon,tue,wed,thu,fri,sat]',
        'class_id' => 'required|is_not_unique[class.class_id]',
        'status' => 'permit_empty|in_list[active,archived]'
    ];

    protected $validationMessages = [
        'room_id' => [
            'required' => 'The room ID is required.',
            'is_not_unique' => 'The room ID must reference an existing room.'
        ],
        'time_start' => [
            'required' => 'The start time is required.'
        ],
        'time_end' => [
            'required' => 'The end time is required.'
        ],
        'week_day' => [
            'required' => 'The weekday is required.',
            'in_list' => 'The weekday must be one of: mon, tue, wed, thu, fri, sat.'
        ],
        'class_id' => [
            'required' => 'The class ID is required.',
            'is_not_unique' => 'The class ID must reference an existing class.'
        ],
        'status' => [
            'in_list' => 'The status must be one of: active, archived.'
        ]
    ];

    public function getSchedule($scheduleId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($scheduleId);
        }
        return $this->find($scheduleId);
    }

    public function getScheduleByTeacher($teacherId, $withDeleted = false) 
    {
        $builder = $this->select('schedule.* , class.class_name, rooms.room_name')
                        ->join('teacher_assignment', 'teacher_assignment.class_id = schedule.class_id')
                        ->join('class', 'class.class_id = teacher_assignment.class_id')
                        ->join('rooms', 'schedule.room_id = rooms.room_id')
                        ->where('teacher_assignment.teacher_id', $teacherId);
        if ($withDeleted) $builder->withDeleted();
        return $builder->findAll();
    }

    public function scheduleExists($scheduleId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('rts_id', $scheduleId)->get()->getRow() !== null;
    }

    public function createSchedule($scheduleData)
    {
        if (!$this->validate($scheduleData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        if ($this->checkScheduleConflict($scheduleData['room_id'], $scheduleData['week_day'], $scheduleData['time_start'], $scheduleData['time_end'])) {
            throw new \Exception('Schedule conflict detected.');
        }
        $this->insert($scheduleData);
        $scheduleId = $this->getInsertID();
        return $this->select('rts_id, room_id, time_start, time_end, week_day, class_id')->find($scheduleId);
    }

    public function updateSchedule($scheduleId, $scheduleData)
    {
        $scheduleData['rts_id'] = $scheduleId;
        if (!$this->validate($scheduleData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($scheduleData['rts_id']);
        if ($this->checkScheduleConflict($scheduleData['room_id'], $scheduleData['week_day'], $scheduleData['time_start'], $scheduleData['time_end']) && !$this->scheduleExists($scheduleId)) {
            throw new \Exception('Schedule conflict detected.');
        }
        return $this->update($scheduleId, $scheduleData);
    }

    public function getSchedulesByRoom($roomId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('room_id', $roomId)->findAll();
        }
        return $this->where('room_id', $roomId)->findAll();
    }

    public function getSchedulesByClass($classId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('class_id', $classId)->findAll();
        }
        return $this->where('class_id', $classId)->findAll();
    }

    public function checkScheduleConflict($roomId, $weekDay, $timeStart, $timeEnd)
    {
        return $this->where('room_id', $roomId)
                    ->where('week_day', $weekDay)
                    ->where('time_start <', $timeEnd)
                    ->where('time_end >', $timeStart)
                    ->withDeleted(false)
                    ->countAllResults() > 0;
    }

    public function searchSchedules($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('week_day', $searchTerm)
                       ->orLike('status', $searchTerm)
                       ->get()->getResultArray();
    }

    public function softDelete($scheduleId)
    {
        try {
            $this->delete($scheduleId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function hardDelete($scheduleId)
    {
        try {
            $this->onlyDeleted()->delete($scheduleId, true);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($scheduleId)
    {
        try {
            $this->onlyDeleted()->update($scheduleId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
?>
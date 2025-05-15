<?php

namespace App\Services;

use App\Models\ScheduleModel;
use App\Models\ClassModel;
use App\Models\RoomModel;
use CodeIgniter\Validation\Exceptions\ValidationException;

class ScheduleService
{
    protected $scheduleModel;
    protected $classModel;
    protected $roomModel;

    public function __construct(ScheduleModel $scheduleModel, ClassModel $classModel, RoomModel $roomModel)
    {
        $this->scheduleModel = $scheduleModel;
        $this->classModel = $classModel;
        $this->roomModel = $roomModel;
    }

    public function getScheduleByUser(int $userId, string $role): array
    {
        if (!is_numeric($userId)) {
            throw new ValidationException('User ID must be an integer.');
        }
        if (!in_array($role, ['student', 'teacher'])) {
            throw new ValidationException('Role must be one of: student, teacher.');
        }

        $assignmentModel = $role === 'student' ? model('StudentAssignmentModel') : model('TeacherAssignmentModel');
        $classIds = $assignmentModel->where($role . '_id', $userId)->findColumn('class_id') ?? [];

        $schedules = $this->scheduleModel->join('class', 'class.class_id = schedule.class_id')
                                         ->join('room', 'room.room_id = schedule.room_id')
                                         ->whereIn('schedule.class_id', $classIds)
                                         ->findAll();

        $events = [];
        $term = model('EnrollmentTermModel')->where('status', 'active')->first();
        $termStart = $term['start_date'] ?? date('Y-m-d');
        $termEnd = $term['end_date'] ?? date('Y-m-d', strtotime('+1 year'));

        foreach ($schedules as $schedule) {
            $dayIndex = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6][$schedule['week_day']] ?? 0;
            $events[] = [
                'title' => $schedule['class_name'] . ' (' . $schedule['room_name'] . ')',
                'daysOfWeek' => [$dayIndex],
                'startTime' => $schedule['time_start'],
                'endTime' => $schedule['time_end'],
                'startRecur' => $termStart,
                'endRecur' => $termEnd,
                'url' => site_url($role . '/classes/' . $schedule['class_id'])
            ];
        }

        return ['events' => $events, 'termStart' => $termStart, 'termEnd' => $termEnd];
    }

    public function checkScheduleConflict(int $roomId, string $weekDay, string $timeStart, string $timeEnd): bool
    {
        if (!is_numeric($roomId)) {
            throw new ValidationException('Room ID must be an integer.');
        }
        if (!in_array($weekDay, ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'])) {
            throw new ValidationException('Week day must be one of: mon, tue, wed, thu, fri, sat.');
        }

        return $this->scheduleModel->where('room_id', $roomId)
                                  ->where('week_day', $weekDay)
                                  ->where('time_start <', $timeEnd)
                                  ->where('time_end >', $timeStart)
                                  ->countAllResults() > 0;
    }

    
}
<?php

namespace App\Services;

use App\Models\ScheduleModel;
use App\Models\ClassModel;
use App\Models\RoomModel;
use App\Models\StudentAssignmentModel;
use App\Models\TeacherAssignmentModel;
use App\Models\EnrollmentTermModel;
use CodeIgniter\Validation\Exceptions\ValidationException;

class ScheduleService
{
    protected $userRole;
    protected ?ScheduleModel $scheduleModel;
    protected ?ClassModel $classModel;
    protected ?RoomModel $roomModel;
    protected ?StudentAssignmentModel $studentAssignmentModel;
    protected ?TeacherAssignmentModel $teacherAssignmentModel;
    protected ?EnrollmentTermModel $enrollmentTermModel;

    public function __construct(
        ?string $userRole = null,
        ?ScheduleModel $scheduleModel = null,
        ?ClassModel $classModel = null,
        ?RoomModel $roomModel = null,
        ?StudentAssignmentModel $studentAssignmentModel = null,
        ?TeacherAssignmentModel $teacherAssignmentModel = null,
        ?EnrollmentTermModel $enrollmentTermModel = null
    ) {
        $this->userRole = $userRole ?? session()->get('role');
        $this->scheduleModel = $scheduleModel;
        $this->classModel = $classModel;
        $this->roomModel = $roomModel;
        $this->studentAssignmentModel = $studentAssignmentModel;
        $this->teacherAssignmentModel = $teacherAssignmentModel;
        $this->enrollmentTermModel = $enrollmentTermModel;
    }

    /**
     * Get user schedule in FullCalendar format, constrained by role.
     *
     * @param int $userId
     * @return array
     * @throws ValidationException
     */
    public function getUserSchedule(int $userId): array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }

        if ($userId <= 0) {
            throw new ValidationException('User ID must be a positive integer.');
        }

        // Initialize models
        if (!$this->scheduleModel) {
            $this->scheduleModel = new ScheduleModel();
        }
        if (!$this->studentAssignmentModel) {
            $this->studentAssignmentModel = new StudentAssignmentModel();
        }
        if (!$this->teacherAssignmentModel) {
            $this->teacherAssignmentModel = new TeacherAssignmentModel();
        }
        if (!$this->enrollmentTermModel) {
            $this->enrollmentTermModel = new EnrollmentTermModel();
        }
        if (!$this->roomModel) {
            $this->roomModel = new RoomModel();
        }

        // Get active enrollment term
        $term = $this->enrollmentTermModel
            ->where('status', 'active')
            ->where('deleted_at IS NULL')
            ->first();

        $termStart = $term ? $term['term_start'] : null;
        $termEnd = $term ? $term['term_end'] : null;

        // Build base query
        $builder = $this->scheduleModel
            ->select('schedule.*, class.class_name, room.room_name, schedule.time_start, schedule.time_end, schedule.week_day')
            ->join('class', 'class.class_id = schedule.class_id')
            ->join('room', 'room.room_id = schedule.room_id')
            ->where('schedule.status', 'active')
            ->where('schedule.deleted_at IS NULL')
            ->where('class.deleted_at IS NULL')
            ->where('room.deleted_at IS NULL');

        // Apply role-based constraints
        if ($this->userRole === 'student') {
            $classIds = $this->studentAssignmentModel
                ->select('class_id')
                ->where('student_id', $userId)
                ->where('deleted_at IS NULL')
                ->where('enrollment_term_id', $term['enrollment_term_id'] ?? null)
                ->findColumn('class_id') ?? [];
            if (empty($classIds)) {
                return ['events' => [], 'termStart' => $termStart, 'termEnd' => $termEnd];
            }
            $builder->whereIn('schedule.class_id', $classIds);
        } elseif ($this->userRole === 'teacher') {
            $classIds = $this->teacherAssignmentModel
                ->select('class_id')
                ->where('teacher_id', $userId)
                ->where('deleted_at IS NULL')
                ->where('enrollment_term_id', $term['enrollment_term_id'] ?? null)
                ->findColumn('class_id') ?? [];
            if (empty($classIds)) {
                return ['events' => [], 'termStart' => $termStart, 'termEnd' => $termEnd];
            }
            $builder->whereIn('schedule.class_id', $classIds);
        } elseif ($this->userRole === 'admin') {
            // Admins can view a specific user's schedule
            if ($this->studentAssignmentModel->where('student_id', $userId)->where('deleted_at IS NULL')->first()) {
                $classIds = $this->studentAssignmentModel
                    ->select('class_id')
                    ->where('student_id', $userId)
                    ->where('deleted_at IS NULL')
                    ->where('enrollment_term_id', $term['enrollment_term_id'] ?? null)
                    ->findColumn('class_id') ?? [];
                if (!empty($classIds)) {
                    $builder->whereIn('schedule.class_id', $classIds);
                }
            } elseif ($this->teacherAssignmentModel->where('teacher_id', $userId)->where('deleted_at IS NULL')->first()) {
                $classIds = $this->teacherAssignmentModel
                    ->select('class_id')
                    ->where('teacher_id', $userId)
                    ->where('deleted_at IS NULL')
                    ->where('enrollment_term_id', $term['enrollment_term_id'] ?? null)
                    ->findColumn('class_id') ?? [];
                if (!empty($classIds)) {
                    $builder->whereIn('schedule.class_id', $classIds);
                }
            }
            if (empty($classIds)) {
                return ['events' => [], 'termStart' => $termStart, 'termEnd' => $termEnd];
            }
        }

        $schedules = $builder->findAll();

        // Format events for FullCalendar
        $dayMap = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 0];
        $events = array_map(function ($schedule) use ($dayMap, $termStart, $termEnd) {
            $dayIndex = $dayMap[strtolower($schedule['week_day'])] ?? null;
            if ($dayIndex === null) {
                return null;
            }
            return [
                'title' => $schedule['class_name'] . ' (' . $schedule['room_name'] . ')',
                'daysOfWeek' => [$dayIndex],
                'startTime' => $schedule['time_start'],
                'endTime' => $schedule['time_end'],
                'startRecur' => $termStart,
                'endRecur' => $termEnd,
                'url' => site_url(($this->userRole === 'student' ? 'student/classes/' : 'teacher/classes/') . $schedule['class_id'])
            ];
        }, $schedules);

        // Filter out null events
        $events = array_filter($events);

        return [
            'events' => $events,
            'termStart' => $termStart,
            'termEnd' => $termEnd
        ];
    }
}
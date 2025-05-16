<?php
namespace App\Commands;

use App\Services\ClassSessionService;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\BaseCommand;

class AutoOpenClassSession extends BaseCommand
{
    protected $group = 'Cron';
    protected $name = 'session:open';
    protected $description = 'Creates class sessions from schedules';

    protected $classSessionService;

    public function __construct()
    {
        $this->classSessionService = new ClassSessionService();
    }

    public function run(array $params)
    {
        $currentTime = date('Y-m-d H:i:s');
        $weekday = strtolower(date('l'));
        $schedules = $this->getSchedules($weekday);

        foreach ($schedules as $schedule) {
            if (!$this->sessionExists($schedule, $currentTime)) {
                $sessionData = [
                    'class_id' => $schedule['class_id'],
                    'session_date' => date('Y-m-d'),
                    'open_datetime' => date('Y-m-d') . ' ' . $schedule['start_time'],
                    'close_datetime' => date('Y-m-d') . ' ' . $schedule['end_time'],
                    'session_name' => $schedule['class_name'] . ' - ' . date('Y-m-d'),
                    'room_id' => $schedule['room_id']
                ];
                $this->classSessionService->createAutoClassSession($schedule['schedule_id'], $sessionData);
                CLI::write("Created session for schedule {$schedule['schedule_id']}");
            }
        }
    }

    private function getSchedules(string $weekday): array
    {
        return (new \App\Models\ScheduleModel())
            ->select('schedule.*, class.class_name')
            ->join('class', 'class.class_id = schedule.class_id')
            ->where('schedule.day_of_week', $weekday)
            ->where('schedule.deleted_at IS NULL')
            ->where('class.deleted_at IS NULL')
            ->findAll();
    }

    private function sessionExists(array $schedule, string $currentTime): bool
    {
        $sessionDate = date('Y-m-d');
        return $this->classSessionService->classSessionModel
            ->where('class_id', $schedule['class_id'])
            ->where('session_date', $sessionDate)
            ->where('open_datetime >=', date('Y-m-d H:i:s', strtotime($schedule['start_time'] . ' -5 minutes')))
            ->where('open_datetime <=', date('Y-m-d H:i:s', strtotime($schedule['start_time'] . ' +5 minutes')))
            ->countAllResults() > 0;
    }
}
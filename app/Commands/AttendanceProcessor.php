<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\AttendanceService;

class AttendanceProcessor extends BaseCommand
{
    protected $group = 'Attendance';
    protected $name = 'attendance:process';
    protected $description = 'Processes attendance logs for automatic marking';

    public function run(array $params)
    {
        $attendanceService = new AttendanceService(
            new \App\Models\AttendanceModel(),
            new \App\Models\ClassSessionModel(),
            new \App\Models\AttendanceLogsModel()
        );

        $sessions = model('ClassSessionModel')->where('auto_mark_attendance', 'yes')
            ->where('status', 'pending')
            ->where('open_datetime <=', date('Y-m-d H:i:s'))
            ->findAll();

        foreach ($sessions as $session) {
            CLI::write("Processing session ID: {$session['class_session_id']}");
            $attendanceService->processAttendance($session['class_session_id']);
        }

        CLI::write('Attendance processing completed.');
    }
}
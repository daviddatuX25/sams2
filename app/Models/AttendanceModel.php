<?php
namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    protected $allowedFields = ['user_id', 'class_session_id', 'status', 'marked_at'];
    protected $useSoftDeletes = false;

    protected $validationRules = [
        'user_id' => 'required|is_not_unique[users.user_id]',
        'class_session_id' => 'required|is_not_unique[class_sessions.class_session_id]',
        'status' => 'required|in_list[present,absent,late,unmarked]',
        'marked_at' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'The user ID is required.',
            'is_not_unique' => 'The user ID must reference an existing user.'
        ],
        'class_session_id' => [
            'required' => 'The session ID is required.',
            'is_not_unique' => 'The session ID must reference an existing session.'
        ],
        'status' => [
            'required' => 'The status is required.',
            'in_list' => 'The status must be one of: present, absent, late, unmarked.'
        ],
        'marked_at' => [
            'required' => 'The marked at date is required.',
            'valid_date' => 'The marked at date must be a valid date.'
        ]
    ];

    public function getAttendance($attendanceId)
    {
        return $this->find($attendanceId);
    }

    public function attendanceExists($attendanceId)
    {
        return $this->builder()->where('attendance_id', $attendanceId)->get()->getRow() !== null;
    }

    public function getAttendanceRate($userId, $classId = null)
    {
        $builder = $this->join('class_sessions', 'class_sessions.class_session_id = attendance.class_session_id')
            ->where('attendance.user_id', $userId)
            ->where('class_sessions.close_datetime <', date('Y-m-d H:i:s'));

        if ($classId) {
            $builder->where('class_sessions.class_id', $classId);
        }

        $totalSessions = $builder->countAllResults(false);
        $presentCount = $builder->where('attendance.status', 'present')->countAllResults();

        return $totalSessions > 0 ? ($presentCount / $totalSessions) * 100 : 0;
    }

    public function getAttendanceStats($userId, $classId)
    {
        $attendance = $this->where('user_id', $userId)
            ->where('class_session_id IN (SELECT class_session_id FROM class_sessions WHERE class_id = ' . $this->db->escape($classId) . ')')
            ->findAll();

        $stats = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'unmarked' => 0,
        ];

        foreach ($attendance as $record) {
            if (isset($stats[$record['status']])) {
                $stats[$record['status']]++;
            }
        }

        return $stats;
    }

    public function createAttendance($attendanceData)
    {
        if (!$this->validate($attendanceData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($attendanceData);
        $attendanceId = $this->getInsertID();
        return $this->select('attendance_id, user_id, class_session_id, status')->find($attendanceId);
    }

    public function updateAttendance($attendanceId, $attendanceData)
    {
        $attendanceData['attendance_id'] = $attendanceId;
        if (!$this->validate($attendanceData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($attendanceData['attendance_id']);
        return $this->update($attendanceId, $attendanceData);
    }

    public function getAttendanceBySession($sessionId)
    {
        return $this->where('class_session_id', $sessionId)->findAll();
    }

    public function getAttendanceByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function calculateAttendancePercentage($userId, $classId)
    {
        $totalSessions = $this->join('class_sessions', 'class_sessions.class_session_id = attendance.class_session_id')
                             ->where('class_sessions.class_id', $classId)
                             ->countAllResults();
        $presentCount = $this->where('user_id', $userId)
                            ->where('class_session_id IN (SELECT class_session_id FROM class_sessions WHERE class_id = ' . $classId . ')')
                            ->where('status', 'present')
                            ->countAllResults();
        return $totalSessions > 0 ? ($presentCount / $totalSessions) * 100 : 0;
    }

    public function searchAttendance($searchTerm)
    {
        return $this->builder()->like('status', $searchTerm)
                              ->get()->getResultArray();
    }
    
    public function getAttendanceByDatetimeRange($userId, $startDate, $endDate, $classId = null)
    {
        $builder = $this->select('attendance.*, class.class_name, class_sessions.class_session_name, class_sessions.open_datetime')
                        ->join('class_sessions', 'class_sessions.class_session_id = attendance.class_session_id')
                        ->join('class', 'class.class_id = class_sessions.class_id')
                        ->where('attendance.user_id', $userId)
                        ->where('class_sessions.open_datetime >=', $startDate . ' 00:00:00')
                        ->where('class_sessions.open_datetime <=', $endDate . ' 23:59:59');

        if ($classId !== null) {
            $builder->where('class_sessions.class_id', $classId);
        }

        return $builder->findAll();
    }
}
?>
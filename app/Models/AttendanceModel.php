<?php
namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    protected $allowedFields = ['user_id', 'class_session_id', 'status'];
    protected $useSoftDeletes = false;

    protected $validationRules = [
        'user_id' => 'required|is_not_unique[users.user_id]',
        'class_session_id' => 'required|is_not_unique[class_sessions.class_session_id]',
        'status' => 'required|in_list[present,absent,late,unmarked]'
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
}
?>
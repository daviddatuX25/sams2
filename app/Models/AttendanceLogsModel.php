<?php
namespace App\Models;

use CodeIgniter\Model;

class AttendanceLogsModel extends Model
{
    protected $table = 'attendance_logs';
    protected $primaryKey = 'log_id';
    protected $allowedFields = ['user_id', 'class_session_id', 'tracker_id', 'action', 'timestamp'];
    protected $useSoftDeletes = false;

    protected $validationRules = [
        'user_id' => 'required|is_not_unique[users.user_id]',
        'class_session_id' => 'required|is_not_unique[class_sessions.class_session_id]',
        'tracker_id' => 'required|is_not_unique[trackers.tracker_id]',
        'action' => 'required|in_list[time_in,time_out]',
        'timestamp' => 'required|valid_date'
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
        'tracker_id' => [
            'required' => 'The tracker ID is required.',
            'is_not_unique' => 'The tracker ID must reference an existing tracker.'
        ],
        'action' => [
            'required' => 'The action is required.',
            'in_list' => 'The action must be one of: time_in, time_out.'
        ],
        'timestamp' => [
            'required' => 'The timestamp is required.',
            'valid_date' => 'The timestamp must be a valid date.'
        ]
    ];

    public function getLog($logId)
    {
        return $this->find($logId);
    }

    public function logExists($logId)
    {
        return $this->builder()->where('log_id', $logId)->get()->getRow() !== null;
    }

    public function createLog($logData)
    {
        if (!$this->validate($logData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($logData);
        $logId = $this->getInsertID();
        return $this->select('log_id, user_id, class_session_id, tracker_id, action, timestamp')->find($logId);
    }

    public function updateLog($logId, $logData)
    {
        $logData['log_id'] = $logId;
        if (!$this->validate($logData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($logData['log_id']);
        return $this->update($logId, $logData);
    }

    public function getLogsByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function getLogsBySession($sessionId)
    {
        return $this->where('class_session_id', $sessionId)->findAll();
    }

    public function getLogsByTracker($trackerId)
    {
        return $this->where('tracker_id', $trackerId)->findAll();
    }

    public function getLogsByDateRange($startDate, $endDate)
    {
        return $this->where('timestamp >=', $startDate)->where('timestamp <=', $endDate)->findAll();
    }

    public function searchLogs($searchTerm)
    {
        return $this->builder()->like('action', $searchTerm)
                              ->orLike('timestamp', $searchTerm)
                              ->get()->getResultArray();
    }
}
?>
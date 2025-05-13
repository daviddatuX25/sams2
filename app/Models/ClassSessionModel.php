<?php
namespace App\Models;

use CodeIgniter\Model;

class ClassSessionModel extends Model
{
    protected $table = 'class_sessions';
    protected $primaryKey = 'class_session_id';
    protected $allowedFields = [
        'class_session_name', 'class_session_description', 'class_id', 'open_datetime', 'close_datetime',
        'status', 'attendance_method', 'auto_mark_attendance', 'time_in_threshold', 'time_out_threshold',
        'late_threshold', 'deleted_at'
    ];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'class_session_name' => 'required|max_length[255]',
        'class_session_description' => 'permit_empty',
        'class_id' => 'required|is_not_unique[class.class_id]',
        'open_datetime' => 'required|valid_date',
        'close_datetime' => 'required|valid_date',
        'status' => 'required|in_list[marked,cancelled,pending]',
        'attendance_method' => 'required|in_list[manual,automatic]',
        'auto_mark_attendance' => 'required|in_list[yes,no]',
        'time_in_threshold' => 'required',
        'time_out_threshold' => 'required',
        'late_threshold' => 'required'
    ];

    protected $validationMessages = [
        'class_session_name' => [
            'required' => 'The session name is required.',
            'max_length' => 'The session name cannot exceed 255 characters.'
        ],
        'class_session_description' => [
            'permit_empty' => 'The session description is optional.'
        ],
        'class_id' => [
            'required' => 'The class ID is required.',
            'is_not_unique' => 'The class ID must reference an existing class.'
        ],
        'open_datetime' => [
            'required' => 'The open datetime is required.',
            'valid_date' => 'The open datetime must be a valid date.'
        ],
        'close_datetime' => [
            'required' => 'The close datetime is required.',
            'valid_date' => 'The close datetime must be a valid date.'
        ],
        'status' => [
            'required' => 'The status is required.',
            'in_list' => 'The status must be one of: marked, cancelled, pending.'
        ],
        'attendance_method' => [
            'required' => 'The attendance method is required.',
            'in_list' => 'The attendance method must be one of: manual, automatic.'
        ],
        'auto_mark_attendance' => [
            'required' => 'The auto mark attendance setting is required.',
            'in_list' => 'The auto mark attendance must be one of: yes, no.'
        ],
        'time_in_threshold' => [
            'permit_empty' => 'The time-in threshold is dependent to automatic settings.'
        ],
        'time_out_threshold' => [
            'permit_empty' => 'The time-out threshold is dependent to automatic settings.'
        ],
        'late_threshold' => [
            'permit_empty' => 'The late threshold is dependent to automatic settings.'
        ]
    ];

    public function getSession($sessionId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($sessionId);
        }
        return $this->find($sessionId);
    }

    public function sessionExists($sessionId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('class_session_id', $sessionId)->get()->getRow() !== null;
    }

    public function createSession($sessionData)
    {
        if (!$this->validate($sessionData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($sessionData);
        $sessionId = $this->getInsertID();
        return $this->select('class_session_id, class_session_name, class_id, open_datetime, close_datetime')->find($sessionId);
    }

    public function getSessionsByClass($classId, $withDeleted = false)
    {
        log_message('debug', ' class: ' . $classId);
        
        if ($withDeleted) {
            return $this->withDeleted()->where('class_id', $classId)->findAll();
        }
        return $this->where('class_id', $classId)->findAll();
    }

    public function getSessionsByDateRange($startDate, $endDate, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('open_datetime >=', $startDate)->where('close_datetime <=', $endDate)->findAll();
        }
        return $this->where('open_datetime >=', $startDate)->where('close_datetime <=', $endDate)->findAll();
    }

    public function getSessionsByStatus($status, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('status', $status)->findAll();
        }
        return $this->where('status', $status)->findAll();
    }

    public function searchSessions($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('class_session_name', $searchTerm)
                       ->orLike('status', $searchTerm)
                       ->get()->getResultArray();
    }

    public function getTodaySessionsForUser($userId, $role = 'student')
    {
        $assignmentTable = $role === 'student' ? 'student_assignment' : 'teacher_assignment';
        $userField = $role === 'student' ? 'student_id' : 'teacher_id';

        $classIds = $this->db->table($assignmentTable)->where($userField, $userId)->get()->getResultArray();
        $classIds = array_column($classIds, 'class_id');

        if (empty($classIds)) {
            return [];
        }

        return $this->whereIn('class_id', $classIds)
            ->where('DATE(open_datetime)', date('Y-m-d'))
            ->findAll();
    }

    public function getSessionsByDate($date, $withDeleted = false) 
    {
        $builder = $this->where('DATE(open_datetime)', $date);
        if ($withDeleted) $builder->withDeleted();
        return $builder->findAll();
        log_message('debug', "data: " . print_r($date, true));

    }

    public function startSession($classId, $data)
    {
        if (!isset($data['class_session_name']) || !isset($data['open_datetime']) || !isset($data['duration'])) {
            throw new \Exception('Missing required fields: class_session_name, open_datetime, and duration.');
        }
        if (strtotime($data['open_datetime']) <= time()) {
            throw new \Exception('Start date and time must be in the future.');
        }

        if (!is_numeric($data['duration']) || $data['duration'] <= 0) {
            throw new \Exception('Duration must be a positive number.');
        }

        $data['class_id'] = (int)$classId;
        // Parse and format open_datetime
        $openDateTime = str_replace('T', ' ', $data['open_datetime']) . ':00'; // Convert 2025-05-30T02:32 to 2025-05-30 02:32:00
        $openTimestamp = strtotime($openDateTime);
        if (!$openTimestamp) {
            throw new \Exception('Invalid open_datetime format.');
        }
        $data['open_datetime'] = date('Y-m-d H:i:s', $openTimestamp);
        // Validate and calculate close_datetime
        $duration = (int)$data['duration'];
        if (!is_numeric($duration) || $duration <= 0) {
            throw new \Exception('Duration must be a positive number.');
        }
        $data['close_datetime'] = date('Y-m-d H:i:s', $openTimestamp + $duration * 60);
        // Ensure all required fields are set, using defaults if not provided
        $data['status'] = $data['status'] ?? 'pending';
        $data['attendance_method'] = $data['attendance_method'] ?? 'manual';
        $data['auto_mark_attendance'] = $data['auto_mark_attendance'] ?? 'yes';
        $data['time_in_threshold'] = $data['time_in_threshold'] ?? '00:00:15';
        $data['time_out_threshold'] = $data['time_out_threshold'] ?? '00:00:15';
        $data['late_threshold'] = $data['late_threshold'] ?? '00:00:30';
        if (!$this->validate($data)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        log_message('debug','Hi' . json_encode($data));

        $this->insert($data);
        return $this->getInsertID();
    }

    public function updateSession($sessionId, $data)
    {
        if (!$this->find($sessionId)) {
            throw new \Exception('Session not found.');
        }
        if (!isset($data['class_session_name']) || !isset($data['open_datetime']) || !isset($data['duration'])) {
            throw new \Exception('Missing required fields: class_session_name, open_datetime, and duration.');
        }
        if (strtotime($data['open_datetime']) <= time()) {
            throw new \Exception('Start date and time must be in the future.');
        }
        if (!is_numeric($data['duration']) || $data['duration'] <= 0) {
            throw new \Exception('Duration must be a positive number.');
        }

        $data['open_datetime'] = date('Y-m-d H:i:s', strtotime($data['open_datetime']));
        $data['close_datetime'] = date('Y-m-d H:i:s', strtotime($data['open_datetime']) + $data['duration'] * 60);
        
        // Ensure all required fields are set
        $data['attendance_method'] = $data['attendance_method'] ?? 'manual';
        $data['auto_mark_attendance'] = $data['auto_mark_attendance'] ?? 'yes';
        $data['time_in_threshold'] = $data['time_in_threshold'] ?? '08:00:00';
        $data['time_out_threshold'] = $data['time_out_threshold'] ?? '09:00:00';
        $data['late_threshold'] = $data['late_threshold'] ?? '08:15:00';

        $data['class_session_id'] = $sessionId;
        if (!$this->validate($data)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($data['class_session_id']);

        return $this->update($sessionId, $data);
    }

    public function deleteSession($sessionId) {
        return $this->delete($sessionId);
    }

    public function softDelete($sessionId)
    {
        try {
            $this->delete($sessionId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function hardDelete($sessionId)
    {
        try {
            $this->onlyDeleted()->delete($sessionId, true);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($sessionId)
    {
        try {
            $this->onlyDeleted()->update($sessionId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
?>
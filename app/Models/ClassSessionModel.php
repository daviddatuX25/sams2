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
            'required' => 'The time-in threshold is required.'
        ],
        'time_out_threshold' => [
            'required' => 'The time-out threshold is required.'
        ],
        'late_threshold' => [
            'required' => 'The late threshold is required.'
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

    public function updateSession($sessionId, $sessionData)
    {
        $sessionData['class_session_id'] = $sessionId;
        if (!$this->validate($sessionData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($sessionData['class_session_id']);
        return $this->update($sessionId, $sessionData);
    }

    public function getSessionsByClass($classId, $withDeleted = false)
    {
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
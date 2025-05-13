<?php
namespace App\Models;

use CodeIgniter\Model;

class TeacherAssignmentModel extends Model
{
    protected $table = 'teacher_assignment';
    protected $primaryKey = 'assignment_id';
    protected $allowedFields = ['teacher_id', 'class_id', 'assigned_date', 'enrollment_term_id', 'deleted_at'];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'teacher_id' => 'required|is_not_unique[users.user_id]',
        'class_id' => 'required|is_not_unique[class.class_id]',
        'assigned_date' => 'permit_empty|valid_date',
        'enrollment_term_id' => 'required|is_not_unique[enrollment_term.enrollment_term_id]'
    ];

    protected $validationMessages = [
        'teacher_id' => [
            'required' => 'The teacher ID is required.',
            'is_not_unique' => 'The teacher ID must reference an existing user.'
        ],
        'class_id' => [
            'required' => 'The class ID is required.',
            'is_not_unique' => 'The class ID must reference an existing class.'
        ],
        'assigned_date' => [
            'valid_date' => 'The assigned date must be a valid date.'
        ],
        'enrollment_term_id' => [
            'required' => 'The term ID is required.',
            'is_not_unique' => 'The term ID must reference an existing term.'
        ]
    ];

    public function getAssignment($assignmentId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($assignmentId);
        }
        return $this->find($assignmentId);
    }

    public function assignmentExists($assignmentId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('assignment_id', $assignmentId)->get()->getRow() !== null;
    }

    public function createAssignment($assignmentData)
    {
        if (!$this->validate($assignmentData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($assignmentData);
        $assignmentId = $this->getInsertID();
        return $this->select('assignment_id, teacher_id, class_id, enrollment_term_id')->find($assignmentId);
    }

    public function updateAssignment($assignmentId, $assignmentData)
    {
        $assignmentData['assignment_id'] = $assignmentId;
        if (!$this->validate($assignmentData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($assignmentData['assignment_id']);
        return $this->update($assignmentId, $assignmentData);
    }

    public function assignTeacher($teacherId, $classId, $termId)
    {
        $data = [
            'teacher_id' => $teacherId,
            'class_id' => $classId,
            'enrollment_term_id' => $termId
        ];
        if (!$this->validate($data)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($data);
        return $this->getInsertID();
    }

    public function getAssignedClasses($teacherId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('teacher_id', $teacherId)->findAll();
        }
        return $this->where('teacher_id', $teacherId)->findAll();
    }

    public function getTeachersByClass($classId, $withDeleted = false) 
    {
        $builder = $this->where('class_id', $classId);
        if ($withDeleted) $builder->withDeleted();
        return $builder->findAll();
    }

    public function isTeacherAssigned($teacherId, $classId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('teacher_id', $teacherId)->where('class_id', $classId)->get()->getRow() !== null;
    }

    public function searchAssignments($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('assigned_date', $searchTerm)
                       ->get()->getResultArray();
    }

    public function softDelete($assignmentId)
    {
        try {
            $this->delete($assignmentId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function hardDelete($assignmentId)
    {
        try {
            $this->onlyDeleted()->delete($assignmentId, true);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($assignmentId)
    {
        try {
            $this->onlyDeleted()->update($assignmentId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
?>
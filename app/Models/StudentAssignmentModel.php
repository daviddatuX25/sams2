<?php
namespace App\Models;

use CodeIgniter\Model;

class StudentAssignmentModel extends Model
{
    protected $table = 'student_assignment';
    protected $primaryKey = 'enrollment_id';
    protected $allowedFields = ['student_id', 'class_id', 'enrollment_datetime', 'enrollment_term_id', 'deleted_at'];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'student_id' => 'required|is_not_unique[users.user_id]',
        'class_id' => 'required|is_not_unique[class.class_id]',
        'enrollment_datetime' => 'permit_empty|valid_date',
        'enrollment_term_id' => 'required|is_not_unique[enrollment_term.enrollment_term_id]'
    ];

    protected $validationMessages = [
        'student_id' => [
            'required' => 'The student ID is required.',
            'is_not_unique' => 'The student ID must reference an existing user.'
        ],
        'class_id' => [
            'required' => 'The class ID is required.',
            'is_not_unique' => 'The class ID must reference an existing class.'
        ],
        'enrollment_datetime' => [
            'valid_date' => 'The enrollment datetime must be a valid date.'
        ],
        'enrollment_term_id' => [
            'required' => 'The term ID is required.',
            'is_not_unique' => 'The term ID must reference an existing term.'
        ]
    ];

    public function getEnrollment($enrollmentId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($enrollmentId);
        }
        return $this->find($enrollmentId);
    }

    public function enrollmentExists($enrollmentId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('enrollment_id', $enrollmentId)->get()->getRow() !== null;
    }

    public function createEnrollment($enrollmentData)
    {
        if (!$this->validate($enrollmentData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($enrollmentData);
        $enrollmentId = $this->getInsertID();
        return $this->select('enrollment_id, student_id, class_id, enrollment_term_id')->find($enrollmentId);
    }

    public function updateEnrollment($enrollmentId, $enrollmentData)
    {
        $enrollmentData['enrollment_id'] = $enrollmentId;
        if (!$this->validate($enrollmentData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($enrollmentData['enrollment_id']);
        return $this->update($enrollmentId, $enrollmentData);
    }

    public function enrollStudent($studentId, $classId, $termId)
    {
        $data = [
            'student_id' => $studentId,
            'class_id' => $classId,
            'enrollment_term_id' => $termId
        ];
        if (!$this->validate($data)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($data);
        return $this->getInsertID();
    }

    public function getEnrolledStudents($classId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('class_id', $classId)->findAll();
        }
        return $this->where('class_id', $classId)->findAll();
    }

    public function getAssignmentsByStudent($studentId, $withDeleted = false) 
    {
        $builder = $this->where('student_id', $studentId);
        if ($withDeleted) $builder->withDeleted();
        return $builder->findAll();
    }

    public function isStudentEnrolled($studentId, $classId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('student_id', $studentId)->where('class_id', $classId)->get()->getRow() !== null;
    }

    public function searchEnrollments($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('enrollment_datetime', $searchTerm)
                       ->get()->getResultArray();
    }

    public function bulkEnroll($studentIds, $classId, $termId)
    {
        $success = [];
        $failed = [];

        foreach ($studentIds as $studentId) {
            try {
                if ($this->isStudentEnrolled($studentId, $classId)) {
                    $failed[] = "Student ID $studentId: Already enrolled.";
                    continue;
                }
                $enrollmentId = $this->enrollStudent($studentId, $classId, $termId);
                $success[] = "Student ID $studentId: Enrolled (ID: $enrollmentId).";
            } catch (\Exception $e) {
                $failed[] = "Student ID $studentId: " . $e->getMessage();
            }
        }

        return ['success' => $success, 'failed' => $failed];
    }

    public function getClassIdsForStudent($studentId)
    {
        return $this->where('student_id', $studentId)->findColumn('class_id') ?? [];
    }

    public function softDelete($enrollmentId)
    {
        try {
            $this->delete($enrollmentId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function hardDelete($enrollmentId)
    {
        try {
            $this->onlyDeleted()->delete($enrollmentId, true);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($enrollmentId)
    {
        try {
            $this->onlyDeleted()->update($enrollmentId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
    
}
?>
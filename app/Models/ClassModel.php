<?php
namespace App\Models;

use CodeIgniter\Model;

class ClassModel extends Model
{
    protected $table = 'class';
    protected $primaryKey = 'class_id';
    protected $allowedFields = ['class_name', 'class_description', 'subject_id', 'teacher_id', 'section', 'class_settings_id', 'deleted_at'];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'class_name' => 'required|max_length[255]',
        'class_description' => 'permit_empty',
        'subject_id' => 'required|is_not_unique[subject.subject_id]',
        'teacher_id' => 'required|is_not_unique[users.user_id]',
        'section' => 'required|max_length[10]',
        'class_settings_id' => 'required|is_not_unique[class_session_settings.class_session_settings_id]'
    ];

    protected $validationMessages = [
        'class_name' => [
            'required' => 'The class name is required.',
            'max_length' => 'The class name cannot exceed 255 characters.'
        ],
        'class_description' => [
            'permit_empty' => 'The class description is optional.'
        ],
        'subject_id' => [
            'required' => 'The subject ID is required.',
            'is_not_unique' => 'The subject ID must reference an existing subject.'
        ],
        'teacher_id' => [
            'required' => 'The teacher ID is required.',
            'is_not_unique' => 'The teacher ID must reference an existing user.'
        ],
        'section' => [
            'required' => 'The section is required.',
            'max_length' => 'The section cannot exceed 10 characters.'
        ],
        'class_settings_id' => [
            'required' => 'The class settings ID is required.',
            'is_not_unique' => 'The class settings ID must reference an existing setting.'
        ]
    ];

    public function getClass($classId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($classId);
        }
        return $this->find($classId);
    }

    public function classExists($classId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('class_id', $classId)->get()->getRow() !== null;
    }

    public function createClass($classData)
    {
        if (!$this->validate($classData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($classData);
        $classId = $this->getInsertID();
        return $this->select('class_id, class_name, subject_id, teacher_id, section')->find($classId);
    }

    public function updateClass($classId, $classData)
    {
        $classData['class_id'] = $classId;
        if (!$this->validate($classData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($classData['class_id']);
        return $this->update($classId, $classData);
    }

    public function getClassesBySubject($subjectId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('subject_id', $subjectId)->findAll();
        }
        return $this->where('subject_id', $subjectId)->findAll();
    }

    public function getClassesByTerm($termId, $withDeleted = false)
    {
        $builder = $this->select('class.*')
                        ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                        ->where('teacher_assignment.enrollment_term_id', $termId);
        if (!$withDeleted) {
            $builder->where('class.deleted_at IS NULL');
        }
        return $builder->findAll();
    }

    public function getClassesByTeacher($teacherId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('teacher_id', $teacherId)->findAll();
        }
        return $this->where('teacher_id', $teacherId)->findAll();
    }

    public function getClassesBySection($section, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('section', $section)->findAll();
        }
        return $this->where('section', $section)->findAll();
    }

    public function getClassSettings($classId)
    {
        $class = $this->find($classId);
        if ($class && isset($class['class_settings_id'])) {
            $settingsModel = new \App\Models\ClassSessionSettingsModel();
            return $settingsModel->find($class['class_settings_id']);
        }
        return null;
    }

    public function searchClasses($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('class_name', $searchTerm)
                       ->orLike('section', $searchTerm)
                       ->get()->getResultArray();
    }

    public function softDelete($classId)
    {
        try {
            $this->delete($classId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function hardDelete($classId)
    {
        try {
            $this->onlyDeleted()->delete($classId, true);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($classId)
    {
        try {
            $this->onlyDeleted()->update($classId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
?>
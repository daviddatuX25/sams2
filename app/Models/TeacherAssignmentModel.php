<?php

namespace App\Models;

use CodeIgniter\Model;

class TeacherAssignmentModel extends BaseModel
{
    protected $table = 'teacher_assignment';
    protected $primaryKey = 'assignment_id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'teacher_id',
        'class_id',
        'enrollment_term_id',
        'assigned_date',
        'deleted_at'
    ];
    protected $validationRules = [
        'teacher_id' => 'required|is_natural_no_zero',
        'class_id' => 'required|is_natural_no_zero',
        'enrollment_term_id' => 'required|is_natural_no_zero',
        'assigned_date' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'teacher_id' => [
            'required' => 'Teacher ID is required.',
            'integer' => 'Teacher ID must be an integer.'
        ],
        'class_id' => [
            'required' => 'Class ID is required.',
            'integer' => 'Class ID must be an integer.'
        ],
        'enrollment_term_id' => [
            'required' => 'Enrollment term ID is required.',
            'integer' => 'Enrollment term ID must be an integer.'
        ],
        'assigned_date' => [
            'required' => 'Assigned date is required.',
            'valid_date' => 'Assigned date is not valid.'
        ]
    ];

    // Find assignments by teacher_id
    public function findByTeacher($teacherId)
    {
        return $this->where('teacher_id', $teacherId)->findAll();
    }

    // Find assignments by class_id
    public function findByClass($classId)
    {
        return $this->where('class_id', $classId)->findAll();
    }
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentAssignmentModel extends BaseModel
{
    protected $table = 'student_assignment';
    protected $primaryKey = 'enrollment_id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'student_id',
        'class_id',
        'enrollment_term_id',
        'enrollment_datetime',
        'deleted_at'
    ];
    protected $validationRules = [
        'student_id' => 'required|is_natural_no_zero',
        'class_id' => 'required|is_natural_no_zero',
        'enrollment_term_id' => 'required|is_natural_no_zero',
        'enrollment_datetime' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student ID is required.',
            'integer' => 'Student ID must be an integer.'
        ],
        'class_id' => [
            'required' => 'Class ID is required.',
            'integer' => 'Class ID must be an integer.'
        ],
        'enrollment_term_id' => [
            'required' => 'Enrollment term ID is required.',
            'integer' => 'Enrollment term ID must be an integer.'
        ],
        'enrollment_datetime' => [
            'required' => 'Enrollment date is required.',
            'valid_date' => 'Enrollment date is not valid.'
        ]
    ];

    // Find assignments by student_id
    public function findByStudent($studentId)
    {
        return $this->where('student_id', $studentId)->findAll();
    }

    // Find assignments by class_id
    public function findByClass($classId)
    {
        return $this->where('class_id', $classId)->findAll();
    }
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassModel extends BaseModel
{
    protected $table = 'class';
    protected $primaryKey = 'class_id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'class_name',
        'class_description',
        'subject_id',
        'section',
        'deleted_at'
    ];
    protected $validationRules = [
        'class_name' => 'required|max_length[255]',
        'class_description' => 'permit_empty',
        'subject_id' => 'required|is_natural_no_zero',
        'section' => 'required|max_length[255]'
    ];

    protected $validationMessages = [
        'class_name' => [
            'required' => 'Class name is required.',
            'max_length' => 'Class name cannot exceed 100 characters.'
        ],
        'subject_id' => [
            'required' => 'Subject ID is required.',
            'integer' => 'Subject ID must be an integer.'
        ],
        'teacher_id' => [
            'required' => 'Teacher ID is required.',
            'integer' => 'Teacher ID must be an integer.'
        ],
        'enrollment_term_id' => [
            'required' => 'Enrollment term ID is required.',
            'integer' => 'Enrollment term ID must be an integer.'
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be one of: active, inactive.'
        ]
    ];

    // Find active classes
    public function findActive()
    {
        return $this->where('status', 'active')->findAll();
    }
}
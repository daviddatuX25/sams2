<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentTermModel extends Model
{
    protected $table = 'enrollment_term';
    protected $primaryKey = 'enrollment_term_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'academic_year',
        'semester',
        'term_start',
        'term_end',
        'term_description',
        'status',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'academic_year' => 'required|max_length[255]',
        'semester' => 'required|in_list[1st,2nd,summer]',
        'term_start' => 'required|valid_date',
        'term_end' => 'required|valid_date',
        'term_description' => 'permit_empty',
        'status' => 'required|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'academic_year' => [
            'required' => 'Academic year is required.',
            'max_length' => 'Academic year must not exceed 255 characters.',
        ],
        'semester' => [
            'required' => 'Semester is required.',
            'in_list' => 'Invalid semester.',
        ],
        'term_name' => [
            'required' => 'Term name is required.',
            'max_length' => 'Term name cannot exceed 100 characters.'
        ],
        'term_start' => [
            'required' => 'Start date is required.',
            'valid_date' => 'Start date must be a valid date.'
        ],
        'term_end' => [
            'required' => 'End date is required.',
            'valid_date' => 'End date must be a valid date.'
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be one of: active, inactive.'
        ]
    ];

    // Find active term
    public function findActive()
    {
        return $this->where('status', 'active')->first();
    }
}
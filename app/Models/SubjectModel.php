<?php

namespace App\Models;

use CodeIgniter\Model;

class SubjectModel extends Model
{
    protected $table = 'subject';
    protected $primaryKey = 'subject_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'subject_code',
        'subject_name',
        'subject_description',
        'subject_credits',
        'deleted_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [
        'subject_code' => 'required|is_unique[subject.subject_code,subject_id,{subject_id}]|max_length[255]',
        'subject_name' => 'required|max_length[255]',
        'subject_description' => 'permit_empty',
        'subject_credits' => 'required|is_natural'
    ];

    protected $validationMessages = [
        'subject_name' => [
            'required' => 'Subject name is required.',
            'max_length' => 'Subject name cannot exceed 100 characters.',
            'is_unique' => 'Subject name already exists.'
        ],
        'subject_code' => [
            'required' => 'Subject code is required.',
            'max_length' => 'Subject code cannot exceed 100 characters.',
            'is_unique' => 'Subject code already exists.'
        ],
        'subject_credits' => [
            'required' => 'Subject credits is required.', 
            'is_natural' => 'Subject credits must be a natural number.'
        ]
    ];

    // Find active subjects
    public function findActive()
    {
        return $this->where('status', 'active')->findAll();
    }
}
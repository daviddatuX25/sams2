<?php

namespace App\Models;

use CodeIgniter\Model;

class SubjectModel extends Model
{
    protected $table = 'subject';
    protected $primaryKey = 'subject_id';
    protected $allowedFields = ['subject_code', 'subject_name', 'subject_description', 'subject_credits', 'deleted_at'];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'subject_code' => 'required|is_unique[subject.subject_code,subject_id,{subject_id}]|max_length[50]',
        'subject_name' => 'required|max_length[255]',
        'subject_description' => 'permit_empty',
        'subject_credits' => 'required|integer|greater_than_equal_to[0]'
    ];

    protected $validationMessages = [
        'subject_code' => [
            'required' => 'The subject code is required.',
            'is_unique' => 'The subject code must be unique.',
            'max_length' => 'The subject code cannot exceed 50 characters.'
        ],
        'subject_name' => [
            'required' => 'The subject name is required.',
            'max_length' => 'The subject name cannot exceed 255 characters.'
        ],
        'subject_credits' => [
            'required' => 'The subject credits are required.',
            'integer' => 'The subject credits must be an integer.',
            'greater_than_equal_to' => 'The subject credits must be at least 0.'
        ]
    ];

    public function getSubject($subjectId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($subjectId);
        }
        return $this->find($subjectId);
    }

    public function updateSubject($subjectId, $subjectData)
    {
        $subjectData['subject_id'] = $subjectId;
        if (!$this->validate($subjectData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($subjectData['subject_id']);
        return $this->update($subjectId, $subjectData);
    }

    public function searchSubjects($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('subject_code', $searchTerm)
                       ->orLike('subject_name', $searchTerm)
                       ->get()->getResultArray();
    }

    public function getSubjectsByCredits($credits, $withDeleted = false) 
    {
        $builder = $this->where('subject_credits', $credits);
        if ($withDeleted) $builder->withDeleted();
        return $builder->findAll();
    }

    public function softDelete($subjectId)
    {
        try {
            $this->delete($subjectId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($subjectId)
    {
        try {
            $this->onlyDeleted()->update($subjectId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
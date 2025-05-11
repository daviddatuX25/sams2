<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentTermModel extends Model
{
    protected $table = 'enrollment_term';
    protected $primaryKey = 'enrollment_term_id';
    protected $allowedFields = [
        'academic_year', 'semester', 'sem_start', 'sem_end', 'term_start', 'term_end',
        'term_description', 'status', 'deleted_at'
    ];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'academic_year' => 'required|exact_length[9]',
        'semester' => 'required|in_list[1st,2nd,summer]',
        'sem_start' => 'required|valid_date',
        'sem_end' => 'required|valid_date',
        'term_start' => 'required|valid_date',
        'term_end' => 'required|valid_date',
        'term_description' => 'permit_empty',
        'status' => 'required|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'academic_year' => [
            'required' => 'The academic year is required.',
            'exact_length' => 'The academic year must be exactly 9 characters long.'
        ],
        'semester' => [
            'required' => 'The semester is required.',
            'in_list' => 'The semester must be one of: 1st, 2nd, summer.'
        ],
        'sem_start' => [
            'required' => 'The semester start date is required.',
            'valid_date' => 'The semester start date must be a valid date.'
        ],
        'sem_end' => [
            'required' => 'The semester end date is required.',
            'valid_date' => 'The semester end date must be a valid date.'
        ],
        'term_start' => [
            'required' => 'The term start date is required.',
            'valid_date' => 'The term start date must be a valid date.'
        ],
        'term_end' => [
            'required' => 'The term end date is required.',
            'valid_date' => 'The term end date must be a valid date.'
        ],
        'status' => [
            'required' => 'The status is required.',
            'in_list' => 'The status must be one of: active, inactive.'
        ]
    ];

    public function getTerm($termId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($termId);
        }
        return $this->find($termId);
    }

    public function updateTerm($termId, $termData)
    {
        $termData['enrollment_term_id'] = $termId;
        if (!$this->validate($termData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($termData['enrollment_term_id']);
        return $this->update($termId, $termData);
    }

    public function getCurrentTerm()
    {
        $currentDate = date('Y-m-d');
        return $this->where('term_start <=', $currentDate)
                    ->where('term_end >=', $currentDate)
                    ->where('status', 'active')
                    ->first();
    }

    public function softDelete($termId)
    {
        try {
            $this->delete($termId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($termId)
    {
        try {
            $this->onlyDeleted()->update($termId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
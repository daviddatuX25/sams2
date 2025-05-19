<?php

namespace App\Services;

use App\Models\EnrollmentTermModel;
use App\Traits\ServiceExceptionTrait;

class EnrollmentTermService
{
    use ServiceExceptionTrait;

    protected ?EnrollmentTermModel $enrollmentTermModel;

    public function __construct(?EnrollmentTermModel $enrollmentTermModel = null)
    {
        $this->enrollmentTermModel = $enrollmentTermModel ?? new EnrollmentTermModel();
    }

    /**
     * Create a new enrollment term.
     */
    public function createTerm(array $data): int
    {
        $this->enrollmentTermModel->insert($data);
        return $this->enrollmentTermModel->insertID();
    }


    /**
     * Retrieve enrollment terms.
     */
    public function getTerm($termId): array
    {
        return $this->enrollmentTermModel
            ->where('deleted_at IS NULL')
            ->where('enrollment_term_id', $termId);
    }

    /**
     * Retrieve enrollment terms.
     */
    public function getTerms(): array
    {
        return $this->enrollmentTermModel
            ->select('enrollment_term_id, academic_year, semester, term_start, term_end, term_description, status')
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Update an enrollment term.
     */
    public function updateTerm(int $termId, array $data): bool
    {
        $term = $this->enrollmentTermModel->where('enrollment_term_id', $termId)->where('deleted_at IS NULL')->first();
        if (!$term) {
            $this->throwNotFound('Enrollment Term', $termId);
        }

        return $this->enrollmentTermModel->update($termId, $data);
    }

    /**
     * Soft delete an enrollment term.
     */
    public function deleteTerm(int $termId): bool
    {
        $term = $this->enrollmentTermModel->where('enrollment_term_id', $termId)->where('deleted_at IS NULL')->first();
        if (!$term) {
            $this->throwNotFound('Enrollment Term', $termId);
        }

        return $this->enrollmentTermModel->delete($termId);
    }
}
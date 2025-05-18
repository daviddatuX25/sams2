<?php

namespace App\Services;

use App\Models\SubjectModel;
use App\Traits\ServiceExceptionTrait;

class SubjectService
{
    use ServiceExceptionTrait;

    protected ?SubjectModel $subjectModel;

    public function __construct(?SubjectModel $subjectModel = null)
    {
        $this->subjectModel = $subjectModel ?? new SubjectModel();
    }

    /**
     * Create a new subject.
     */
    public function createSubject(array $data): int
    {
        $this->subjectModel->insert($data);
        return $this->subjectModel->insertID();
    }

    /**
     * Retrieve subjects.
     */
    public function getSubjects(): array
    {
        return $this->subjectModel
            ->select('subject_id, subject_code, subject_name, subject_description, subject_credits')
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Update a subject.
     */
    public function updateSubject(int $subjectId, array $data): bool
    {
        $subject = $this->subjectModel->where('subject_id', $subjectId)->where('deleted_at IS NULL')->first();
        if (!$subject) {
            $this->throwNotFound('Subject', $subjectId);
        }

        return $this->subjectModel->update($subjectId, $data);
    }

    /**
     * Soft delete a subject.
     */
    public function deleteSubject(int $subjectId): bool
    {
        $subject = $this->subjectModel->where('subject_id', $subjectId)->where('deleted_at IS NULL')->first();
        if (!$subject) {
            $this->throwNotFound('Subject', $subjectId);
        }

        return $this->subjectModel->delete($subjectId);
    }
}
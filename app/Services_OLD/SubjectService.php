<?php

namespace App\Services;

use App\Models\SubjectModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class SubjectService
{
    protected $subjectModel;

    public function __construct(SubjectModel $subjectModel)
    {
        $this->subjectModel = $subjectModel;
    }

    public function getSubject(int $subjectId): array
    {
        $subject = $this->subjectModel->find($subjectId);
        if (!$subject) {
            throw new PageNotFoundException('Subject not found');
        }
        return $subject;
    }

    public function getAllSubjects(): array
    {
        return $this->subjectModel
            ->where('deleted_at IS NULL')
            ->findAll();
    }
}
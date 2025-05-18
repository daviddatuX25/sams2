<?php

namespace App\Services;

use App\Models\StudentAssignmentModel;
use App\Traits\ServiceExceptionTrait;

class StudentAssignmentService
{
    use ServiceExceptionTrait;

    protected ?StudentAssignmentModel $studentAssignmentModel;

    public function __construct(?StudentAssignmentModel $studentAssignmentModel = null)
    {
        $this->studentAssignmentModel = $studentAssignmentModel ?? new StudentAssignmentModel();
    }

    /**
     * Assign a student to a class and term.
     */
    public function createAssignment(array $data): int
    {
        // Ensure student exists and is a student
        $userModel = new \App\Models\UserModel();
        $student = $userModel->where('user_id', $data['student_id'])->where('role', 'student')->where('deleted_at IS NULL')->first();
        if (!$student) {
            $this->throwNotFound('Student', $data['student_id']);
        }

        // Ensure class exists
        $classModel = new \App\Models\ClassModel();
        if (!$classModel->where('class_id', $data['class_id'])->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Class', $data['class_id']);
        }

        // Ensure term exists
        $termModel = new \App\Models\EnrollmentTermModel();
        if (!$termModel->where('enrollment_term_id', $data['enrollment_term_id'])->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Enrollment Term', $data['enrollment_term_id']);
        }

        $this->studentAssignmentModel->insert($data);
        return $this->studentAssignmentModel->insertID();
    }

    /**
     * Retrieve student assignments.
     */
    public function getAssignments(): array
    {
        return $this->studentAssignmentModel
            ->select('enrollment_id, student_id, class_id, enrollment_term_id, enrollment_datetime')
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Update a student assignment.
     */
    public function updateAssignment(int $assignmentId, array $data): bool
    {
        $assignment = $this->studentAssignmentModel->where('enrollment_id', $assignmentId)->where('deleted_at IS NULL')->first();
        if (!$assignment) {
            $this->throwNotFound('Student Assignment', $assignmentId);
        }

        // Ensure student exists and is a student
        $userModel = new \App\Models\UserModel();
        $student = $userModel->where('user_id', $data['student_id'])->where('role', 'student')->where('deleted_at IS NULL')->first();
        if (!$student) {
            $this->throwNotFound('Student', $data['student_id']);
        }

        // Ensure class exists
        $classModel = new \App\Models\ClassModel();
        if (!$classModel->where('class_id', $data['class_id'])->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Class', $data['class_id']);
        }

        // Ensure term exists
        $termModel = new \App\Models\EnrollmentTermModel();
        if (!$termModel->where('enrollment_term_id', $data['enrollment_term_id'])->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Enrollment Term', $data['enrollment_term_id']);
        }

        return $this->studentAssignmentModel->update($assignmentId, $data);
    }

    /**
     * Soft delete a student assignment.
     */
    public function deleteAssignment(int $assignmentId): bool
    {
        $assignment = $this->studentAssignmentModel->where('enrollment_id', $assignmentId)->where('deleted_at IS NULL')->first();
        if (!$assignment) {
            $this->throwNotFound('Student Assignment', $assignmentId);
        }

        return $this->studentAssignmentModel->delete($assignmentId);
    }
}
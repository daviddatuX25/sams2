<?php

namespace App\Services;

use App\Models\TeacherAssignmentModel;
use App\Traits\ServiceExceptionTrait;

class TeacherAssignmentService
{
    use ServiceExceptionTrait;

    protected ?TeacherAssignmentModel $teacherAssignmentModel;

    public function __construct(?TeacherAssignmentModel $teacherAssignmentModel = null)
    {
        $this->teacherAssignmentModel = $teacherAssignmentModel ?? new TeacherAssignmentModel();
    }

    /**
     * Assign a teacher to a class and term.
     */
    public function createAssignment(array $data): int
    {
        // Ensure teacher exists and is a teacher
        $userModel = new \App\Models\UserModel();
        $teacher = $userModel->where('user_id', $data['teacher_id'])->where('role', 'teacher')->where('deleted_at IS NULL')->first();
        if (!$teacher) {
            $this->throwNotFound('Teacher', $data['teacher_id']);
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

        $this->teacherAssignmentModel->insert($data);
        return $this->teacherAssignmentModel->insertID();
    }

    /**
     * Retrieve teacher assignments.
     */
    public function getAssignments(): array
    {
        return $this->teacherAssignmentModel
            ->select('assignment_id, teacher_id, class_id, enrollment_term_id, assigned_date')
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Update a teacher assignment.
     */
    public function updateAssignment(int $assignmentId, array $data): bool
    {
        $assignment = $this->teacherAssignmentModel->where('assignment_id', $assignmentId)->where('deleted_at IS NULL')->first();
        if (!$assignment) {
            $this->throwNotFound('Teacher Assignment', $assignmentId);
        }

        // Ensure teacher exists and is a teacher
        $userModel = new \App\Models\UserModel();
        $teacher = $userModel->where('user_id', $data['teacher_id'])->where('role', 'teacher')->where('deleted_at IS NULL')->first();
        if (!$teacher) {
            $this->throwNotFound('Teacher', $data['teacher_id']);
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

        return $this->teacherAssignmentModel->update($assignmentId, $data);
    }

    /**
     * Soft delete a teacher assignment.
     */
    public function deleteAssignment(int $assignmentId): bool
    {
        $assignment = $this->teacherAssignmentModel->where('assignment_id', $assignmentId)->where('deleted_at IS NULL')->first();
        if (!$assignment) {
            $this->throwNotFound('Teacher Assignment', $assignmentId);
        }

        return $this->teacherAssignmentModel->delete($assignmentId);
    }
}
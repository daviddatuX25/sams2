<?php

namespace App\Services;

use App\Models\StudentAssignmentModel;
use App\Models\TeacherAssignmentModel;
use App\Models\EnrollmentTermModel;
use CodeIgniter\Validation\Exceptions\ValidationException;

class EnrollmentService
{
    protected $studentAssignmentModel;
    protected $teacherAssignmentModel;
    protected $enrollmentTermModel;

    public function __construct(
        StudentAssignmentModel $studentAssignmentModel,
        TeacherAssignmentModel $teacherAssignmentModel,
        EnrollmentTermModel $enrollmentTermModel
    ) {
        $this->studentAssignmentModel = $studentAssignmentModel;
        $this->teacherAssignmentModel = $teacherAssignmentModel;
        $this->enrollmentTermModel = $enrollmentTermModel;
    }

    public function enrollStudent(int $studentId, int $classId, int $termId): int
    {
        if (!is_numeric($studentId) || !is_numeric($classId) || !is_numeric($termId)) {
            throw new ValidationException('Student ID, Class ID, and Term ID must be integers.');
        }

        if ($this->studentAssignmentModel->where('student_id', $studentId)
                                        ->where('class_id', $classId)
                                        ->first()) {
            throw new ValidationException('Student is already enrolled in this class.');
        }

        return $this->studentAssignmentModel->insert([
            'student_id' => $studentId,
            'class_id' => $classId,
            'enrollment_term_id' => $termId
        ]);
    }

    public function assignTeacher(int $teacherId, int $classId, int $termId): int
    {
        if (!is_numeric($teacherId) || !is_numeric($classId) || !is_numeric($termId)) {
            throw new ValidationException('Teacher ID, Class ID, and Term ID must be integers.');
        }

        if ($this->teacherAssignmentModel->where('teacher_id', $teacherId)
                                         ->where('class_id', $classId)
                                         ->first()) {
            throw new ValidationException('Teacher is already assigned to this class.');
        }

        return $this->teacherAssignmentModel->insert([
            'teacher_id' => $teacherId,
            'class_id' => $classId,
            'enrollment_term_id' => $termId
        ]);
    }

    public function getClassesForStudent(int $studentId): array
    {
        if (!is_numeric($studentId)) {
            throw new ValidationException('Student ID must be an integer.');
        }

        return $this->studentAssignmentModel->join('class', 'class.class_id = student_assignment.class_id')
                                            ->join('user', 'user.user_id = class.teacher_id')
                                            ->where('student_assignment.student_id', $studentId)
                                            ->findAll();
    }

    public function getClassesForTeacher(int $teacherId): array
    {
        if (!is_numeric($teacherId)) {
            throw new ValidationException('Teacher ID must be an integer.');
        }

        return $this->teacherAssignmentModel->join('class', 'class.class_id = teacher_assignment.class_id')
                                            ->join('subject', 'subject.subject_id = class.subject_id')
                                            ->where('teacher_assignment.teacher_id', $teacherId)
                                            ->findAll();
    }

    public function getStudentsByClass(int $classId): array
    {
        if (!is_numeric($classId)) {
            throw new ValidationException('Class ID must be an integer.');
        }

        return $this->studentAssignmentModel->join('user', 'user.user_id = student_assignment.student_id')
                                            ->where('student_assignment.class_id', $classId)
                                            ->findAll();
    }
    public function isTeacherAssigned(int $userId, int $classId): bool
    {
        return $this->teacherAssignmentModel
            ->where('teacher_id', $userId)
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->countAllResults() > 0;
    }

    public function isStudentEnrolled(int $userId, int $classId): bool
    {
        return $this->studentAssignmentModel
            ->where('student_id', $userId)
            ->where('class_id', $classId)
            ->where('deleted_at IS NULL')
            ->countAllResults() > 0;
    }

    public function getClassStudents(int $classId): array
    {
        return $this->studentAssignmentModel
            ->select('user.*')
            ->join('user', 'user.user_id = student_assignment.student_id')
            ->where('student_assignment.class_id', $classId)
            ->where('student_assignment.deleted_at IS NULL')
            ->findAll();
    }
    
}
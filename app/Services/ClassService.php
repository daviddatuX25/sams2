<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\ClassSessionModel;
use App\Models\StudentAssignmentModel;
use App\Models\TeacherAssignmentModel;
use App\Traits\ServiceExceptionTrait;

class ClassService
{
    protected ClassModel $classModel; 
    public function __construct(
        ?ClassModel $classModel = null
    )
    {
        $this->classModel ??= $classModel ?? new ClassModel();
    }

    public function countActive(): int
    {
        return $this->classModel->where('status', 'active')->where('deleted_at IS NULL')->countAllResults();
    }

    public function getClasses(): array
    {
        return $this->classModel->where('deleted_at IS NULL')->findAll();
    }

    public function getClassesBySubject(int $subjectId): array
    {
        $subjectModel = new \App\Models\SubjectModel();
        if (!$subjectModel->where('subject_id', $subjectId)->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Subject', $subjectId);
        }

        return $this->classModel
            ->select('class_id, class_name, subject_id, enrollment_term_id, class_description')
            ->where('subject_id', $subjectId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Retrieve classes by enrollment term ID.
     */
    public function getClassesByTerm(int $termId): array
    {
        $termModel = new \App\Models\EnrollmentTermModel();
        if (!$termModel->where('enrollment_term_id', $termId)->where('deleted_at IS NULL')->first()) {
            $this->throwNotFound('Enrollment Term', $termId);
        }

        return $this->classModel
            ->select('class_id, class_name, subject_id, enrollment_term_id, class_description')
            ->where('enrollment_term_id', $termId)
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    public function student_getUserClasses(int $userId, int $currentTermId): ?array
    {
        if (!(new \App\Models\UserModel)->find($userId)){
            $this->throwNotFound('User id', $userId );
        }
        return $this->classModel
                    ->select('class.*, teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, subject.subject_name')
                    ->join('student_assignment', 'student_assignment.class_id = class.class_id')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id AND teacher_assignment.enrollment_term_id = student_assignment.enrollment_term_id')
                    ->join('user AS teacher', 'teacher.user_id = teacher_assignment.teacher_id')
                    ->join('subject', 'subject.subject_id = class.subject_id')
                    ->where('student_assignment.student_id', $userId)
                    ->where('student_assignment.enrollment_term_id', $currentTermId)
                    ->where('student_assignment.deleted_at IS NULL')
                    ->where('class.deleted_at IS NULL')
                    ->where('teacher_assignment.deleted_at IS NULL')
                    ->findAll();
    }

    public function teacher_getUserClasses(int $userId, int $currentTermId): ?array
    {
         if (!(new \App\Models\UserModel)->find($userId)){
             $this->throwNotFound('Teacher id', $userId );
        }

        return $this->classModel
                    ->select('class.*, teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, subject.*')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->join('user AS teacher', 'teacher.user_id = teacher_assignment.teacher_id')
                    ->join('subject', 'subject.subject_id = class.subject_id')
                    ->where('teacher_assignment.teacher_id', $userId)
                    ->where('teacher_assignment.enrollment_term_id', $currentTermId)
                    ->where('teacher_assignment.deleted_at IS NULL')
                    ->where('class.deleted_at IS NULL')
                    ->findAll();
    }

    public function student_getClassInfoByUser(int $userId, int $classId): ?array
    {
         if (!(new \App\Models\UserModel)->find($userId)){
             $this->throwNotFound('Student id', $userId );
        }

        return $this->classModel
                    ->select('class.*, teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, subject.*')
                    ->join('subject', 'subject.subject_id = class.subject_id')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->join('user AS teacher', 'teacher.user_id = teacher_assignment.teacher_id')
                    ->join('student_assignment', 'student_assignment.class_id = class.class_id')
                    ->where('class.class_id', $classId)
                    ->where('student_assignment.student_id', $userId)
                    ->where('student_assignment.deleted_at IS NULL')
                    ->where('class.deleted_at IS NULL')
                    ->first();
    }

    public function teacher_getClassInfoByUser(int $userId, int $classId): ?array
    {
        if (!(new \App\Models\UserModel)->find($userId)){
             $this->throwNotFound('Teacher id', $userId );
        }
        if (!$this->classModel->find($classId)){
            $this->throwNotFound('Class id', $classId );
        }

        return $this->classModel
                    ->select('class.*, teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, subject.*')
                    ->join('subject', 'subject.subject_id = class.subject_id')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->join('user AS teacher', 'teacher.user_id = teacher_assignment.teacher_id')
                    ->where('class.class_id', $classId)
                    ->where('teacher_assignment.teacher_id', $userId)
                    ->where('teacher_assignment.deleted_at IS NULL')
                    ->where('class.deleted_at IS NULL')
                    ->first();
    }

    public function admin_getClassInfoByUser(int $userId, int $classId): ?array
    {
         if (!(new \App\Models\UserModel)->find($userId)){
             $this->throwNotFound('Admin id', $userId );
        }
        if (!$this->classModel->find($classId)){
             $this->throwNotFound('Class id', $classId);
        }

        return $this->classModel
                    ->select('class.*, teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, subject.*')
                    ->join('subject', 'subject.subject_id = class.subject_id')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->join('user AS teacher', 'teacher.user_id = teacher_assignment.teacher_id')
                    ->join('student_assignment', 'student_assignment.class_id = class.class_id')
                    ->where('class.class_id', $classId)
                    ->where('student_assignment.deleted_at IS NULL')
                    ->where('teacher_assignment.deleted_at IS NULL')
                    ->first();
    }

    public function getClassRosterByUser(int $classId): ?array
    {
        if (!$this->classModel->find($classId)){
             $this->throwNotFound('Class id', $classId );
        }

        return $this->classModel
                    ->select('user.*')
                    ->join('student_assignment', 'student_assignment.class_id = class.class_id')
                    ->join('user', 'user.user_id = student_assignment.student_id')
                    ->where('class.class_id', $classId)
                    ->where('student_assignment.deleted_at IS NULL')
                    ->where('class.deleted_at IS NULL')
                    ->findAll();
    }
}
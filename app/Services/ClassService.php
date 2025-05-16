<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\ClassSessionModel;
use App\Models\StudentAssignmentModel;
use App\Models\TeacherAssignmentModel;

class ClassService
{
    protected ClassModel $classModel; 
    public function __construct(
        protected $userRole = null
    )
    {
        $this->classModel = new ClassModel();
        $this->userRole = $userRole ?? session()->get('role');
    }

    public function getUserClasses(int $userId, int $currentTermId): ?array
    {
        if ($this->userRole === 'student') {
            $studentAssignmentModel = new StudentAssignmentModel();
            return $studentAssignmentModel
                        ->select('class.*, teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, subject.subject_name')
                        ->join('class', 'class.class_id = student_assignment.class_id')
                        ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id AND teacher_assignment.enrollment_term_id = student_assignment.enrollment_term_id')
                        ->join('user AS teacher', 'teacher.user_id = teacher_assignment.teacher_id')
                        ->join('subject', 'subject.subject_id = class.subject_id')
                        ->where('student_assignment.student_id', $userId)
                        ->where('student_assignment.enrollment_term_id', $currentTermId)
                        ->where('student_assignment.deleted_at IS NULL')
                        ->where('class.deleted_at IS NULL')
                        ->where('teacher_assignment.deleted_at IS NULL')
                        ->findAll();
        } elseif ($this->userRole === 'teacher') {
            $teacherAssignmentModel = new TeacherAssignmentModel();
            return $teacherAssignmentModel
                        ->select('class.*, teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, subject.*')
                        ->join('class', 'class.class_id = teacher_assignment.class_id')
                        ->join('user AS teacher', 'teacher.user_id = teacher_assignment.teacher_id')
                        ->join('subject', 'subject.subject_id = class.subject_id')
                        ->where('teacher_assignment.teacher_id', $userId)
                        ->where('teacher_assignment.enrollment_term_id', $currentTermId)
                        ->where('teacher_assignment.deleted_at IS NULL')
                        ->where('class.deleted_at IS NULL')
                        ->findAll();
        }
        return [];
    }

    // Get basic class information.
   public function getClassInfoByUser(int $userId = null, int $classId ): ?array
    {
        $builder = $this->classModel
                    ->select('class.*, teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, subject.*')
                    ->join('subject', 'subject.subject_id = class.subject_id')
                    ->join('teacher_assignment', 'teacher_assignment.class_id = class.class_id')
                    ->join('user AS teacher', 'teacher.user_id = teacher_assignment.teacher_id')
                    ->where('class.class_id', $classId);
        if ($this->userRole === 'student' && $userId) {
            $builder->join('student_assignment', 'student_assignment.class_id = class.class_id')
                    ->where('student_assignment.student_id', $userId)
                    ->where('student_assignment.deleted_at IS NULL')
                    ->where('class.deleted_at IS NULL');

        } elseif ($this->userRole === 'teacher' && $userId) {
            $builder->where('teacher_assignment.teacher_id', $userId)
                    ->where('teacher_assignment.deleted_at IS NULL')
                    ->where('class.deleted_at IS NULL');
        } elseif ($this->userRole === 'admin'&& $userId) {
            $builder->join('student_assignment', 'student_assignment.class_id = class.class_id')
                    ->where('student_assignment.deleted_at IS NULL')
                    ->where('teacher_assignment.deleted_at IS NULL');
        } else {
            return null;
        }
        return $builder->first();
    }


    // Get student roster for a class.
    public function getClassRosterByUser(int $classId): ?array
    {
        $builder = $this->classModel
            ->select('user.*')
            ->join('student_assignment', 'student_assignment.class_id = class.class_id')
            ->join('user', 'user.user_id = student_assignment.student_id')
            ->where('class.class_id', $classId)
            ->where('student_assignment.deleted_at IS NULL')
            ->where('class.deleted_at IS NULL');
        return $builder->findAll();
    }
}
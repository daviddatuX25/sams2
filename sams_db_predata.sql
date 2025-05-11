-- 1. Insert into `users` (using plain-text passwords with is_password_temporary)
INSERT INTO `users` (`user_key`, `first_name`, `last_name`, `middle_name`, `birthday`, `password_hash`, `is_password_temporary`, `role`, `status`, `gender`, `bio`, `profile_picture`, `deleted_at`) VALUES
('admin001', 'John', 'Doe', 'A', '1980-01-01', 'admin123', 1, 'admin', 'active', 'Male', 'Admin user', NULL, NULL),
('teacher001', 'Jane', 'Smith', 'B', '1985-02-15', 'teacher123', 1, 'teacher', 'active', 'Female', 'Math teacher', NULL, NULL),
('student001', 'Alice', 'Johnson', 'C', '2000-03-20', 'student123', 1, 'student', 'active', 'Female', 'Computer Science student', NULL, NULL);

-- 2. Insert into `trackers`
INSERT INTO `trackers` (`tracker_name`, `tracker_description`, `tracker_type`, `status`, `deleted_at`) VALUES
('Tracker001', 'Facial recognition for Room A', 'face', 'active', NULL);

-- 3. Insert into `rooms`
INSERT INTO `rooms` (`room_name`, `room_description`, `room_capacity`, `room_type`, `room_status`, `tracker_id`, `deleted_at`) VALUES
('Room A', 'Classroom for lectures', 30, 'classroom', 'active', 1, NULL);

-- 4. Insert into `subject`
INSERT INTO `subject` (`subject_code`, `subject_name`, `subject_description`, `subject_credits`, `deleted_at`) VALUES
('CS101', 'Introduction to Programming', 'Basic programming concepts', 3, NULL);

-- 5. Insert into `enrollment_term`
INSERT INTO `enrollment_term` (`academic_year`, `semester`, `sem_start`, `sem_end`, `term_start`, `term_end`, `term_description`, `status`, `deleted_at`) VALUES
('2024-2025', '1st', '2024-08-01', '2024-12-15', '2024-08-01', '2024-12-15', 'First semester of 2024', 'active', NULL);

-- 6. Insert into `class_session_settings`
INSERT INTO `class_session_settings` (`attendance_method`, `time_in_threshold`, `time_out_threshold`, `late_threshold`, `auto_create_session`, `auto_mark_attendance`, `deleted_at`) VALUES
('automatic', '08:00:00', '10:00:00', '08:15:00', 'yes', 'yes', NULL);

-- 7. Insert into `class`
INSERT INTO `class` (`class_name`, `class_description`, `subject_id`, `teacher_id`, `section`, `class_settings_id`, `deleted_at`) VALUES
('CS101-A', 'Programming class section A', 1, 2, 'A', 1, NULL);

-- 8. Insert into `schedule`
INSERT INTO `schedule` (`room_id`, `time_start`, `time_end`, `week_day`, `class_id`, `status`, `deleted_at`) VALUES
(1, '08:00:00', '10:00:00', 'mon', 1, 'active', NULL);

-- 9. Insert into `class_sessions`
INSERT INTO `class_sessions` (`class_session_name`, `class_session_description`, `class_id`, `open_datetime`, `close_datetime`, `status`, `attendance_method`, `auto_mark_attendance`, `time_in_threshold`, `time_out_threshold`, `late_threshold`, `deleted_at`) VALUES
('Session 1', 'First session of CS101-A', 1, '2024-08-05 08:00:00', '2024-08-05 10:00:00', 'pending', 'automatic', 'yes', '08:00:00', '10:00:00', '08:15:00', NULL);

-- 10. Insert into `attendance_logs`
INSERT INTO `attendance_logs` (`user_id`, `class_session_id`, `tracker_id`, `action`, `timestamp`) VALUES
(3, 1, 1, 'time_in', '2024-08-05 08:05:00');

-- 11. Insert into `attendance`
INSERT INTO `attendance` (`user_id`, `class_session_id`, `status`) VALUES
(3, 1, 'present');

-- 12. Insert into `attendance_leave`
INSERT INTO `attendance_leave` (`user_id`, `status`, `letter`, `datetimestamp_created`, `datetimestamp_reviewed`, `datetimestamp_resolved`, `deleted_at`) VALUES
(3, 'pending', 'Sick leave request for 2024-08-06', '2024-08-05 10:00:00', NULL, NULL, NULL);

-- 13. Insert into `notifications`
INSERT INTO `notifications` (`user_id`, `message`, `status`, `created_datetime`, `read_datetime`, `deleted_at`)
VALUES (3, 'You have a new message in your inbox.', 'unread', NOW(), NULL, NULL);

-- 14. Insert into `student_assignment`
INSERT INTO `student_assignment` (`student_id`, `class_id`, `enrollment_datetime`, `enrollment_term_id`, `deleted_at`) VALUES
(3, 1, '2024-08-01 09:00:00', 1, NULL);

-- 15. Insert into `teacher_assignment`
INSERT INTO `teacher_assignment` (`teacher_id`, `class_id`, `assigned_date`, `enrollment_term_id`, `deleted_at`) VALUES
(2, 1, '2024-08-01 09:00:00', 1, NULL);
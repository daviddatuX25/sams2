-- Use the database
USE `sams_db`;

-- Start transaction
START TRANSACTION;

-- Insert users (2 students, 1 teacher, 1 admin)
INSERT INTO `user` (
  `user_key`, `password_hash`, `is_password_temporary`, `role`, `status`, `first_name`, `last_name`, `middle_name`, 
  `birthday`, `gender`, `bio`, `profile_picture`, `created_at`, `updated_at`
) VALUES
  ('student001', 'password123', 1, 'student', 'active', 'Juan', 'Dela Cruz', 'Santos', '2003-05-15', 'male', 'BSIT student', NULL, NOW(), NOW()),
  ('student002', 'password123', 1, 'student', 'active', 'Maria', 'Reyes', 'Lopez', '2004-08-22', 'female', 'BSCS student', NULL, NOW(), NOW()),
  ('teacher001', 'password123', 1, 'teacher', 'active', 'Pedro', 'Santos', 'Gomez', '1985-03-10', 'male', 'Mathematics professor', NULL, NOW(), NOW()),
  ('admin001', 'password123', 1, 'admin', 'active', 'Ana', 'Garcia', 'Mendoza', '1978-11-30', 'female', 'System administrator', NULL, NOW(), NOW());

-- Insert trackers
INSERT INTO `tracker` (
  `tracker_name`, `tracker_description`, `tracker_type`, `status`, `created_at`, `updated_at`
) VALUES
  ('FaceScanner1', 'Facial recognition scanner', 'face', 'active', NOW(), NOW()),
  ('RFID001', 'RFID card reader', 'rfid', 'active', NOW(), NOW());

-- Insert room
INSERT INTO `room` (
  `room_name`, `room_description`, `room_capacity`, `room_type`, `room_status`, `tracker_id`, `created_at`, `updated_at`
) VALUES
  ('Room101', 'Main classroom', 30, 'classroom', 'active', 1, NOW(), NOW());

-- Insert subject
INSERT INTO `subject` (
  `subject_code`, `subject_name`, `subject_description`, `subject_credits`, `created_at`, `updated_at`
) VALUES
  ('CS101', 'Introduction to Programming', 'Basic programming concepts', 3, NOW(), NOW());

-- Insert enrollment term
INSERT INTO `enrollment_term` (
  `academic_year`, `semester`, `term_start`, `term_end`, `term_description`, `status`, `created_at`, `updated_at`
) VALUES
  ('2024-2025', '1st', '2024-08-01', '2024-12-15', 'First semester 2024-2025', 'active', NOW(), NOW());

-- Insert class
INSERT INTO `class` (
  `class_name`, `class_description`, `subject_id`, `section`, `created_at`, `updated_at`
) VALUES
  ('CS101-A', 'Programming class section A', 1, 'A', NOW(), NOW());

-- Insert schedule
INSERT INTO `schedule` (
  `room_id`, `class_id`, `time_start`, `time_end`, `week_day`, `status`, `created_at`, `updated_at`
) VALUES
  (1, 1, '08:00:00', '10:00:00', 'mon', 'active', NOW(), NOW());

-- Insert class session
INSERT INTO `class_session` (
  `class_session_name`, `class_session_description`, `class_id`, `open_datetime`, `close_datetime`, 
  `status`, `attendance_method`, `auto_mark_attendance`, `time_in_threshold`, `time_out_threshold`, `late_threshold`, 
  `created_at`, `updated_at`
) VALUES
  ('Session 1', 'First class session', 1, '2024-08-05 08:00:00', '2024-08-05 10:00:00', 
   'pending', 'automatic', 'yes', '00:15:00', '00:15:00', '00:10:00', NOW(), NOW());

-- Insert attendance logs
INSERT INTO `attendance_logs` (
  `user_id`, `class_session_id`, `tracker_id`, `action`, `timestamp`, `created_at`, `updated_at`
) VALUES
  (1, 1, 1, 'time_in', '2024-08-05 07:55:00', NOW(), NOW()),
  (1, 1, 1, 'time_out', '2024-08-05 09:50:00', NOW(), NOW());

-- Insert attendance
INSERT INTO `attendance` (
  `user_id`, `class_session_id`, `status`, `is_manual`, `marked_at`, `created_at`, `updated_at`
) VALUES
  (1, 1, 'unmarked', 0, '2024-08-05 07:55:00', NOW(), NOW());

-- Insert attendance leave
INSERT INTO `attendance_leave` (
  `user_id`, `class_id`, `status`, `letter`, `datetimestamp_created`, `created_at`, `updated_at`
) VALUES
  (2, 1, 'pending', 'Sick leave request', '2024-08-06 08:00:00', NOW(), NOW());

-- Insert notifications
INSERT INTO `notifications` (
  `user_id`, `message`, `type`, `is_read`, `created_at`, `updated_at`
) VALUES
  (1, 'You logged in successfully.', 'info', 0, NOW(), NOW()),
  (2, 'Your leave request was submitted.', 'success', 0, NOW(), NOW());

-- Insert student assignments
INSERT INTO `student_assignment` (
  `student_id`, `class_id`, `enrollment_term_id`, `enrollment_datetime`, `created_at`, `updated_at`
) VALUES
  (1, 1, 1, '2024-08-01 09:00:00', NOW(), NOW()),
  (2, 1, 1, '2024-08-01 09:00:00', NOW(), NOW());

-- Insert teacher assignment
INSERT INTO `teacher_assignment` (
  `teacher_id`, `class_id`, `enrollment_term_id`, `assigned_date`, `created_at`, `updated_at`
) VALUES
  (3, 1, 1, '2024-08-01 09:00:00', NOW(), NOW());

-- Commit transaction
COMMIT;
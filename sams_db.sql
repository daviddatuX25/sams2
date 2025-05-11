-- Create the database
CREATE DATABASE IF NOT EXISTS `sams_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sams_db`;

-- Set session configurations
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Start transaction
START TRANSACTION;

-- Table: users
CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_key` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `birthday` date DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_password_temporary` TINYINT(1) NOT NULL DEFAULT 0,
  `role` enum('student','teacher','admin') NOT NULL,
  `status` enum('active','pending','archived') NOT NULL DEFAULT 'pending',
  `gender` varchar(50) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture` VARCHAR(255) NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `unique_user_key` (`user_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: trackers
CREATE TABLE `trackers` (
  `tracker_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tracker_name` varchar(255) NOT NULL,
  `tracker_description` text DEFAULT NULL,
  `tracker_type` enum('face','rfid') NOT NULL DEFAULT 'face',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`tracker_id`),
  UNIQUE KEY `tracker_name` (`tracker_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: rooms
CREATE TABLE `rooms` (
  `room_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_name` varchar(255) NOT NULL,
  `room_description` text DEFAULT NULL,
  `room_capacity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `room_type` enum('classroom','laboratory','office') NOT NULL DEFAULT 'classroom',
  `room_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `tracker_id` int(10) UNSIGNED DEFAULT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  KEY `idx_tracker` (`tracker_id`),
  CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`tracker_id`) REFERENCES `trackers` (`tracker_id`) ON DELETE SET NULL,
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_name` (`room_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: subject
CREATE TABLE `subject` (
  `subject_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `subject_description` text DEFAULT NULL,
  `subject_credits` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `subject_code` (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: enrollment_term
CREATE TABLE `enrollment_term` (
  `enrollment_term_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `academic_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','summer') NOT NULL,
  `sem_start` date NOT NULL,
  `sem_end` date NOT NULL,
  `term_start` date NOT NULL,
  `term_end` date NOT NULL,
  `term_description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`enrollment_term_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: class_session_settings
CREATE TABLE `class_session_settings` (
  `class_session_settings_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attendance_method` enum('manual','automatic') NOT NULL DEFAULT 'manual',
  `time_in_threshold` time NOT NULL,
  `time_out_threshold` time NOT NULL,
  `late_threshold` time NOT NULL,
  `auto_create_session` enum('yes','no') NOT NULL DEFAULT 'yes',
  `auto_mark_attendance` enum('yes','no') NOT NULL DEFAULT 'yes',
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`class_session_settings_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: class
CREATE TABLE `class` (
  `class_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL,
  `class_description` text DEFAULT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `section` varchar(10) NOT NULL,
  `class_settings_id` int(10) UNSIGNED NOT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  KEY `idx_class_settings` (`class_settings_id`),
  CONSTRAINT `class_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `class_ibfk_2` FOREIGN KEY (`class_settings_id`) REFERENCES `class_session_settings` (`class_session_settings_id`) ON DELETE CASCADE,
  CONSTRAINT `class_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE,
  PRIMARY KEY (`class_id`),
  KEY `idx_class_teacher` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: schedule
CREATE TABLE `schedule` (
  `rts_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` int(10) UNSIGNED NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `week_day` enum('mon','tue','wed','thu','fri','sat') NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`rts_id`),
  KEY `class_id` (`class_id`),
  KEY `idx_room_time` (`room_id`,`week_day`,`time_start`,`time_end`),
  CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: class_sessions
CREATE TABLE `class_sessions` (
  `class_session_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_session_name` varchar(255) NOT NULL,
  `class_session_description` text DEFAULT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `open_datetime` datetime NOT NULL,
  `close_datetime` datetime NOT NULL,
  `status` enum('marked','cancelled','pending') NOT NULL DEFAULT 'pending',
  `attendance_method` enum('manual','automatic') NOT NULL DEFAULT 'manual',
  `auto_mark_attendance` enum('yes','no') NOT NULL DEFAULT 'no',
  `time_in_threshold` time NOT NULL,
  `time_out_threshold` time NOT NULL,
  `late_threshold` time NOT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`class_session_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `class_sessions_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: attendance_logs
CREATE TABLE `attendance_logs` (
  `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `class_session_id` int(10) UNSIGNED NOT NULL,
  `tracker_id` int(10) UNSIGNED NOT NULL,
  `action` enum('time_in','time_out') NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_log_user` (`user_id`),
  KEY `idx_log_session` (`class_session_id`),
  KEY `idx_log_tracker` (`tracker_id`),
  CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_logs_ibfk_2` FOREIGN KEY (`class_session_id`) REFERENCES `class_sessions` (`class_session_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_logs_ibfk_3` FOREIGN KEY (`tracker_id`) REFERENCES `trackers` (`tracker_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: attendance
CREATE TABLE `attendance` (
  `attendance_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `class_session_id` int(10) UNSIGNED NOT NULL,
  `status` enum('present','absent','late','unmarked') NOT NULL DEFAULT 'unmarked',
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `idx_unique_attendance` (`user_id`,`class_session_id`),
  KEY `idx_attendance_session` (`class_session_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`class_session_id`) REFERENCES `class_sessions` (`class_session_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: attendance_leave
CREATE TABLE `attendance_leave` (
  `attendance_leave_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `letter` text NOT NULL,
  `datetimestamp_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datetimestamp_reviewed` datetime DEFAULT NULL,
  `datetimestamp_resolved` datetime DEFAULT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`attendance_leave_id`),
  KEY `idx_leave_user` (`user_id`),
  KEY `idx_leave_status` (`status`),
  CONSTRAINT `attendance_leave_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: notifications
CREATE TABLE `notifications` (
  `notif_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `status` enum('read','unread') NOT NULL DEFAULT 'unread',
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`notif_id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_status` (`status`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: student_assignment
CREATE TABLE `student_assignment` (
  `enrollment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `enrollment_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enrollment_term_id` int(10) UNSIGNED NOT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`enrollment_id`),
  KEY `idx_enrollment_user` (`student_id`),
  KEY `idx_enrollment_class` (`class_id`),
  KEY `enrollment_term_id` (`enrollment_term_id`),
  CONSTRAINT `student_assignment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `student_assignment_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `student_assignment_ibfk_3` FOREIGN KEY (`enrollment_term_id`) REFERENCES `enrollment_term` (`enrollment_term_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: teacher_assignment
CREATE TABLE `teacher_assignment` (
  `assignment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enrollment_term_id` int(10) UNSIGNED NOT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL, -- Soft delete column
  PRIMARY KEY (`assignment_id`),
  KEY `idx_teacher_class` (`teacher_id`,`class_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `teacher_assignment_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignment_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_teacher_assignment_term` FOREIGN KEY (`enrollment_term_id`) REFERENCES `enrollment_term` (`enrollment_term_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Commit transaction
COMMIT;
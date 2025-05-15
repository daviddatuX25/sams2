-- Create the database
CREATE DATABASE IF NOT EXISTS `sams_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sams_db`;

-- Set session configurations
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Start transaction
START TRANSACTION;

-- Table: user
CREATE TABLE `user` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_key` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_password_temporary` TINYINT(1) NOT NULL DEFAULT 0,
  `role` ENUM('student', 'teacher', 'admin') NOT NULL,
  `status` ENUM('active', 'pending', 'archived') NOT NULL DEFAULT 'pending',
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `middle_name` VARCHAR(50) DEFAULT NULL,
  `birthday` DATE DEFAULT NULL,
  `gender` ENUM('male', 'female', 'other') DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `profile_picture` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `unique_user_key` (`user_key`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: tracker
CREATE TABLE `tracker` (
  `tracker_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tracker_name` VARCHAR(255) NOT NULL,
  `tracker_description` TEXT DEFAULT NULL,
  `tracker_type` ENUM('face', 'rfid', 'manual') NOT NULL DEFAULT 'manual',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`tracker_id`),
  UNIQUE KEY `tracker_name` (`tracker_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: room
CREATE TABLE `room` (
  `room_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_name` VARCHAR(255) NOT NULL,
  `room_description` TEXT DEFAULT NULL,
  `room_capacity` INT UNSIGNED NOT NULL DEFAULT 0,
  `room_type` ENUM('classroom', 'laboratory', 'office') NOT NULL DEFAULT 'classroom',
  `room_status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `tracker_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_name` (`room_name`),
  KEY `idx_tracker` (`tracker_id`),
  CONSTRAINT `room_ibfk_1` FOREIGN KEY (`tracker_id`) REFERENCES `tracker` (`tracker_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: subject
CREATE TABLE `subject` (
  `subject_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject_code` VARCHAR(50) NOT NULL,
  `subject_name` VARCHAR(255) NOT NULL,
  `subject_description` TEXT DEFAULT NULL,
  `subject_credits` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `subject_code` (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: enrollment_term
CREATE TABLE `enrollment_term` (
  `enrollment_term_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `academic_year` VARCHAR(9) NOT NULL,
  `semester` ENUM('1st', '2nd', 'summer') NOT NULL,
  `term_start` DATE NOT NULL,
  `term_end` DATE NOT NULL,
  `term_description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`enrollment_term_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: class
CREATE TABLE `class` (
  `class_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_name` VARCHAR(255) NOT NULL,
  `class_description` TEXT DEFAULT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `section` VARCHAR(10) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`class_id`),
  KEY `idx_subject` (`subject_id`),
  CONSTRAINT `class_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: schedule
CREATE TABLE `schedule` (
  `schedule_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `time_start` TIME NOT NULL,
  `time_end` TIME NOT NULL,
  `week_day` ENUM('mon', 'tue', 'wed', 'thu', 'fri', 'sat') NOT NULL,
  `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `idx_room` (`room_id`),
  KEY `idx_class` (`class_id`),
  KEY `idx_room_time` (`room_id`, `week_day`, `time_start`, `time_end`),
  CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: class_session
CREATE TABLE `class_session` (
  `class_session_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_session_name` VARCHAR(255) NOT NULL,
  `class_session_description` TEXT DEFAULT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `open_datetime` DATETIME NOT NULL,
  `close_datetime` DATETIME NOT NULL,
  `status` ENUM('marked', 'cancelled', 'pending') NOT NULL DEFAULT 'pending',
  `attendance_method` ENUM('manual', 'automatic') NOT NULL DEFAULT 'manual',
  `auto_mark_attendance` ENUM('yes', 'no') NOT NULL DEFAULT 'no',
  `time_in_threshold` TIME DEFAULT '00:00:00',
  `time_out_threshold` TIME DEFAULT '00:00:00',
  `late_threshold` TIME DEFAULT '00:00:00',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`class_session_id`),
  KEY `idx_class` (`class_id`),
  CONSTRAINT `class_session_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: attendance_logs
CREATE TABLE `attendance_logs` (
  `log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `class_session_id` INT UNSIGNED NOT NULL,
  `tracker_id` INT UNSIGNED NOT NULL,
  `action` ENUM('time_in', 'time_out', 'auto') NOT NULL,
  `timestamp` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_session` (`class_session_id`),
  KEY `idx_tracker` (`tracker_id`),
  CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_logs_ibfk_2` FOREIGN KEY (`class_session_id`) REFERENCES `class_session` (`class_session_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_logs_ibfk_3` FOREIGN KEY (`tracker_id`) REFERENCES `tracker` (`tracker_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: attendance
CREATE TABLE `attendance` (
  `attendance_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `class_session_id` INT UNSIGNED NOT NULL,
  `status` ENUM('present', 'absent', 'late', 'unmarked') NOT NULL DEFAULT 'unmarked',
  `is_manual` TINYINT(1) NOT NULL DEFAULT 0,
  `marked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `idx_unique_attendance` (`user_id`, `class_session_id`),
  KEY `idx_session` (`class_session_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`class_session_id`) REFERENCES `class_session` (`class_session_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: attendance_leave
CREATE TABLE `attendance_leave` (
  `attendance_leave_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `letter` TEXT NOT NULL,
  `datetimestamp_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datetimestamp_reviewed` DATETIME DEFAULT NULL,
  `datetimestamp_resolved` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`attendance_leave_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_class` (`class_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `attendance_leave_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_leave_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: notifications
CREATE TABLE `notifications` (
  `notification_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'error') NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: student_assignment
CREATE TABLE `student_assignment` (
  `enrollment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `enrollment_term_id` INT UNSIGNED NOT NULL,
  `enrollment_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`enrollment_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_class` (`class_id`),
  KEY `idx_term` (`enrollment_term_id`),
  CONSTRAINT `student_assignment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `student_assignment_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `student_assignment_ibfk_3` FOREIGN KEY (`enrollment_term_id`) REFERENCES `enrollment_term` (`enrollment_term_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: teacher_assignment
CREATE TABLE `teacher_assignment` (
  `assignment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `teacher_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `enrollment_term_id` INT UNSIGNED NOT NULL,
  `assigned_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `idx_teacher` (`teacher_id`),
  KEY `idx_class` (`class_id`),
  KEY `idx_term` (`enrollment_term_id`),
  CONSTRAINT `teacher_assignment_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignment_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignment_ibfk_3` FOREIGN KEY (`enrollment_term_id`) REFERENCES `enrollment_term` (`enrollment_term_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Commit transaction
COMMIT;
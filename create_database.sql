-- Create database schema for IMATT College Student Result Management System
-- Run this first, then run database_migration.sql

USE srms;

-- Table: users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client',
  `enroll_no` varchar(50) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `c_year` varchar(50) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enroll_no` (`enroll_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: students
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `enroll_no` varchar(50) NOT NULL,
  `branch_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enroll_no` (`enroll_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: subjects
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_code` (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: marks (with semester support)
CREATE TABLE IF NOT EXISTS `marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `obtained_marks` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `grade` varchar(10) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  KEY `idx_semester_year` (`semester`, `academic_year`),
  KEY `idx_student_semester` (`student_id`, `semester`, `academic_year`),
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default lecturer account
-- Password: admin123 (MD5 hash)
-- Using numeric ID format
INSERT INTO `users` (`username`, `password`, `role`, `enroll_no`, `course`, `c_year`, `branch`) 
VALUES ('Lecturer', '0192023a7bbd73250516f069df18b500', 'admin', '100', 'All Courses', 'All Years', 'All Branches')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample student accounts with numeric IDs
-- Password: student123 (MD5 hash)
-- Example: Student ID 470 (as requested)
INSERT INTO `users` (`username`, `password`, `role`, `enroll_no`, `course`, `c_year`, `branch`) 
VALUES 
('John Doe', 'cd73502828457d15655bbd7a63fb0bc8', 'client', '470', 'Computer Science', '1st Year', 'Computer Science'),
('Jane Smith', 'cd73502828457d15655bbd7a63fb0bc8', 'client', '471', 'Business Administration', '2nd Year', 'Business Administration'),
('Test Student', 'cd73502828457d15655bbd7a63fb0bc8', 'client', '472', 'Law', '1st Year', 'Law')
ON DUPLICATE KEY UPDATE username=username;

INSERT INTO `students` (`name`, `enroll_no`, `branch_code`) 
VALUES 
('John Doe', '470', 'CS001'),
('Jane Smith', '471', 'BA001'),
('Test Student', '472', 'LAW001')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample subject
INSERT INTO `subjects` (`subject_code`, `subject_name`) 
VALUES ('BA101', 'Introduction to Business')
ON DUPLICATE KEY UPDATE subject_name=subject_name;


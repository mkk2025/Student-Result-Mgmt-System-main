-- PostgreSQL Schema for IMATT College Student Result Management System
-- For Supabase PostgreSQL Database
-- Run this in your Supabase SQL Editor

-- Table: users
CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(10) NOT NULL DEFAULT 'client' CHECK (role IN ('admin', 'client')),
  enroll_no VARCHAR(50) NOT NULL UNIQUE,
  course VARCHAR(100) DEFAULT NULL,
  c_year VARCHAR(50) DEFAULT NULL,
  branch VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: students
CREATE TABLE IF NOT EXISTS students (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  enroll_no VARCHAR(50) NOT NULL UNIQUE,
  branch_code VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: subjects
CREATE TABLE IF NOT EXISTS subjects (
  id SERIAL PRIMARY KEY,
  subject_code VARCHAR(50) NOT NULL UNIQUE,
  subject_name VARCHAR(200) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: marks (with semester support)
CREATE TABLE IF NOT EXISTS marks (
  id SERIAL PRIMARY KEY,
  student_id INTEGER NOT NULL,
  subject_id INTEGER NOT NULL,
  obtained_marks INTEGER NOT NULL,
  total_marks INTEGER NOT NULL,
  grade VARCHAR(10) NOT NULL,
  semester VARCHAR(50) NOT NULL,
  academic_year VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_marks_student_id ON marks(student_id);
CREATE INDEX IF NOT EXISTS idx_marks_subject_id ON marks(subject_id);
CREATE INDEX IF NOT EXISTS idx_marks_semester_year ON marks(semester, academic_year);
CREATE INDEX IF NOT EXISTS idx_marks_student_semester ON marks(student_id, semester, academic_year);
CREATE INDEX IF NOT EXISTS idx_users_enroll_no ON users(enroll_no);
CREATE INDEX IF NOT EXISTS idx_students_enroll_no ON students(enroll_no);

-- Insert default lecturer account
-- Password: admin123 (MD5 hash)
INSERT INTO users (username, password, role, enroll_no, course, c_year, branch) 
VALUES ('Lecturer', '0192023a7bbd73250516f069df18b500', 'admin', '100', 'All Courses', 'All Years', 'All Branches')
ON CONFLICT (enroll_no) DO UPDATE SET username = EXCLUDED.username;

-- Insert sample student accounts with numeric IDs
-- Password: student123 (MD5 hash)
INSERT INTO users (username, password, role, enroll_no, course, c_year, branch) 
VALUES 
('John Doe', 'ad6a280417a0f533d8b670c61667e1a0', 'client', '470', 'Computer Science', '1st Year', 'Computer Science'),
('Jane Smith', 'ad6a280417a0f533d8b670c61667e1a0', 'client', '471', 'Business Administration', '2nd Year', 'Business Administration'),
('Test Student', 'ad6a280417a0f533d8b670c61667e1a0', 'client', '472', 'Law', '1st Year', 'Law')
ON CONFLICT (enroll_no) DO UPDATE SET username = EXCLUDED.username;

INSERT INTO students (name, enroll_no, branch_code) 
VALUES 
('John Doe', '470', 'CS001'),
('Jane Smith', '471', 'BA001'),
('Test Student', '472', 'LAW001')
ON CONFLICT (enroll_no) DO UPDATE SET name = EXCLUDED.name;

-- Insert sample subjects
INSERT INTO subjects (subject_code, subject_name) 
VALUES 
('CS101', 'Introduction to Programming'),
('CS102', 'Data Structures'),
('MA101', 'Calculus I'),
('BA101', 'Introduction to Business')
ON CONFLICT (subject_code) DO UPDATE SET subject_name = EXCLUDED.subject_name;


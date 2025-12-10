# Database Setup Guide
## IMATT College - Student Result Management System

### Initial Database Schema

If you're setting up a new database, use the following schema:

```sql
-- Create database
CREATE DATABASE IF NOT EXISTS `srms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `srms`;

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
```

### Migration for Existing Database

If you already have a database, run the migration script:

1. **Backup your database first!**
   ```bash
   mysqldump -u root -p srms > srms_backup.sql
   ```

2. **Run the migration script:**
   ```bash
   mysql -u root -p srms < database_migration.sql
   ```

   Or execute the SQL commands from `database_migration.sql` in phpMyAdmin or your MySQL client.

### Default Admin Account Setup

After setting up the database, create a default lecturer/admin account:

```sql
-- Insert default lecturer account
-- Password: admin123 (MD5 hash)
-- Using numeric ID format (e.g., 100)
INSERT INTO `users` (`username`, `password`, `role`, `enroll_no`, `course`, `c_year`, `branch`) 
VALUES ('Lecturer', '0192023a7bbd73250516f069df18b500', 'admin', '100', 'All Courses', 'All Years', 'All Branches');
```

**Default Lecturer Login:**
- **ID Number**: 100
- **Password**: admin123

**Note:** Change the default password immediately after first login!

### Sample Student Accounts (Numeric IDs)

The system uses numeric Student IDs (e.g., 470, 471, 472):

```sql
-- Insert sample student accounts with numeric IDs
-- Password: student123 (MD5 hash)
INSERT INTO `users` (`username`, `password`, `role`, `enroll_no`, `course`, `c_year`, `branch`) 
VALUES 
('John Doe', 'cd73502828457d15655bbd7a63fb0bc8', 'client', '470', 'Computer Science', '1st Year', 'Computer Science'),
('Jane Smith', 'cd73502828457d15655bbd7a63fb0bc8', 'client', '471', 'Business Administration', '2nd Year', 'Business Administration');

INSERT INTO `students` (`name`, `enroll_no`, `branch_code`) 
VALUES 
('John Doe', '470', 'CS001'),
('Jane Smith', '471', 'BA001');
```

**Sample Student Logins:**
- **Student 1**: ID Number: 470, Password: student123
- **Student 2**: ID Number: 471, Password: student123

**ID Format:** Use numeric IDs (e.g., 470, 471, 472) for students. The system accepts any numeric or alphanumeric ID format.

### Important Notes

1. **Password Security**: The current system uses MD5 hashing. For production, consider upgrading to bcrypt or Argon2.

2. **Semester Format**: 
   - Semester values: "Semester 1", "Semester 2", etc.
   - Academic Year format: "2024/2025", "2023/2024", etc.

3. **Data Integrity**: 
   - Each student can have multiple marks entries for the same subject in different semesters
   - The system allows viewing results filtered by semester and academic year

4. **Backup Regularly**: Always backup your database before making schema changes.


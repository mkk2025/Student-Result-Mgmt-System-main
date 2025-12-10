-- Database Migration Script for Semester-Based Grading System
-- IMATT College - Student Result Management System
-- Run this script to add semester support to the existing database

-- Step 1: Add semester and academic_year columns to marks table
ALTER TABLE `marks` 
ADD COLUMN `semester` VARCHAR(50) NULL AFTER `grade`,
ADD COLUMN `academic_year` VARCHAR(20) NULL AFTER `semester`;

-- Step 2: Update existing records with default values (if any)
-- Update existing marks to have a default semester and academic year
-- Modify these values according to your needs
UPDATE `marks` 
SET `semester` = 'Semester 1', 
    `academic_year` = '2024/2025' 
WHERE `semester` IS NULL OR `academic_year` IS NULL;

-- Step 3: Make semester and academic_year required (NOT NULL) after updating existing data
-- Uncomment the following lines after you've updated all existing records:
-- ALTER TABLE `marks` 
-- MODIFY COLUMN `semester` VARCHAR(50) NOT NULL,
-- MODIFY COLUMN `academic_year` VARCHAR(20) NOT NULL;

-- Step 4: Add index for better query performance
CREATE INDEX `idx_semester_year` ON `marks` (`semester`, `academic_year`);
CREATE INDEX `idx_student_semester` ON `marks` (`student_id`, `semester`, `academic_year`);

-- Verification Query: Check the updated table structure
-- DESCRIBE `marks`;

-- Verification Query: View sample data with semester information
-- SELECT m.*, s.name as student_name, sub.subject_name 
-- FROM marks m 
-- JOIN students s ON m.student_id = s.id 
-- JOIN subjects sub ON m.subject_id = sub.id 
-- LIMIT 10;


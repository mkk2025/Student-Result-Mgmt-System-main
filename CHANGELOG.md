# Changelog - Semester-Based Grading System
## IMATT College - Student Result Management System

### Major Updates - Semester-Based Grading Implementation

#### 🔐 Authentication System
- **Changed**: Login now uses **Student ID Number** (Enrollment Number) instead of username
- **Updated**: Login form label changed from "Username" to "Student ID Number"
- **Improved**: Error messages now reference "ID number" instead of "username"
- **File**: `index.php`

#### 📚 Semester-Based Grading
- **Added**: Semester field to grade upload form (Semester 1-8)
- **Added**: Academic Year field to grade upload form (2024/2025 format)
- **Updated**: Database insert queries to include semester and academic_year
- **File**: `a_results.php`

#### 👨‍🏫 Lecturer Interface Updates
- **Changed**: "Admin" terminology updated to "Lecturer" throughout the system
- **Updated**: Navigation labels:
  - "Admin Dashboard" → "Lecturer Dashboard"
  - "Add Students" → "Manage Students"
  - "Add Results" → "Upload Grades"
- **Files**: `sidebar.php`, `a_dashboard.php`, `a_results.php`

#### 🎓 Student Results View
- **Added**: Semester selector dropdown
- **Added**: Academic Year selector dropdown
- **Added**: Filter results by semester and academic year
- **Added**: Display selected semester and academic year on marksheet
- **Updated**: Results table to show semester column
- **Improved**: Auto-selects most recent semester if none selected
- **File**: `s_results.php`

#### 📊 Database Schema
- **Added**: `semester` column to `marks` table
- **Added**: `academic_year` column to `marks` table
- **Added**: Database indexes for performance optimization
- **Created**: Migration script for existing databases
- **Files**: `database_migration.sql`, `DATABASE_SETUP.md`

#### 🎨 UI/UX Improvements
- **Updated**: Dashboard welcome messages
- **Updated**: Page titles to reflect new functionality
- **Improved**: Student results page with semester filtering interface
- **Enhanced**: Marksheet display with semester information
- **Files**: `dashboard.php`, `a_dashboard.php`, `s_results.php`

#### 📝 Documentation
- **Updated**: README.md with new features and usage instructions
- **Created**: DATABASE_SETUP.md with complete database setup guide
- **Created**: CHANGELOG.md (this file)
- **Added**: Default account credentials and setup instructions

### Technical Details

#### Database Changes Required
```sql
ALTER TABLE `marks` 
ADD COLUMN `semester` VARCHAR(50) NOT NULL,
ADD COLUMN `academic_year` VARCHAR(20) NOT NULL;
```

#### Login Changes
- **Before**: Login with username
- **After**: Login with Student ID Number (enrollment number)
- **Query Change**: `WHERE username='...'` → `WHERE enroll_no='...'`

#### Grade Upload Process
1. Lecturer enters Student ID Number
2. Selects Academic Year (e.g., 2024/2025)
3. Selects Semester (Semester 1-8)
4. Chooses subject and enters marks
5. System stores grade with semester and academic year

#### Student Results View Process
1. Student logs in with ID Number
2. Navigates to "View Academic Results"
3. Selects Academic Year and Semester from dropdowns
4. Views filtered results for selected semester
5. Can download marksheet for that semester

### Migration Instructions

#### For Existing Databases:
1. **Backup your database first!**
   ```bash
   mysqldump -u root -p srms > srms_backup.sql
   ```

2. **Run migration script:**
   ```bash
   mysql -u root -p srms < database_migration.sql
   ```

3. **Update existing records** (if any) with default semester values

#### For New Installations:
- Use the complete schema in `DATABASE_SETUP.md`
- Includes semester support from the start

### Breaking Changes
⚠️ **Important**: 
- Students must now login with their **ID Number** instead of username
- Existing student accounts will need to use their enrollment number to login
- Database migration is required for existing installations

### Testing Checklist
- [ ] Database migration completed successfully
- [ ] Lecturer can upload grades with semester selection
- [ ] Student can login with ID number
- [ ] Student can view results filtered by semester
- [ ] Semester selector shows available semesters
- [ ] Marksheet displays correct semester information
- [ ] Download marksheet works correctly
- [ ] All navigation links work properly

### Future Enhancements (Potential)
- GPA calculation per semester
- Cumulative GPA across all semesters
- Grade statistics and analytics
- Email notifications for grade uploads
- Bulk grade upload via CSV
- Grade history timeline view

---

**Version**: 2.0.0  
**Date**: December 2024  
**Developed By**: CORE BRIM TECH  
**For**: IMATT College


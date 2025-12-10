<div align="center">
  <img src="IMATT-LOGO-PNG.png" alt="IMATT College Logo" width="200">
  
  # Student Result Management System (SRMS)
  ## IMATT COLLEGE
  ### International Management, Accounting, Technology, and Tourism
  
  A PHP-based web application for managing student results and academic records at IMATT College.
</div>

## About IMATT College

**IMATT College** (International Management, Accounting, Technology, and Tourism) is a registered tertiary institution in Sierra Leone, established on May 26, 2009.

### Institutional Information
- **Full Name**: IMATT College (International Management, Accounting, Technology, and Tourism)
- **Established**: May 26, 2009
- **Accreditation**: Registered with the Tertiary Education Commission; affiliated with the University of Sierra Leone and its constituent colleges: Fourah Bay College (FBC), Institute of Public Administration and Management (IPAM), and College of Medicine and Allied Health Sciences (COMAHS)

### Mission Statement
To facilitate teaching and learning geared towards emulating the University of London International Programmes, through a convenient and safe ambiance, focusing on small group, distance, and online learning models provided by dedicated, committed, experienced practitioners.

### Contact Information
- **Address**: 14 Off Hennessy Street, Kingtom, Freetown, Sierra Leone
- **Phone**: +232 78 900082
- **Email**: info@imatcollege.com
- **Website**: https://imattcollege.com
- **Student Portal**: https://imatt.college

### Programs Offered
- Law (LLB Honours)
- Business Administration
- Computer Science
- Banking and Finance
- Nursing (Higher Diploma in Nursing in collaboration with COMAHS)

## Features

### Lecturer Role
- **Login**: Access using lecturer credentials
- **Manage Students**: Add/Remove students from the system
- **Upload Grades**: Upload student grades with semester and academic year
- **Semester-Based Grading**: Assign grades for specific semesters (Semester 1-8)
- **Academic Year Management**: Organize results by academic year (e.g., 2024/2025)
- **View Student Records**: Access and manage student academic information
- **Change Password**: Update account password

### Student Role
- **Login**: Access using Student ID Number (Enrollment Number) and password
- **View Results by Semester**: Filter and view academic results for specific semesters
- **Academic Year Filtering**: View results organized by academic year
- **Marksheet View**: View results in professional marksheet format
- **Download Marksheet**: Download marksheet as PNG image
- **View All Semesters**: Access results from all completed semesters
- **Personal Dashboard**: View enrollment details and course information
- **Change Password**: Update account password

## Technology Stack
- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Libraries**: html2canvas (for marksheet download)

## Installation

1. Clone or download this repository
2. Configure database connection in `config.php`:
   ```php
   $servername = "127.0.0.1";
   $username = "root";
   $password = ""; // Your database password
   $database = "srms"; // Your database name
   ```
3. **Database Setup**: 
   - For new installation: Use the schema in `DATABASE_SETUP.md`
   - For existing database: Run `database_migration.sql` to add semester support
   - See `DATABASE_SETUP.md` for detailed instructions
4. Place files in your web server directory (XAMPP/WAMP/LAMP)
5. Access the application via browser
6. **Default Login**: See `DATABASE_SETUP.md` for default account credentials

## Usage

### For Lecturers:
1. **Login**: Use lecturer credentials to access the lecturer dashboard
2. **Manage Students**: Add new students or update existing student information
3. **Upload Grades**: 
   - Enter student ID number
   - Select academic year and semester
   - Choose subject and enter marks
   - Submit grades for the selected semester

### For Students:
1. **Login**: Use Student ID Number (Enrollment Number) and password
2. **View Results**: 
   - Select academic year and semester from the dropdown
   - View filtered results for the selected semester
   - Download marksheet as PNG
3. **Access All Semesters**: Switch between different semesters to view historical results

## Project Structure

- `index.php` - Login page
- `signup.php` - Student registration
- `dashboard.php` - Student dashboard
- `a_dashboard.php` - Admin dashboard
- `a_students.php` - Add/Manage students
- `a_results.php` - Add/Manage results
- `s_results.php` - View results (marksheet)
- `config.php` - Database configuration
- `sidebar.php` - Navigation sidebar
- `footer.php` - Footer component

## Credits

**Designed With ❤️ By CORE BRIM TECH**

© 2024 SRMS Portal. All rights reserved.

---

**IMATT COLLEGE** - Student Result Management System

<?php
/**
 * DATABASE SETUP SCRIPT
 * Works with both MySQL (local) and PostgreSQL (Supabase)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Database Setup</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;padding:10px;background:#efe;border:1px solid #cfc;margin:10px 0;border-radius:5px;}";
echo ".error{color:red;padding:10px;background:#fee;border:1px solid #fcc;margin:10px 0;border-radius:5px;}";
echo ".info{color:#666;padding:10px;background:#fff;border:1px solid #ddd;margin:10px 0;border-radius:5px;}";
echo "h1{color:#E63946;}h2{color:#D62839;}</style></head><body>";
echo "<h1>🚀 Database Setup</h1>";

include 'config.php';

$isPostgreSQL = defined('USE_SUPABASE') && USE_SUPABASE;
$dbType = $isPostgreSQL ? "PostgreSQL (Supabase)" : "MySQL (Local)";

echo "<div class='info'>📋 Database Type: <strong>$dbType</strong></div>";

// Step 1: Test Connection
echo "<h2>Step 1: Testing Connection...</h2>";
if ($conn) {
    echo "<div class='success'>✅ Connected successfully!</div>";
} else {
    die("<div class='error'>❌ Connection failed!</div></body></html>");
}

// Step 2: Insert Default Data (tables already exist from create_database.sql)
echo "<h2>Step 2: Inserting/Updating Default Data...</h2>";

// Password hashes
$adminPass = md5('admin123');    // 0192023a7bbd73250516f069df18b500
$studentPass = md5('student123'); // ad6a280417a0f533d8b670c61667e1a0

$dataInserts = [];

if ($isPostgreSQL) {
    // PostgreSQL syntax
    $dataInserts = [
        "INSERT INTO users (username, password, role, enroll_no, course, c_year, branch) 
         VALUES ('Lecturer', '$adminPass', 'admin', '100', 'All Courses', 'All Years', 'All Branches')
         ON CONFLICT (enroll_no) DO UPDATE SET password = EXCLUDED.password",
        
        "INSERT INTO users (username, password, role, enroll_no, course, c_year, branch) 
         VALUES ('John Doe', '$studentPass', 'client', '470', 'Computer Science', '1st Year', 'CS')
         ON CONFLICT (enroll_no) DO UPDATE SET password = EXCLUDED.password",
        
        "INSERT INTO users (username, password, role, enroll_no, course, c_year, branch) 
         VALUES ('Jane Smith', '$studentPass', 'client', '471', 'Business Administration', '2nd Year', 'BA')
         ON CONFLICT (enroll_no) DO UPDATE SET password = EXCLUDED.password",
        
        "INSERT INTO students (name, enroll_no, branch_code) 
         VALUES ('John Doe', '470', 'CS001')
         ON CONFLICT (enroll_no) DO UPDATE SET name = EXCLUDED.name",
        
        "INSERT INTO students (name, enroll_no, branch_code) 
         VALUES ('Jane Smith', '471', 'BA001')
         ON CONFLICT (enroll_no) DO UPDATE SET name = EXCLUDED.name",
        
        "INSERT INTO subjects (subject_code, subject_name) 
         VALUES ('CS101', 'Introduction to Programming')
         ON CONFLICT (subject_code) DO UPDATE SET subject_name = EXCLUDED.subject_name",
        
        "INSERT INTO subjects (subject_code, subject_name) 
         VALUES ('CS102', 'Data Structures')
         ON CONFLICT (subject_code) DO UPDATE SET subject_name = EXCLUDED.subject_name",
        
        "INSERT INTO subjects (subject_code, subject_name) 
         VALUES ('BA101', 'Introduction to Business')
         ON CONFLICT (subject_code) DO UPDATE SET subject_name = EXCLUDED.subject_name"
    ];
} else {
    // MySQL syntax
    $dataInserts = [
        "INSERT INTO users (username, password, role, enroll_no, course, c_year, branch) 
         VALUES ('Lecturer', '$adminPass', 'admin', '100', 'All Courses', 'All Years', 'All Branches')
         ON DUPLICATE KEY UPDATE password = VALUES(password)",
        
        "INSERT INTO users (username, password, role, enroll_no, course, c_year, branch) 
         VALUES ('John Doe', '$studentPass', 'client', '470', 'Computer Science', '1st Year', 'CS')
         ON DUPLICATE KEY UPDATE password = VALUES(password)",
        
        "INSERT INTO users (username, password, role, enroll_no, course, c_year, branch) 
         VALUES ('Jane Smith', '$studentPass', 'client', '471', 'Business Administration', '2nd Year', 'BA')
         ON DUPLICATE KEY UPDATE password = VALUES(password)",
        
        "INSERT INTO students (name, enroll_no, branch_code) 
         VALUES ('John Doe', '470', 'CS001')
         ON DUPLICATE KEY UPDATE name = VALUES(name)",
        
        "INSERT INTO students (name, enroll_no, branch_code) 
         VALUES ('Jane Smith', '471', 'BA001')
         ON DUPLICATE KEY UPDATE name = VALUES(name)",
        
        "INSERT INTO subjects (subject_code, subject_name) 
         VALUES ('CS101', 'Introduction to Programming')
         ON DUPLICATE KEY UPDATE subject_name = VALUES(subject_name)",
        
        "INSERT INTO subjects (subject_code, subject_name) 
         VALUES ('CS102', 'Data Structures')
         ON DUPLICATE KEY UPDATE subject_name = VALUES(subject_name)",
        
        "INSERT INTO subjects (subject_code, subject_name) 
         VALUES ('BA101', 'Introduction to Business')
         ON DUPLICATE KEY UPDATE subject_name = VALUES(subject_name)"
    ];
}

$insertCount = 0;
foreach ($dataInserts as $sql) {
    try {
        $conn->query($sql);
        $insertCount++;
    } catch (Exception $e) {
        echo "<div class='info'>ℹ️ " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
echo "<div class='success'>✅ Processed $insertCount data operations</div>";

// Step 3: Verify Setup
echo "<h2>Step 3: Verifying Setup...</h2>";

$checks = [
    "SELECT COUNT(*) as count FROM users" => "Users",
    "SELECT COUNT(*) as count FROM students" => "Students",
    "SELECT COUNT(*) as count FROM subjects" => "Subjects",
    "SELECT COUNT(*) as count FROM marks" => "Marks"
];

foreach ($checks as $sql => $name) {
    try {
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $count = $row['count'] ?? 0;
        echo "<div class='success'>✅ $name table: $count records</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ $name check failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Step 4: Test Login Credentials
echo "<h2>Step 4: Testing Login Credentials...</h2>";

try {
    // Test lecturer
    $lecturerId = '100';
    $stmt = $conn->prepare('SELECT username, enroll_no, role FROM users WHERE enroll_no = ?');
    $stmt->bind_param('s', $lecturerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $lecturer = $result->fetch_assoc();
    $stmt->close();
    
    if ($lecturer) {
        echo "<div class='success'>✅ Lecturer account ready (ID: 100, Password: admin123)</div>";
    } else {
        echo "<div class='error'>❌ Lecturer account not found</div>";
    }
    
    // Test student
    $studentId = '470';
    $stmt = $conn->prepare('SELECT username, enroll_no, role FROM users WHERE enroll_no = ?');
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    
    if ($student) {
        echo "<div class='success'>✅ Student account ready (ID: 470, Password: student123)</div>";
    } else {
        echo "<div class='error'>❌ Student account not found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Credential test failed: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<h2>✅ Setup Complete!</h2>";
echo "<div class='success' style='font-size:18px;padding:20px;'>";
echo "<strong>🎉 Your database is ready!</strong><br><br>";
echo "📋 <strong>Login Credentials:</strong><br>";
echo "<table style='margin:10px 0;'>";
echo "<tr><td style='padding:5px 15px 5px 0;'>Lecturer:</td><td>ID = <code>100</code>, Password = <code>admin123</code></td></tr>";
echo "<tr><td style='padding:5px 15px 5px 0;'>Student:</td><td>ID = <code>470</code>, Password = <code>student123</code></td></tr>";
echo "<tr><td style='padding:5px 15px 5px 0;'>Student:</td><td>ID = <code>471</code>, Password = <code>student123</code></td></tr>";
echo "</table><br>";
echo "🔗 <a href='index.php' style='color:#E63946;font-weight:bold;font-size:20px;'>→ Go to Login Page</a>";
echo "</div>";

echo "</body></html>";
?>

<?php
session_start();
// Authentication check MUST be before any output
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}
include 'config.php';

$message = '';
$message_type = '';
$upload_stats = ['success' => 0, 'failed' => 0, 'errors' => []];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['csv_file'])) {
    if ($_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $conn->begin_transaction();
        
        try {
            while (($data = fgetcsv($handle)) !== FALSE) {
                // Expected CSV format: Student ID, Subject Code, Obtained Marks, Total Marks, Semester, Academic Year
                if (count($data) < 6) continue;
                
                $enroll_no = trim($data[0]);
                $subject_code = trim($data[1]);
                $obtained_marks = intval($data[2]);
                $total_marks = intval($data[3]);
                $semester = trim($data[4]);
                $academic_year = trim($data[5]);
                
                // Validation
                if ($total_marks <= 0 || $obtained_marks < 0 || $obtained_marks > $total_marks) {
                    $upload_stats['failed']++;
                    $upload_stats['errors'][] = "Row: Invalid marks for $enroll_no - $subject_code";
                    continue;
                }
                
                // Calculate grade
                $percentage = ($obtained_marks / $total_marks) * 100;
                if ($percentage >= 90) $grade = 'A+';
                elseif ($percentage >= 85) $grade = 'A';
                elseif ($percentage >= 80) $grade = 'A-';
                elseif ($percentage >= 75) $grade = 'B+';
                elseif ($percentage >= 70) $grade = 'B';
                elseif ($percentage >= 65) $grade = 'B-';
                elseif ($percentage >= 60) $grade = 'C+';
                elseif ($percentage >= 55) $grade = 'C';
                elseif ($percentage >= 50) $grade = 'C-';
                elseif ($percentage >= 45) $grade = 'D';
                else $grade = 'F';
                
                // Get student ID
                $stmt = $conn->prepare("SELECT id FROM students WHERE enroll_no = ?");
                $stmt->bind_param("s", $enroll_no);
                $stmt->execute();
                $result = $stmt->get_result();
                $student = $result->fetch_assoc();
                $stmt->close();
                
                if (!$student) {
                    $upload_stats['failed']++;
                    $upload_stats['errors'][] = "Row: Student $enroll_no not found";
                    continue;
                }
                
                // Get subject ID
                $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ?");
                $stmt->bind_param("s", $subject_code);
                $stmt->execute();
                $result = $stmt->get_result();
                $subject = $result->fetch_assoc();
                $stmt->close();
                
                if (!$subject) {
                    $upload_stats['failed']++;
                    $upload_stats['errors'][] = "Row: Subject $subject_code not found";
                    continue;
                }
                
                $student_id = $student['id'];
                $subject_id = $subject['id'];
                
                // Check if exists
                $check = $conn->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ? AND semester = ? AND academic_year = ?");
                $check->bind_param("iiss", $student_id, $subject_id, $semester, $academic_year);
                $check->execute();
                $existing = $check->get_result();
                
                if ($existing->num_rows > 0) {
                    // Update
                    $mark_id = $existing->fetch_assoc()['id'];
                    $check->close();
                    $stmt = $conn->prepare("UPDATE marks SET obtained_marks = ?, total_marks = ?, grade = ? WHERE id = ?");
                    $stmt->bind_param("iisi", $obtained_marks, $total_marks, $grade, $mark_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Insert
                    $check->close();
                    $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, obtained_marks, total_marks, grade, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiiisss", $student_id, $subject_id, $obtained_marks, $total_marks, $grade, $semester, $academic_year);
                    $stmt->execute();
                    $stmt->close();
                }
                
                $upload_stats['success']++;
            }
            
            $conn->commit();
            $message = "Bulk upload completed! Success: {$upload_stats['success']}, Failed: {$upload_stats['failed']}";
            $message_type = 'success';
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $message_type = 'error';
        }
        
        fclose($handle);
    } else {
        $message = "File upload error";
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Upload Grades</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .upload-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .upload-container h2 {
            color: #E63946;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .info-box {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #E63946;
        }
        
        .info-box h3 {
            color: #E63946;
            margin-top: 0;
        }
        
        .info-box code {
            background: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .file-upload {
            border: 2px dashed #ddd;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: border-color 0.3s;
        }
        
        .file-upload:hover {
            border-color: #E63946;
        }
        
        .file-upload input[type="file"] {
            margin: 20px 0;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #E63946 0%, #D62839 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .error-list {
            max-height: 200px;
            overflow-y: auto;
            background: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 12px;
        }
        
        .download-template {
            display: inline-block;
            padding: 10px 20px;
            background: #17a2b8;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .download-template:hover {
            background: #138496;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="content">
            <div class="upload-container">
                <h2>📊 Bulk Upload Grades (CSV)</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                        <?php if (!empty($upload_stats['errors']) && count($upload_stats['errors']) <= 10): ?>
                            <div class="error-list">
                                <?php foreach ($upload_stats['errors'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif (!empty($upload_stats['errors'])): ?>
                            <div class="error-list">
                                <div><strong>First 10 errors:</strong></div>
                                <?php foreach (array_slice($upload_stats['errors'], 0, 10) as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                                <div><em>... and <?php echo count($upload_stats['errors']) - 10; ?> more errors</em></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="info-box">
                    <h3>📋 CSV Format Instructions</h3>
                    <p><strong>Required columns (in order):</strong></p>
                    <ol>
                        <li><code>Student ID</code> - Enrollment number (e.g., 470)</li>
                        <li><code>Subject Code</code> - Subject code (e.g., CS101)</li>
                        <li><code>Obtained Marks</code> - Marks obtained (0-100)</li>
                        <li><code>Total Marks</code> - Total marks (usually 100)</li>
                        <li><code>Semester</code> - Semester (e.g., Semester 1)</li>
                        <li><code>Academic Year</code> - Academic year (e.g., 2024/2025)</li>
                    </ol>
                    <p><strong>Example CSV:</strong></p>
                    <pre style="background: white; padding: 10px; border-radius: 5px; overflow-x: auto;">Student ID,Subject Code,Obtained Marks,Total Marks,Semester,Academic Year
470,CS101,85,100,Semester 1,2024/2025
470,CS102,78,100,Semester 1,2024/2025
471,BA101,92,100,Semester 1,2024/2025</pre>
                    <a href="download_template.php" class="download-template">📥 Download CSV Template</a>
                </div>
                
                <form action="a_bulk_upload.php" method="POST" enctype="multipart/form-data">
                    <div class="file-upload">
                        <label for="csv_file" style="display: block; margin-bottom: 15px; font-weight: 500;">
                            Select CSV File:
                        </label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                        <p style="color: #666; font-size: 14px; margin-top: 10px;">
                            Maximum file size: 5MB
                        </p>
                    </div>
                    
                    <button type="submit" class="btn-submit">Upload & Process CSV</button>
                </form>
            </div>
        </div>
        <footer class="footer">
            <?php include 'footer.php';?>
        </footer>
    </div>
</body>
</html>


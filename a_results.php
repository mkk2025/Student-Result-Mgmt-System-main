<?php
session_start();
include 'config.php';
include 'sidebar.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

$message = '';
$message_type = '';
$student_name = '';

// Fetch all subjects from database
$subjects_query = "SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_name";
$subjects_result = $conn->query($subjects_query);
$subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
}

// AJAX endpoint to get student name
if (isset($_GET['get_student']) && isset($_GET['enroll_no'])) {
    $enroll_no = $_GET['enroll_no'];
    $stmt = $conn->prepare("SELECT name FROM students WHERE enroll_no = ?");
    $stmt->bind_param("s", $enroll_no);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'name' => $row['name']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
    }
    $stmt->close();
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enroll_no = $_POST['enroll_no'];
    $subject_id = $_POST['subject_id'];
    $total_marks = intval($_POST['total_marks']);
    $obtained_marks = intval($_POST['obtained_marks']);
    $semester = $_POST['semester'];
    $academic_year = $_POST['academic_year'];
    
    // Auto-calculate grade based on percentage
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
    
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Get student ID
        $stmt = $conn->prepare("SELECT id, name FROM students WHERE enroll_no = ?");
        $stmt->bind_param("s", $enroll_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
        
        if (!$student) {
            throw new Exception("Student with ID $enroll_no not found");
        }
        
        $student_id = $student['id'];
        $student_name = $student['name'];
        
        // Check if grade already exists for this student/subject/semester
        $check_stmt = $conn->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ? AND semester = ? AND academic_year = ?");
        $check_stmt->bind_param("iiss", $student_id, $subject_id, $semester, $academic_year);
        $check_stmt->execute();
        $existing = $check_stmt->get_result();
        
        if ($existing->num_rows > 0) {
            // Update existing record
            $mark_id = $existing->fetch_assoc()['id'];
            $check_stmt->close();
            
            $sql = "UPDATE marks SET obtained_marks = ?, total_marks = ?, grade = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisi", $obtained_marks, $total_marks, $grade, $mark_id);
            $stmt->execute();
            $stmt->close();
            $message = "Grade updated successfully for $student_name!";
        } else {
            $check_stmt->close();
            
            // Insert new record
            $sql = "INSERT INTO marks (student_id, subject_id, obtained_marks, total_marks, grade, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiisss", $student_id, $subject_id, $obtained_marks, $total_marks, $grade, $semester, $academic_year);
            $stmt->execute();
            $stmt->close();
            $message = "Grade uploaded successfully for $student_name!";
        }

        $conn->commit();
        $message_type = 'success';
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Student Grades</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .upload-container {
            max-width: 700px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #E63946;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
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
        
        .student-info {
            padding: 10px 15px;
            background: #e8f4fd;
            border-radius: 8px;
            margin-top: 5px;
            display: none;
        }
        
        .student-info.show {
            display: block;
        }
        
        .grade-preview {
            padding: 15px;
            background: linear-gradient(135deg, #E63946 0%, #D62839 100%);
            color: white;
            border-radius: 8px;
            text-align: center;
            margin-top: 10px;
            display: none;
        }
        
        .grade-preview.show {
            display: block;
        }
        
        .grade-preview .grade-letter {
            font-size: 36px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="upload-container">
                <h2>📝 Upload Student Grades</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form action="a_results.php" method="POST" id="gradeForm">
                    <div class="form-group">
                        <label for="enroll_no">Student ID Number:</label>
                        <input type="text" id="enroll_no" name="enroll_no" required 
                               placeholder="Enter student ID (e.g., 470)"
                               onkeyup="lookupStudent(this.value)">
                        <div class="student-info" id="studentInfo">
                            <strong>Student:</strong> <span id="studentName"></span>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="academic_year">Academic Year:</label>
                            <select id="academic_year" name="academic_year" required>
                                <option value="">Select Academic Year</option>
                                <option value="2024/2025">2024/2025</option>
                                <option value="2023/2024">2023/2024</option>
                                <option value="2022/2023">2022/2023</option>
                                <option value="2021/2022">2021/2022</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="semester">Semester:</label>
                            <select id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <option value="Semester 1">Semester 1</option>
                                <option value="Semester 2">Semester 2</option>
                                <option value="Semester 3">Semester 3</option>
                                <option value="Semester 4">Semester 4</option>
                                <option value="Semester 5">Semester 5</option>
                                <option value="Semester 6">Semester 6</option>
                                <option value="Semester 7">Semester 7</option>
                                <option value="Semester 8">Semester 8</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject_id">Subject:</label>
                        <select id="subject_id" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #666;">Don't see the subject? <a href="a_subjects.php" style="color: #E63946;">Add new subject</a></small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="total_marks">Total Marks:</label>
                            <input type="number" id="total_marks" name="total_marks" required 
                                   value="100" min="1" max="1000" onchange="calculateGrade()">
                        </div>
                        
                        <div class="form-group">
                            <label for="obtained_marks">Obtained Marks:</label>
                            <input type="number" id="obtained_marks" name="obtained_marks" required 
                                   min="0" placeholder="Enter marks" onkeyup="calculateGrade()">
                        </div>
                    </div>
                    
                    <div class="grade-preview" id="gradePreview">
                        <div>Auto-Calculated Grade:</div>
                        <div class="grade-letter" id="gradeLetter">-</div>
                        <div id="gradePercent">0%</div>
                    </div>
                    
                    <button type="submit" class="btn-submit">Upload Grade</button>
                </form>
            </div>
        </div>
        <footer class="footer">
            <?php include 'footer.php';?>
        </footer>
    </div>
    
    <script>
        let studentLookupTimeout;
        
        function lookupStudent(enrollNo) {
            clearTimeout(studentLookupTimeout);
            const infoDiv = document.getElementById('studentInfo');
            const nameSpan = document.getElementById('studentName');
            
            if (enrollNo.length < 2) {
                infoDiv.classList.remove('show');
                return;
            }
            
            studentLookupTimeout = setTimeout(() => {
                fetch(`a_results.php?get_student=1&enroll_no=${encodeURIComponent(enrollNo)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            nameSpan.textContent = data.name;
                            infoDiv.style.background = '#d4edda';
                            infoDiv.classList.add('show');
                        } else {
                            nameSpan.textContent = 'Student not found';
                            infoDiv.style.background = '#f8d7da';
                            infoDiv.classList.add('show');
                        }
                    })
                    .catch(() => {
                        infoDiv.classList.remove('show');
                    });
            }, 300);
        }
        
        function calculateGrade() {
            const total = parseInt(document.getElementById('total_marks').value) || 0;
            const obtained = parseInt(document.getElementById('obtained_marks').value) || 0;
            const preview = document.getElementById('gradePreview');
            const gradeLetter = document.getElementById('gradeLetter');
            const gradePercent = document.getElementById('gradePercent');
            
            if (total > 0 && obtained >= 0) {
                const percentage = (obtained / total) * 100;
                let grade;
                
                if (percentage >= 90) grade = 'A+';
                else if (percentage >= 85) grade = 'A';
                else if (percentage >= 80) grade = 'A-';
                else if (percentage >= 75) grade = 'B+';
                else if (percentage >= 70) grade = 'B';
                else if (percentage >= 65) grade = 'B-';
                else if (percentage >= 60) grade = 'C+';
                else if (percentage >= 55) grade = 'C';
                else if (percentage >= 50) grade = 'C-';
                else if (percentage >= 45) grade = 'D';
                else grade = 'F';
                
                gradeLetter.textContent = grade;
                gradePercent.textContent = percentage.toFixed(1) + '%';
                preview.classList.add('show');
            } else {
                preview.classList.remove('show');
            }
        }
    </script>
</body>
</html>

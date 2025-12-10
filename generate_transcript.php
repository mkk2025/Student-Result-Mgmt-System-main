<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
include 'config.php';

// Get student enrollment number
$enroll_no = $_SESSION['enroll_no'];

// Fetch all student results
$query = "SELECT 
    subjects.subject_code,
    subjects.subject_name,
    marks.obtained_marks,
    marks.total_marks,
    marks.grade,
    marks.semester,
    marks.academic_year
FROM marks 
JOIN students ON marks.student_id = students.id 
JOIN subjects ON marks.subject_id = subjects.id 
WHERE students.enroll_no = ? 
ORDER BY marks.academic_year ASC, marks.semester ASC, subjects.subject_code ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $enroll_no);
$stmt->execute();
$results = $stmt->get_result();
$all_results = $results->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get student info
$stmt = $conn->prepare("SELECT * FROM students WHERE enroll_no = ?");
$stmt->bind_param('s', $enroll_no);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate overall GPA
$total_points = 0;
$total_credits = 0;
foreach ($all_results as $row) {
    if ($row['total_marks'] > 0) {
        $percentage = ($row['obtained_marks'] / $row['total_marks']) * 100;
        if ($percentage >= 90) $points = 4.0;
        elseif ($percentage >= 80) $points = 3.5;
        elseif ($percentage >= 70) $points = 3.0;
        elseif ($percentage >= 60) $points = 2.5;
        elseif ($percentage >= 50) $points = 2.0;
        else $points = 1.0;
        
        $credits = 3;
        $total_points += ($points * $credits);
        $total_credits += $credits;
    }
}
$overall_gpa = $total_credits > 0 ? $total_points / $total_credits : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript</title>
    <link rel="stylesheet" href="style.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 20px; }
        }
        
        .transcript-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 40px;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .transcript-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #E63946;
            padding-bottom: 20px;
        }
        
        .transcript-header h1 {
            color: #E63946;
            margin: 0;
        }
        
        .transcript-header h2 {
            color: #D62839;
            font-size: 18px;
            margin: 5px 0;
        }
        
        .student-info {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .student-info p {
            margin: 8px 0;
            font-size: 16px;
        }
        
        .transcript-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .transcript-table th {
            background: #E63946;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .transcript-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }
        
        .transcript-table tr:hover {
            background: #f8f9fa;
        }
        
        .gpa-summary {
            background: linear-gradient(135deg, #E63946 0%, #D62839 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
        }
        
        .gpa-summary h3 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .gpa-value {
            font-size: 48px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .print-btn {
            background: #E63946;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 20px 0;
        }
        
        .print-btn:hover {
            background: #D62839;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="content">
            <div class="transcript-container">
                <div class="no-print" style="text-align: center; margin-bottom: 20px;">
                    <button onclick="window.print()" class="print-btn">🖨️ Print Transcript</button>
                </div>
                
                <div class="transcript-header">
                    <h1>IMATT COLLEGE</h1>
                    <h2>International Management, Accounting, Technology, and Tourism</h2>
                    <p>14 Off Hennessy Street, Kingtom, Freetown, Sierra Leone</p>
                    <p>Tel: +232 78 900082 | Email: info@imatcollege.com</p>
                </div>
                
                <h2 style="text-align: center; color: #E63946; margin: 30px 0;">ACADEMIC TRANSCRIPT</h2>
                
                <div class="student-info">
                    <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                    <p><strong>Student ID:</strong> <?php echo htmlspecialchars($enroll_no); ?></p>
                    <p><strong>Branch Code:</strong> <?php echo htmlspecialchars($student['branch_code'] ?? 'N/A'); ?></p>
                </div>
                
                <?php if (!empty($all_results)): ?>
                <table class="transcript-table">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Semester</th>
                            <th>Academic Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td><?php echo $row['obtained_marks']; ?>/<?php echo $row['total_marks']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['grade']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                            <td><?php echo htmlspecialchars($row['academic_year']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="gpa-summary">
                    <h3>Overall Academic Performance</h3>
                    <div class="gpa-value"><?php echo number_format($overall_gpa, 2); ?></div>
                    <p>Out of 4.0 Scale</p>
                    <p style="margin-top: 15px;">Total Subjects: <?php echo count($all_results); ?></p>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <h3>No academic records found</h3>
                    <p>Your transcript will appear here once grades are uploaded.</p>
                </div>
                <?php endif; ?>
                
                <div class="footer-note">
                    <p>This is an official transcript generated on <?php echo date('F d, Y'); ?></p>
                    <p>© <?php echo date('Y'); ?> IMATT College. All rights reserved.</p>
                </div>
            </div>
        </div>
        <footer class="footer no-print">
            <?php include 'footer.php';?>
        </footer>
    </div>
</body>
</html>

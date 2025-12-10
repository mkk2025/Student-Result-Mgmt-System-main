<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

include 'config.php';

// Get student statistics
$enroll_no = $_SESSION['enroll_no'];

// Get student info using prepared statement
$stmt = $conn->prepare("SELECT * FROM students WHERE enroll_no = ?");
$stmt->bind_param('s', $enroll_no);
$stmt->execute();
$student_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get all marks for GPA calculation and statistics using prepared statement
// Filter out records with zero total_marks to prevent division by zero
$gpa_query = "SELECT 
    marks.obtained_marks,
    marks.total_marks,
    marks.semester
    FROM marks 
    JOIN students ON marks.student_id = students.id 
    WHERE students.enroll_no = ? AND marks.total_marks > 0";
$stmt = $conn->prepare($gpa_query);
$stmt->bind_param('s', $enroll_no);
$stmt->execute();
$gpa_result = $stmt->get_result();
$gpa_rows = $gpa_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate GPA using discrete grade ranges (same as s_results.php)
$gpa = 0;
$total_points = 0;
$total_credits = 0;
$semesters = 0;
$subjects = 0;

if (!empty($gpa_rows)) {
    // Calculate unique semesters and total subjects
    $unique_semesters = [];
    $subjects = count($gpa_rows);
    
    // Calculate GPA using same discrete ranges as s_results.php
    foreach ($gpa_rows as $row) {
        // Skip records with zero or null total_marks to prevent division by zero
        if (empty($row['total_marks']) || $row['total_marks'] == 0) {
            continue;
        }
        
        // Track unique semesters
        if (!empty($row['semester']) && !in_array($row['semester'], $unique_semesters)) {
            $unique_semesters[] = $row['semester'];
        }
        
        $percentage = ($row['obtained_marks'] / $row['total_marks']) * 100;
        // Convert percentage to GPA (4.0 scale) - same ranges as s_results.php
        if ($percentage >= 90) $points = 4.0;
        elseif ($percentage >= 80) $points = 3.5;
        elseif ($percentage >= 70) $points = 3.0;
        elseif ($percentage >= 60) $points = 2.5;
        elseif ($percentage >= 50) $points = 2.0;
        else $points = 1.0;
        
        $credits = 3; // Assuming 3 credits per subject (can be made dynamic)
        $total_points += ($points * $credits);
        $total_credits += $credits;
    }
    
    $semesters = count($unique_semesters);
    
    if ($total_credits > 0) {
        $gpa = $total_points / $total_credits;
    }
}

$gpa = number_format($gpa, 2);

// Get recent results using prepared statement
$recent_query = "SELECT subjects.subject_name, marks.obtained_marks, marks.total_marks, marks.grade, marks.semester, marks.academic_year
     FROM marks 
     JOIN students ON marks.student_id = students.id 
     JOIN subjects ON marks.subject_id = subjects.id 
     WHERE students.enroll_no = ? 
     ORDER BY marks.academic_year DESC, marks.semester DESC 
     LIMIT 5";
$stmt = $conn->prepare($recent_query);
$stmt->bind_param('s', $enroll_no);
$stmt->execute();
$recent_results = $stmt->get_result();
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Dashboard</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .dashboard-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .info-card {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                border-left: 5px solid #E63946;
                transition: transform 0.3s, box-shadow 0.3s;
            }
            .info-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            }
            .info-card h3 {
                color: #666;
                font-size: 14px;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .info-card .value {
                color: #E63946;
                font-size: 32px;
                font-weight: bold;
                margin: 10px 0;
            }
            .gpa-card {
                background: linear-gradient(135deg, #E63946 0%, #D62839 100%);
                color: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 4px 15px rgba(230, 57, 70, 0.3);
                text-align: center;
            }
            .gpa-card .gpa-value {
                font-size: 48px;
                font-weight: bold;
                margin: 15px 0;
            }
            .welcome-banner {
                background: linear-gradient(135deg, #E63946 0%, #D62839 100%);
                color: white;
                padding: 30px;
                border-radius: 15px;
                margin-bottom: 30px;
                box-shadow: 0 4px 15px rgba(230, 57, 70, 0.3);
            }
            .welcome-banner h1 {
                color: white;
                margin-bottom: 10px;
            }
            .recent-results {
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                margin-top: 20px;
            }
            .recent-results table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            .recent-results th {
                background: #E63946;
                color: white;
                padding: 12px;
                text-align: left;
            }
            .recent-results td {
                padding: 12px;
                border-bottom: 1px solid #eee;
            }
            .recent-results tr:hover {
                background: #f8f9fa;
            }
            .quick-action-btn {
                display: inline-block;
                padding: 12px 25px;
                background: #E63946;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                margin: 10px 5px;
                transition: background 0.3s;
                font-weight: 500;
            }
            .quick-action-btn:hover {
                background: #D62839;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="welcome-banner">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                    <p style="font-size: 16px; opacity: 0.9;">Student Dashboard - IMATT College</p>
                </div>

                <div class="dashboard-container">
                    <div class="gpa-card">
                        <h3 style="color: rgba(255,255,255,0.9);">Current GPA</h3>
                        <div class="gpa-value"><?php echo $gpa; ?></div>
                        <p style="opacity: 0.9;">Out of 4.0 Scale</p>
                    </div>

                    <div class="info-card">
                        <h3>Student ID</h3>
                        <div class="value"><?php echo htmlspecialchars($_SESSION['enroll_no']); ?></div>
                    </div>

                    <div class="info-card">
                        <h3>Course</h3>
                        <div class="value" style="font-size: 24px;"><?php echo htmlspecialchars($_SESSION['course']); ?></div>
                    </div>

                    <div class="info-card">
                        <h3>Academic Year</h3>
                        <div class="value" style="font-size: 24px;"><?php echo htmlspecialchars($_SESSION['c_year']); ?></div>
                    </div>

                    <div class="info-card">
                        <h3>Semesters Completed</h3>
                        <div class="value"><?php echo $semesters; ?></div>
                    </div>

                    <div class="info-card">
                        <h3>Subjects Taken</h3>
                        <div class="value"><?php echo $subjects; ?></div>
                    </div>
                </div>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="s_results.php" class="quick-action-btn">View My Results</a>
                    <a href="cpassword.php" class="quick-action-btn">Change Password</a>
                </div>

                <?php if (mysqli_num_rows($recent_results) > 0): ?>
                <div class="recent-results">
                    <h2 style="color: #E63946; margin-bottom: 15px;">Recent Results</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Semester</th>
                                <th>Academic Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($result = mysqli_fetch_assoc($recent_results)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($result['obtained_marks']); ?>/<?php echo htmlspecialchars($result['total_marks']); ?></td>
                                <td><strong><?php echo htmlspecialchars($result['grade']); ?></strong></td>
                                <td><?php echo htmlspecialchars($result['semester']); ?></td>
                                <td><?php echo htmlspecialchars($result['academic_year']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                <?php $stmt->close(); ?>
            </div>
            <footer class="footer">
                <?php include 'footer.php'; ?>
            </footer>
        </div>
    </body>
</html>

<?php
// Close database connection
$conn->close();
?>

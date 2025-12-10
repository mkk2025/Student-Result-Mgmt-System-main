<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

include 'config.php';

// Get student statistics
$enroll_no = $_SESSION['enroll_no'];
$student_info = mysqli_query($conn, "SELECT * FROM students WHERE enroll_no = '$enroll_no'")->fetch_assoc();

// Get GPA and grade statistics
$gpa_query = "SELECT 
    AVG((marks.obtained_marks / marks.total_marks) * 4.0) as gpa,
    COUNT(DISTINCT marks.semester) as semesters_completed,
    COUNT(marks.id) as total_subjects
    FROM marks 
    JOIN students ON marks.student_id = students.id 
    WHERE students.enroll_no = '$enroll_no'";
$gpa_result = mysqli_query($conn, $gpa_query);
$gpa_data = $gpa_result->fetch_assoc();
$gpa = $gpa_data['gpa'] ? number_format($gpa_data['gpa'], 2) : '0.00';
$semesters = $gpa_data['semesters_completed'] ?: 0;
$subjects = $gpa_data['total_subjects'] ?: 0;

// Get recent results
$recent_results = mysqli_query($conn, 
    "SELECT subjects.subject_name, marks.obtained_marks, marks.total_marks, marks.grade, marks.semester, marks.academic_year
     FROM marks 
     JOIN students ON marks.student_id = students.id 
     JOIN subjects ON marks.subject_id = subjects.id 
     WHERE students.enroll_no = '$enroll_no' 
     ORDER BY marks.academic_year DESC, marks.semester DESC 
     LIMIT 5");
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
            </div>
            <footer class="footer">
                <?php include 'footer.php'; ?>
            </footer>
        </div>
    </body>
</html>

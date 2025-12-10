<?php
session_start();
// Authentication check MUST be before any output
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}
include 'config.php';

// Get statistics
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_subjects = $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'];
$total_grades = $conn->query("SELECT COUNT(*) as count FROM marks")->fetch_assoc()['count'];

// Get pass/fail statistics
$pass_count = $conn->query("SELECT COUNT(*) as count FROM marks WHERE grade IN ('A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-')")->fetch_assoc()['count'];
$fail_count = $conn->query("SELECT COUNT(*) as count FROM marks WHERE grade IN ('D', 'F')")->fetch_assoc()['count'];
$pass_rate = $total_grades > 0 ? ($pass_count / $total_grades) * 100 : 0;

// Get top performing students
$top_students_query = "
    SELECT s.name, s.enroll_no, 
           AVG(CASE 
               WHEN (CAST(m.obtained_marks AS DECIMAL(10,2)) / CAST(m.total_marks AS DECIMAL(10,2))) * 100 >= 90 THEN 4.0
               WHEN (CAST(m.obtained_marks AS DECIMAL(10,2)) / CAST(m.total_marks AS DECIMAL(10,2))) * 100 >= 80 THEN 3.5
               WHEN (CAST(m.obtained_marks AS DECIMAL(10,2)) / CAST(m.total_marks AS DECIMAL(10,2))) * 100 >= 70 THEN 3.0
               WHEN (CAST(m.obtained_marks AS DECIMAL(10,2)) / CAST(m.total_marks AS DECIMAL(10,2))) * 100 >= 60 THEN 2.5
               WHEN (CAST(m.obtained_marks AS DECIMAL(10,2)) / CAST(m.total_marks AS DECIMAL(10,2))) * 100 >= 50 THEN 2.0
               ELSE 1.0
           END) as avg_gpa,
           COUNT(m.id) as subject_count
    FROM students s
    JOIN marks m ON s.id = m.student_id
    WHERE m.total_marks > 0
    GROUP BY s.id, s.name, s.enroll_no
    HAVING COUNT(m.id) >= 3
    ORDER BY avg_gpa DESC
    LIMIT 10
";
$top_students = $conn->query($top_students_query)->fetch_all(MYSQLI_ASSOC);

// Get subject performance
$subject_performance_query = "
    SELECT sub.subject_code, sub.subject_name,
           AVG((CAST(m.obtained_marks AS DECIMAL(10,2)) / CAST(m.total_marks AS DECIMAL(10,2))) * 100) as avg_percentage,
           COUNT(m.id) as student_count
    FROM subjects sub
    JOIN marks m ON sub.id = m.subject_id
    WHERE m.total_marks > 0
    GROUP BY sub.id, sub.subject_code, sub.subject_name
    ORDER BY avg_percentage DESC
";
$subject_performance = $conn->query($subject_performance_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .reports-container {
            max-width: 1200px;
            margin: 20px auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            color: #E63946;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 5px solid #E63946;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-card .number {
            color: #E63946;
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .report-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        
        .report-section h3 {
            color: #E63946;
            margin-bottom: 20px;
            border-bottom: 2px solid #E63946;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #E63946;
            color: white;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        
        .progress-bar {
            width: 100%;
            height: 25px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="content">
            <div class="reports-container">
                <div class="page-header">
                    <h2>📊 Reports & Analytics Dashboard</h2>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Students</h3>
                        <div class="number"><?php echo $total_students; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Total Subjects</h3>
                        <div class="number"><?php echo $total_subjects; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Total Grades</h3>
                        <div class="number"><?php echo $total_grades; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Pass Rate</h3>
                        <div class="number"><?php echo number_format($pass_rate, 1); ?>%</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $pass_rate; ?>%;">
                                <?php echo number_format($pass_rate, 1); ?>%
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="report-section">
                    <h3>🏆 Top Performing Students</h3>
                    <?php if (!empty($top_students)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Average GPA</th>
                                <th>Subjects</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_students as $index => $student): ?>
                            <tr>
                                <td><strong>#<?php echo $index + 1; ?></strong></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['enroll_no']); ?></td>
                                <td><span class="badge badge-success"><?php echo number_format($student['avg_gpa'], 2); ?></span></td>
                                <td><?php echo $student['subject_count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="color: #666; text-align: center; padding: 20px;">No data available yet.</p>
                    <?php endif; ?>
                </div>
                
                <div class="report-section">
                    <h3>📚 Subject Performance Analysis</h3>
                    <?php if (!empty($subject_performance)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Average Score</th>
                                <th>Students</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subject_performance as $subject): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                <td><?php echo number_format($subject['avg_percentage'], 1); ?>%</td>
                                <td><?php echo $subject['student_count']; ?></td>
                                <td>
                                    <?php 
                                    $avg = $subject['avg_percentage'];
                                    if ($avg >= 80) echo '<span class="badge badge-success">Excellent</span>';
                                    elseif ($avg >= 70) echo '<span class="badge" style="background: #17a2b8; color: white;">Good</span>';
                                    elseif ($avg >= 60) echo '<span class="badge" style="background: #ffc107; color: #333;">Average</span>';
                                    else echo '<span class="badge badge-danger">Needs Improvement</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="color: #666; text-align: center; padding: 20px;">No data available yet.</p>
                    <?php endif; ?>
                </div>
                
                <div class="report-section">
                    <h3>📈 Grade Distribution</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <?php
                        $grades = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F'];
                        foreach ($grades as $grade):
                            $count = $conn->query("SELECT COUNT(*) as count FROM marks WHERE grade = '$grade'")->fetch_assoc()['count'];
                            $percentage = $total_grades > 0 ? ($count / $total_grades) * 100 : 0;
                        ?>
                        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                            <div style="font-size: 24px; font-weight: bold; color: #E63946;"><?php echo $grade; ?></div>
                            <div style="font-size: 20px; margin: 5px 0;"><?php echo $count; ?></div>
                            <div style="font-size: 12px; color: #666;"><?php echo number_format($percentage, 1); ?>%</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <footer class="footer">
            <?php include 'footer.php';?>
        </footer>
    </div>
</body>
</html>


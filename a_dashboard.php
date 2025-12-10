<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
?>

<?php
/*// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/?>

<?php 
include 'sidebar.php';
include 'config.php';

// Get actual statistics from database
$total_students = mysqli_query($conn, "SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_subjects = mysqli_query($conn, "SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'];
$total_marks = mysqli_query($conn, "SELECT COUNT(*) as count FROM marks")->fetch_assoc()['count'];
$pending_results = 0; // Can be calculated based on your business logic
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lecturer Dashboard</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .dashboard-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .stat-card {
                background: rgba(255, 255, 255, 0.95);
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-align: center;
                border-left: 4px solid #C8102E;
            }
            .stat-card h3 {
                color: #666;
                font-size: 14px;
                margin-bottom: 10px;
                text-transform: uppercase;
            }
            .stat-card .number {
                color: #C8102E;
                font-size: 36px;
                font-weight: bold;
                margin: 10px 0;
            }
            .welcome-section {
                background: rgba(255, 255, 255, 0.95);
                padding: 30px;
                border-radius: 10px;
                margin-bottom: 30px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .welcome-section h1 {
                color: #C8102E;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="welcome-section">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                    <p style="color: #666; font-size: 16px;">Lecturer Dashboard - IMATT College Grading System</p>
                    <p style="color: #888; margin-top: 10px;"><strong>Lecturer ID:</strong> <?php echo htmlspecialchars($_SESSION['enroll_no']); ?></p>
                </div>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Total Students</h3>
                        <div class="number"><?php echo $total_students; ?></div>
                        <p style="color: #888; font-size: 12px;">Registered students</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Total Subjects</h3>
                        <div class="number"><?php echo $total_subjects; ?></div>
                        <p style="color: #888; font-size: 12px;">Available subjects</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Grades Uploaded</h3>
                        <div class="number"><?php echo $total_marks; ?></div>
                        <p style="color: #888; font-size: 12px;">Total grade records</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Quick Actions</h3>
                        <div style="margin-top: 15px;">
                            <a href="a_students.php" style="display: inline-block; padding: 8px 15px; background: #C8102E; color: white; text-decoration: none; border-radius: 5px; margin: 5px;">Manage Students</a><br>
                            <a href="a_results.php" style="display: inline-block; padding: 8px 15px; background: #C8102E; color: white; text-decoration: none; border-radius: 5px; margin: 5px;">Upload Grades</a>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <?php include 'footer.php'; ?>
            </footer>
        </div>
    </body>
</html>

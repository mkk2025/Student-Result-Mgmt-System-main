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
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 25px;
                margin: 25px 0;
            }
            .stat-card {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                text-align: center;
                border-left: 5px solid #E63946;
                transition: transform 0.3s, box-shadow 0.3s;
            }
            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            }
            .stat-card h3 {
                color: #666;
                font-size: 14px;
                margin-bottom: 15px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .stat-card .number {
                color: #E63946;
                font-size: 42px;
                font-weight: bold;
                margin: 15px 0;
            }
            .welcome-section {
                background: linear-gradient(135deg, #E63946 0%, #D62839 100%);
                color: white;
                padding: 35px;
                border-radius: 15px;
                margin-bottom: 30px;
                box-shadow: 0 4px 15px rgba(230, 57, 70, 0.3);
            }
            .welcome-section h1 {
                color: white;
                margin-bottom: 10px;
            }
            .welcome-section p {
                opacity: 0.95;
            }
            .action-card {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .action-btn {
                display: inline-block;
                padding: 15px 30px;
                background: #E63946;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                margin: 10px;
                transition: all 0.3s;
                font-weight: 500;
                font-size: 16px;
            }
            .action-btn:hover {
                background: #D62839;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(230, 57, 70, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="welcome-section">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                    <p style="color: white; font-size: 16px; opacity: 0.95;">Lecturer Dashboard - IMATT College Grading System</p>
                    <p style="color: white; margin-top: 10px; opacity: 0.95;"><strong>Lecturer ID:</strong> <?php echo htmlspecialchars($_SESSION['enroll_no']); ?></p>
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
                        <div style="margin-top: 20px;">
                            <a href="a_students.php" class="action-btn">Manage Students</a>
                            <a href="a_results.php" class="action-btn">Upload Grades</a>
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

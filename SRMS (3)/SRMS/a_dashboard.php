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

<?php include 'sidebar.php'; ?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="style.css"> <!-- Add your CSS file here -->
    </head>
    <body>
        <div class="container">
            <div class="content">
                <h1>Hello,<?php echo $_SESSION['username']; ?> Wellcome to Student Result Management System!</h1><br><br>
                <p><h3>Name:<?php echo $_SESSION['username']; ?></h3></p><br>
                <p><h3>Admin Id:<?php echo $_SESSION['enroll_no']; ?></h3></p><br>
                <p><h3>Total Number of Students:<?php echo $_SESSION['course']; ?></h3></p><br>
                <p><h3>Total Number of Courses:<?php echo $_SESSION['c_year']; ?></h3></p><br>
                <p><h3>Pending Results:<?php echo $_SESSION['c_year']; ?></h3></p><br>
            </div>
            <footer class="footer">
                <?php include 'footer.php'; ?>
            </footer>
        </div>
    </body>
</html>

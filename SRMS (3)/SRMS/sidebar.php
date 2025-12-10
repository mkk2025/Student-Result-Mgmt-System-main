<?php
//session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
?>

<?php
    // Get the current page name
    $currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        <link rel="stylesheet" href="style.css"> <!-- Add your CSS file here -->
    </head>
    <body>
        <div class="sidebar">
            <ul>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li><img src="profile.png" alt="" width="100" height="70"></li>
                    <li><a href="a_dashboard.php"class="<?php if ($currentPage == 'a_dashboard.php') { echo 'active'; } ?>">Admin Dashboard</a></li>
                    <li><a href="a_students.php"class="<?php if ($currentPage == 'a_students.php') { echo 'active'; } ?>">Add Students</a></li>
                    <li><a href="a_results.php"class="<?php if ($currentPage == 'a_results.php') { echo 'active'; } ?>">Add Results</a></li>
                    <li><a href="cpassword.php"class="<?php if ($currentPage == 'cpassword.php') { echo 'active'; } ?>">Change Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><img src="profile.png" alt="" width="100" height="70"></li>
                    <li><a href="dashboard.php" class="<?php if ($currentPage == 'dashboard.php') { echo 'active'; } ?>">Student Dashboard</a></li>
                    <li><a href="s_results.php" class="<?php if ($currentPage == 's_results.php') { echo 'active'; } ?>">View Results</a></li>
                    <li><a href="cpassword.php"class="<?php if ($currentPage == 'cpassword.php') { echo 'active'; } ?>">Change Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </body>
</html>
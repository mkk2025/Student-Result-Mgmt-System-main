<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config.php';
include 'sidebar.php';
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}


// Assuming user is logged in, retrieve their ID from the session
$enroll_no = $_SESSION['enroll_no'];
$name=$_SESSION['username'];
$role=$_SESSION['role'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch user's current hashed password from the database
    if($role=="admin"){
        $query = "SELECT password FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

    }
    else{
        $query = "SELECT password FROM users WHERE enroll_no = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $enroll_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }
    

    // Verify current password
    if (!md5($current_password) === $user['password']) {
        echo "<script>alert('Current Password is incorrect. !'); window.location.href = 'cpassword.php';</script>";
        //die("Error: Current password is incorrect.");
        exit();
    }

    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        echo "<script>alert('The New Password and Confirm Password is Not Matching!'); window.location.href = 'cpassword.php';</script>";
        //die("Error: New passwords do not match with Confirm Password !");
        exit();
    }
    
    // Re-hash the password using bcrypt
    $new_hashed_password = password_hash($current_password, PASSWORD_BCRYPT);
    // Hash the new password
    $hashed_new_password = md5($new_password);
    //password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database
    $update_query = "UPDATE users SET password = ? WHERE enroll_no = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ss", $hashed_new_password, $enroll_no);

    if ($update_stmt->execute()) {
        echo "<script>alert('Password updated successfully!'); window.location.href = 'cpassword.php';</script>";
        //echo "<script>alert('Password updated successfully!');</script>";
    } else {
        echo "Error: Could not update password.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Students</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
             html,body{
                background: url('bg.jpg') no-repeat center center fixed;
                background-size: 100% 100%;
            }
            input[type="text"],input[type="password"]{
                border: 2px solid rgba(255, 255, 255, 0.5);
                background-color: rgba(255, 255, 255, 0.7);
            }

        </style>

    </head>
    <body>
        <div class="container" style="margin-left:280px;">
            <div class="content" style="margin-left:50px;">
                <h2>Change Your Password</h2><br>
                
                <form action="cpassword.php" method="POST">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required><br>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required><br>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required><br>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <footer class="footer">
                <?php include 'footer.php';?>
            </footer>
        </div>
    </body>
</html>

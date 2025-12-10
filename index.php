<?php
session_start();
include('config.php'); // Include database connection
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Pragma: no-cache");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_number = $_POST['id_number']; // Student ID number (enrollment number)
    $password = md5($_POST['password']); // You should use more secure hashing like bcrypt

    // Check user credentials using enrollment number (ID number) - using prepared statement
    $query = "SELECT * FROM users WHERE enroll_no = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $id_number, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['enroll_no'] = $user['enroll_no'];
        $_SESSION['course'] = $user['course'];
        $_SESSION['c_year'] = $user['c_year'];
        $stmt->close();
        if ($_SESSION['role'] == 'client') {
            header('Location: dashboard.php');
        } else {
            header('Location: a_dashboard.php');
        }
        exit();
    } else {
        $stmt->close();
        echo "<script>alert('Invalid ID number or password'); window.location.href='index.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>IMATT College - Student Portal Login</title>
        <!--<link rel="stylesheet" href="stylee.css">-->
        <style>
        html{
            height:100%;
        }
        .header{
            position: relative;
            top: 0;
            text-align: center;
            color: white;
            font-size: 28px;
            padding: 20px 0;
            z-index: 10;
            margin-bottom: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .body {
            display: flex;
            flex-direction:column;
            justify-content: flex-start;
            text-align:center;
            align-items: center;
            min-height: 100vh;
            padding-top: 20px;
            position: relative;
        }
        .body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('back.png') no-repeat center center;
            background-size: cover;
            filter: blur(8px); /* Blur effect for better text visibility */
            z-index: -1; /* Ensures it stays behind all content */
        }
        .body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3); /* Dark overlay for better text contrast */
            z-index: -1;
        }

        .login-box {
            width: 400px;
            padding: 40px;
            position: relative;
            background: rgba(200, 16, 46, 0.85); /* IMATT red with transparency */
            border-radius: 10px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px); /* Adds a blur effect */
            color: white;
            z-index: 10;
            margin-top: 20px;
        }

        .login-box h2 {
            margin-bottom: 30px;
            text-align: center;
        }

        .input-box {
            position: relative;
            margin-bottom: 30px;
        }

        .input-box input {
            width: 100%;
            padding: 10px;
            background: none;
            border: none;
            border-bottom: 2px solid white;
            outline: none;
            color: white;
            font-size: 16px;
        }

        .input-box label {
            position: absolute;
            top: 0;
            left: 0;
            padding: 10px 0;
            color: white;
            pointer-events: none;
            transition: 0.5s;
        }

        .input-box input:focus ~ label,
        .input-box input:valid ~ label {
            top: -20px;
            left: 0;
            color:  #FFD700; /* IMATT College Gold */
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #C8102E; /* IMATT College Red */
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #A01D26; /* Darker red on hover */
        }
        .footer {
            bottom: 10px; /* Adjust the distance from the bottom of the page */
            color: white;
            font-size: 14px;
            text-align:center;
            padding:5px;
        }
        .container {
            max-width: 1200px;  /* Maximum width */
            margin: 0 auto;     /* Centers the container */
            padding: 20px;      /* Adds space inside the container */
            align-items:center;
            flex-direction:column;
            align-items: center;
        }
        .content {
            flex: 2; /* Takes remaining space, pushes footer down */
            padding: 20px;
        }
        .signup{
            width: 35%;
            padding: 10px;
            background: #03a9f4;
            border: none;
            cursor: pointer;
            font-size: 12px;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
            
        }

        </style>
    </head>
    <body class="body">
        <div class="header">
            <img src="IMATT-LOGO-PNG.png" alt="IMATT College Logo" style="max-width: 150px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
            <h1>IMATT College - Student Result Management System</h1>
            <p style="font-size: 16px; margin-top: 10px;">International Management, Accounting, Technology, and Tourism</p>
        </div>
        <div class="container">
            <div class="content">
                <div class="login-box">
                    <h2>Login</h2>
                    <form action="index.php" method="POST">
                        <div class="input-box">
                            <input type="text" name="id_number" required>
                            <label>Student ID Number</label>
                        </div>  
                        <div class="input-box">
                            <input type="password" name="password" required>
                            <label>Password</label>
                        </div>
                        <button type="submit" name="submit">Login</button>
                    </form>
                    <!--<br>
                    <h5>Not Registered Yet?  <button onclick="window.location.href='signup.php'" class="signup"><h4>SIGN UP</h4</button></h5>-->
                </div>
            </div>   
            <footer class="footer">
                <p>© 2024 SRMS Portal. All rights reserved.</p>
                <p>Designed With❤️ By CORE BRIM TECH </p>
            </footer>
        </div>
    </body>
</html>





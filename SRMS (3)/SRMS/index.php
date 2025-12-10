<?php
session_start();
include('config.php'); // Include database connection
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Pragma: no-cache");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // You should use more secure hashing like bcrypt

    // Check user credentials
    $query = "SELECT * FROM users WHERE username='$username' && password='$password' ";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['enroll_no']=$user['enroll_no'];
        $_SESSION['course'] = $user['course'];
        $_SESSION['c_year'] = $user['c_year'];
        if( $_SESSION['role'] == 'client'){
        header('Location: dashboard.php');
        }
        else{
        header('Location: a_dashboard.php');   
        }
    } else {
        echo "<script>alert('Invalid username or password'); window.location.href='index.php';</script>";
        exit();
        echo "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SRMS Portal Login</title>
        <!--<link rel="stylesheet" href="stylee.css">-->
        <style>
        html{
            height:100%;
        }
        .header{
            position: absolute;
            top: 30px; /* Adjust the distance from the top of the page */
            text-align: center;
            color: white;
            font-size: 28px;
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
            justify-content: center;
            text-align:center;
            align-items: center;
            height: 100%;
            background: url('bg1.jpg') no-repeat center center fixed; /* Background image */
            background-repeat: no-repeat;
            background-size: cover; /* Ensure it covers the entire page */
        }
        .body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('bg1.jpg') no-repeat center center fixed; /* Background image */
            background-size: cover;
            filter: blur(10px); /* Blur effect */
            z-index: -1; /* Ensures it stays behind all content */
        }

        .login-box {
            width: 400px;
            padding: 40px;
            /*position: relative;*/
            background: hsla(0, 71%, 12%, 0.302); /* Transparent grey */
            border-radius: 10px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px); /* Adds a blur effect */
            color: white;
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
            color:  #0288d1;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #03a9f4;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0288d1;
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
            <h1>Student Result Management System</h1>
        </div>
        <div class="container">
            <div class="content">
                <div class="login-box">
                    <h2>Login</h2>
                    <form action="index.php" method="POST">
                        <div class="input-box">
                            <input type="text" name="username" required>
                            <label>Username</label>
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
                <p>Designed With❤️ By AGS & RRM </p>
            </footer>
        </div>
    </body>
</html>





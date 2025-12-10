<?php
session_start();
include('config.php'); // Include database connection
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Pragma: no-cache");

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input values
    $name=$_POST['username'];
    $password=$_POST['password'];
    $role="client";
    $enroll_no = $_POST['enroll_no'];
    $course = $_POST['course'];
    $branch=$_POST['branch'];
    $year=$_POST['year'];
  
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into `students` table
        $sql ="INSERT INTO users(username,password,role,enroll_no,course,c_year,branch) VALUES (?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $name,$password,$role,$enroll_no,$course,$year,$branch);
        $stmt->execute();
        $stmt->close();
        // Commit transaction
        $conn->commit();
        echo "Data inserted successfully!";
        header('Location: signup.php');
    } catch (Exception $e) {
        // Rollback transaction if any error occurs
        $conn->rollback();
        echo "Failed to insert data: " . $e->getMessage();
     }

        $conn->close();
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
            /*position: absolute;
            top: 30px; Adjust the distance from the top of the page
            text-align: center;*/
            color: white;
            font-size: 28px;
            top:30px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .body {
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('bg1.jpg') no-repeat center center fixed; /* Background image */
            background-size: cover;
            filter: blur(10px); /* Blur effect */
            z-index: -1; /* Ensures it stays behind all content */
        }

        .login-box {
            width: 600px;
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
            text-align: center;
            color: white;
            font-size: 14px;
            padding:5px;
            bottom:5px;
        }
        .container {
            max-width: 1200px;  /* Maximum width */
            margin: 0 auto;     /* Centers the container */
            padding: 20px;
            display:flex;
            flex-direction:column;
            align-items: center;
        }
        .content {
            flex: 2; /* Takes remaining space, pushes footer down */
            padding: 20px;
        }
        .signup{
            width: 25%;
            padding: 10px;
            background: #03a9f4;
            border: none;
            cursor: pointer;
            font-size: 12px;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
            
        }
        select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 100%;
            max-width: 600px;
        }

        </style>
        <script>
        const options = {
            Btech: [
                { value: 'Computer Science And Engineering', text: 'Computer Science And Engineering' },
                { value: 'Electronics & Computer Engineering', text: 'Electronics & Computer Engineering' },
                { value: 'Civil Engineering', text: 'Civil Engineering' }
            ],
            Bpharma: [
                { value: 'carrot', text: 'Carrot' },
                { value: 'broccoli', text: 'Broccoli' },
                { value: 'spinach', text: 'Spinach' }
            ]
        };

        function updateSecondList() {
            const firstList = document.getElementById("course");
            const secondList = document.getElementById("branch");
            const selectedValue = firstList.value;

            secondList.innerHTML = '<option value="" disabled selected>Choose your Branch</option>';

            if (options[selectedValue]) {
                options[selectedValue].forEach(option => {
                    const newOption = document.createElement("option");
                    newOption.value = option.value;
                    newOption.text = option.text;
                    secondList.appendChild(newOption);
                });
            }
        }
        </script>
    </head>
    <body class="body">
        <div class="header">
            <h1>Student Result Management System</h1>
        </div>
        <div class="container">
            <div class="content">
                <div class="login-box">
                    <h2>SIGN UP</h2>
                    <form action="signup.php" method="POST">
                        <div class="input-box">
                            <input type="text" name="username" required>
                            <label>Enter Your Name:</label>
                        </div>  
                        <div class="input-box">
                            <input type="password" name="password" required>
                            <label>Create Your Password:</label>
                        </div>
                        <div class="input-box">
                            <input type="enroll_no" name="enroll_no" required>
                            <label>Enrollment No:</label>
                        </div>
                        <div class="input-box">
                            <select name="course" id="course"onchange="updateSecondList()">
                                <option value="" disabled selected>Choose Your Course:</option>
                                <option value="Btech">B.Tech</option>
                                <option value="Bpharma">B.Pharma</option>
                            </select>
                            <br><br>
                            <select id="branch" name="branch"onchange="updateThirdList()">
                                <option value="" disabled selected>Choose Your Branch</option>
                            </select>
                            <br><br>
                            <select id="year" name="year">
                                <option value="" disabled selected>Choose Your Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="Final_Year">Final Year</option>
                            </select>
                        </div>

                        <button type="submit" name="submit">SIGN UP</button>
                    </form><br>
                    <h5>Are You Registered Before?  <button onclick="window.location.href='index.php'" class="signup"><h4>LOGIN</h4</button></h5>
                </div>
            </div>   
            <footer class="footer">
                <p>© 2024 SRMS Portal. All rights reserved.</p>
                <p>Designed With❤️ By AGS & RRM </p>
            </footer>
        </div>
    </body>
</html>





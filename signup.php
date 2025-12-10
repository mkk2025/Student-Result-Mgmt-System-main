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
            width: 600px;
            padding: 40px;
            /*position: relative;*/
            background: rgba(200, 16, 46, 0.85); /* IMATT red with transparency */
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
            color:  #FFD700; /* IMATT College Gold */
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #E63946; /* Lighter IMATT College Red */
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #D62839; /* Lighter darker red on hover */
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
            'Law': [
                { value: 'LLB Honours', text: 'LLB Honours' }
            ],
            'Business Administration': [
                { value: 'Business Administration', text: 'Business Administration' }
            ],
            'Computer Science': [
                { value: 'Computer Science', text: 'Computer Science' }
            ],
            'Banking and Finance': [
                { value: 'Banking and Finance', text: 'Banking and Finance' }
            ],
            'Nursing': [
                { value: 'Higher Diploma in Nursing', text: 'Higher Diploma in Nursing' }
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
            <img src="IMATT-LOGO-PNG.png" alt="IMATT College Logo" style="max-width: 150px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
            <h1>IMATT College - Student Result Management System</h1>
            <p style="font-size: 16px; margin-top: 10px;">International Management, Accounting, Technology, and Tourism</p>
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
                                <option value="Law">Law (LLB Honours)</option>
                                <option value="Business Administration">Business Administration</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Banking and Finance">Banking and Finance</option>
                                <option value="Nursing">Nursing</option>
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
                <p>Designed With❤️ By CORE BRIM TECH </p>
            </footer>
        </div>
    </body>
</html>





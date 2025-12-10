<?php
session_start();
include('config.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Pragma: no-cache");

$error_message = '';
$success_message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input values
    $name = trim($_POST['name'] ?? '');
    $id_number = trim($_POST['id_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'client'; // 'client' for student, 'admin' for lecturer
    $course = $_POST['course'] ?? '';
    $branch = $_POST['branch'] ?? '';
    $year = $_POST['year'] ?? '';
    
    // Validation
    if (empty($name) && empty($id_number)) {
        $error_message = "Please provide either Name or ID Number";
    } elseif (empty($password)) {
        $error_message = "Password is required";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        // Generate enrollment number if not provided
        $enroll_no = !empty($id_number) ? $id_number : strtoupper(substr($name, 0, 3)) . rand(100, 999);
        
        // Check if enrollment number already exists
        $check_query = "SELECT enroll_no FROM users WHERE enroll_no = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param('s', $enroll_no);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "ID Number already exists. Please use a different ID or leave blank to auto-generate.";
            $stmt->close();
        } else {
            $stmt->close();
            
            // Hash password using secure method
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Set default values for lecturers
            if ($role == 'admin') {
                $course = $course ?: 'All Courses';
                $year = $year ?: 'N/A';
                $branch = $branch ?: 'All Branches';
            }
            
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Insert into users table
                $sql = "INSERT INTO users(username, password, role, enroll_no, course, c_year, branch) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssss", $name, $hashed_password, $role, $enroll_no, $course, $year, $branch);
                $stmt->execute();
                $stmt->close();
                
                // If student, also insert into students table
                if ($role == 'client') {
                    // Generate branch code
                    $branch_code = '';
                    if ($course == 'Law') $branch_code = 'LAW001';
                    elseif ($course == 'Business Administration') $branch_code = 'BA001';
                    elseif ($course == 'Computer Science') $branch_code = 'CS001';
                    elseif ($course == 'Banking and Finance') $branch_code = 'BF001';
                    elseif ($course == 'Nursing') $branch_code = 'NUR001';
                    else $branch_code = 'GEN001';
                    
                    $sql = "INSERT INTO students(name, enroll_no, branch_code) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $name, $enroll_no, $branch_code);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Commit transaction
                $conn->commit();
                $success_message = "Registration successful! Your ID Number is: <strong>$enroll_no</strong>. Please login with this ID.";
                
                // Clear form
                $_POST = array();
            } catch (Exception $e) {
                // Rollback transaction if any error occurs
                $conn->rollback();
                $error_message = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMATT College - Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        html {
            height: 100%;
        }

        .body {
            text-align: center;
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
            filter: blur(8px);
            z-index: -1;
        }

        .body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }

        .header {
            color: white;
            font-size: 28px;
            top: 30px;
            margin-bottom: 20px;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .login-box h2 {
            color: #E63946;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .input-box {
            position: relative;
            margin-bottom: 25px;
        }

        .input-box input,
        .input-box select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .input-box input:focus,
        .input-box select:focus {
            outline: none;
            border-color: #E63946;
        }

        .input-box label {
            position: absolute;
            top: 12px;
            left: 15px;
            color: #666;
            font-size: 14px;
            pointer-events: none;
            transition: 0.3s;
        }

        .input-box input:focus ~ label,
        .input-box input:valid ~ label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #E63946;
            background: white;
            padding: 0 5px;
        }

        .role-selector {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            justify-content: center;
        }

        .role-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option label {
            cursor: pointer;
            display: block;
            font-weight: 500;
        }

        .role-option:hover {
            border-color: #E63946;
            background: #f8f9fa;
        }

        .role-option input[type="radio"]:checked + label {
            color: #E63946;
        }

        .role-option:has(input[type="radio"]:checked) {
            border-color: #E63946;
            background: #fff5f5;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #E63946;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: white;
            border-radius: 8px;
            transition: background-color 0.3s;
            font-weight: 500;
        }

        button:hover {
            background-color: #D62839;
        }

        .signup {
            width: 100%;
            padding: 10px;
            background: #03a9f4;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .signup:hover {
            background: #0288d1;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .info-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }

        .footer {
            bottom: 10px;
            color: white;
            font-size: 14px;
            text-align: center;
            padding: 5px;
            margin-top: 20px;
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

        function toggleFields() {
            const role = document.querySelector('input[name="role"]:checked').value;
            const courseField = document.getElementById('course').closest('.input-box');
            const branchField = document.getElementById('branch').closest('.input-box');
            const yearField = document.getElementById('year').closest('.input-box');
            
            if (role === 'admin') {
                courseField.style.display = 'none';
                branchField.style.display = 'none';
                yearField.style.display = 'none';
            } else {
                courseField.style.display = 'block';
                branchField.style.display = 'block';
                yearField.style.display = 'block';
            }
        }

        // Set default role to student
        window.onload = function() {
            document.getElementById('role_student').checked = true;
            toggleFields();
        };
    </script>
</head>
<body class="body">
    <div class="header">
        <img src="IMATT-LOGO-PNG.png" alt="IMATT College Logo" style="max-width: 150px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
        <h1>IMATT College - Registration Portal</h1>
        <p style="font-size: 16px; margin-top: 10px;">International Management, Accounting, Technology, and Tourism</p>
    </div>
    <div class="container">
        <div class="content">
            <div class="login-box">
                <h2>Create Account</h2>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form action="signup_enhanced.php" method="POST">
                    <!-- Role Selection -->
                    <div class="role-selector">
                        <div class="role-option">
                            <input type="radio" name="role" value="client" id="role_student" onchange="toggleFields()" checked>
                            <label for="role_student">👨‍🎓 Student</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" value="admin" id="role_lecturer" onchange="toggleFields()">
                            <label for="role_lecturer">👨‍🏫 Lecturer</label>
                        </div>
                    </div>

                    <!-- Name (Required) -->
                    <div class="input-box">
                        <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        <label>Full Name *</label>
                    </div>

                    <!-- ID Number (Optional) -->
                    <div class="input-box">
                        <input type="text" name="id_number" id="id_number" value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>">
                        <label>ID Number (Optional - Auto-generated if left blank)</label>
                        <div class="info-text">Leave blank to auto-generate an ID number</div>
                    </div>

                    <!-- Password -->
                    <div class="input-box">
                        <input type="password" name="password" id="password" required>
                        <label>Password * (Min. 6 characters)</label>
                    </div>

                    <!-- Confirm Password -->
                    <div class="input-box">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <label>Confirm Password *</label>
                    </div>

                    <!-- Course (Students only) -->
                    <div class="input-box" id="course_field">
                        <select name="course" id="course" onchange="updateSecondList()" required>
                            <option value="" disabled selected>Choose Your Course:</option>
                            <option value="Law">Law (LLB Honours)</option>
                            <option value="Business Administration">Business Administration</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Banking and Finance">Banking and Finance</option>
                            <option value="Nursing">Nursing</option>
                        </select>
                    </div>

                    <!-- Branch (Students only) -->
                    <div class="input-box" id="branch_field">
                        <select id="branch" name="branch" required>
                            <option value="" disabled selected>Choose Your Branch</option>
                        </select>
                    </div>

                    <!-- Year (Students only) -->
                    <div class="input-box" id="year_field">
                        <select id="year" name="year" required>
                            <option value="" disabled selected>Choose Your Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="Final Year">Final Year</option>
                        </select>
                    </div>

                    <button type="submit" name="submit">REGISTER</button>
                </form>

                <br>
                <h5>Already have an account? 
                    <button onclick="window.location.href='index.php'" class="signup">LOGIN</button>
                </h5>
            </div>
        </div>
        <footer class="footer">
            <?php include 'footer.php'; ?>
        </footer>
    </div>
</body>
</html>

<?php
if (isset($conn)) {
    $conn->close();
}
?>


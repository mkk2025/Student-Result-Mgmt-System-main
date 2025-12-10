<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
session_start();
include 'config.php';
include 'sidebar.php';
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
?>
<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input values
    $enroll_no = $_POST['enroll_no'];
    $name = $_POST['name'];
    $password=$_POST['password'];
    $role="client";
    $course=$_POST['course'];
    $year=$_POST['year'];
    $branch=$_POST['branch'];
    $branch_code=$_POST['branch_code'];
    $student_id=null;
    $subject_id=null;
  
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into `students` table
        $sql = "INSERT INTO students (name, enroll_no,branch_code) VALUES (?, ?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $enroll_no,$branch_code);
        $stmt->execute();
        $stmt->close();

        //insert into users table
        $sql ="INSERT INTO users(username,password,role,enroll_no,course,c_year,branch) VALUES (?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $name,$password,$role,$enroll_no,$course,$year,$branch);
        $stmt->execute();
        $stmt->close();
        // Commit transaction
        $conn->commit();
        echo "Data inserted successfully!";
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
        <title>Add Students</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            html,body{
                background: url('bg.jpg') no-repeat center center fixed;
                background-size: 100% 100%;
            }
            select{
                padding: 8px;
                border-radius: 4px;
                border: 1px solid #ccc;
                width: 100%;
                max-width: 600px;
                border: 2px solid rgba(255, 255, 255, 0.5);
                background-color: rgba(255, 255, 255, 0.7);
            }
            input[type="text"],input[type="password"]{
                border: 2px solid rgba(255, 255, 255, 0.5);
                background-color: rgba(255, 255, 255, 0.7);
            }
        </style>
        <script>
        const options = {
            Btech: [
                { value: 'CSE', text: 'Computer Science And Engineering' },
                { value: 'ECE', text: 'Electronics & Computer Engineering' },
                { value: 'Civil Engineering', text: 'Civil Engineering' }
            ],
            Bpharma: [
                { value: 'carrot', text: 'Carrot' },
                { value: 'broccoli', text: 'Broccoli' },
                { value: 'spinach', text: 'Spinach' }
            ],
            CSE:[
                {value:'11845',text:'11845'}
            ],
            ECE:[
                {value:'11844',text:'11844'}
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
        function updateThirdList() {
            const firstList = document.getElementById("branch");
            const secondList = document.getElementById("branch_code");
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
    <body>
        <div class="container" style="margin-left:280px;">
            <div class="content" style="margin-left:50px;">
                <h2>Add New Student</h2>
                <form action="a_students.php" method="POST">
                    <div class="form-group">
                        <label for="enroll_no">Enrollment Number</label>
                        <input type="text" class="form-control" id="enroll_no" name="enroll_no" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Student Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div> 

                    <div class="form-group">
                        <label for="username">Enter Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Enter Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <select name="course" id="course"onchange="updateSecondList()">
                            <option value="" disabled selected>Choose Your Course:</option>
                            <option value="Btech">B.Tech</option>
                            <option value="Bpharma">B.Pharma</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select id="branch" name="branch"onchange="updateThirdList()">
                            <option value="" disabled selected>Choose Your Branch</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select id="branch_code" name="branch_code">
                            <option value="" disabled selected>Choose Your Branch Code</option>
                        </select>
                    </div>


                    <div class="form-group">
                        <select id="year" name="year">
                            <option value="" disabled selected>Choose Your Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="Final_Year">Final Year</option>
                        </select>
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

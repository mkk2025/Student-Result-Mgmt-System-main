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
    $subject_code=$_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $total_marks = $_POST['total_marks'];
    $obtained_marks = $_POST['obtained_marks'];
    $grade = $_POST['grade'];
    $student_id=null;
    $subject_id=null;
  
    // Begin transaction
    $conn->begin_transaction();

    try {
        $stud_id = "SELECT id FROM `students` WHERE enroll_no=?";
        $stmt = $conn->prepare($stud_id);
        $stmt->bind_param("i", $enroll_no);
        $stmt->execute();
        $stmt->bind_result($student_id);
        $stmt->fetch();
        $stmt->close();
        
        //retriving the subject id of the subject
        $sub_id = "SELECT `id` FROM `subjects` WHERE subject_code=?";
        $stmt = $conn->prepare($sub_id);
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $stmt->bind_result($subject_id);
        $stmt->fetch();
        $stmt->close();
        
        //inserting the marks of student based on thier student_id & subject_id
        $sql = "INSERT INTO marks (student_id,subject_id,obtained_marks,total_marks,grade) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiis", $student_id,$subject_id, $obtained_marks,$total_marks,$grade);
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
        <title>Insert Student Results</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="style.css">
        <style>
            html,body{
                background: url('bg.jpg') no-repeat center center fixed;
                background-size: 100% 100%;
            }
            input[type="text"],input[type="password"],input[type="number"]{
                border: 2px solid rgba(255, 255, 255, 0.5);
                background-color: rgba(255, 255, 255, 0.7);
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
        </style>
        <script>
            const options = {
                PP:[
                    { value: 'subject_code_PP', text: 'BTECPC401' }
                ],
                DBMS: [
                    { value: 'subject_code', text: 'BTECPC402' }
                ],
                BHR:[
                    { value: 'subject_code', text: 'BTHM403' }
                ],
                PTRP:[
                    { value: 'subject_code', text: 'BTBS404' }
                ],
                M_AP:[
                    { value: 'subject_code', text: 'BTECPE405A' }
                ],
                PP_LAB:[
                    { value: 'subject_code', text: 'BTECPL406' }
                ],
                SEM_II:[
                    { value: 'subject_code', text: 'BTECS407' }
                ]
            };

            function updateSecondList() {
                const firstList = document.getElementById("subject_name");
                const secondList = document.getElementById("subject_code");
                const selectedValue = firstList.value;

                secondList.innerHTML = '<option value="" disabled selected>Choose Subject Code</option>';

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
                <h2>Insert Student Results</h2>
                <form action="a_results.php" method="POST">
                    <div class="form-group">
                        <label for="enroll_no">Enrollment Number:</label>
                        <input type="text" class="form-control" id="enroll_no" name="enroll_no" required>
                    </div>
                    <label for="subject_name">Subject Name:</label>

                    <div class="form-group">
                        <select name="subject_name" id="subject_name"onchange="updateSecondList()">
                            <option value="" disabled selected>Choose A Subject:</option>
                            <option value="PP">Python Programming</option>
                            <option value="DBMS">Database Management System</option>
                            <option value="BHR">Basic Human Rights</option>
                            <option value="PTRP">Probability theory and random processes</option>
                            <option value="M_AP">Microcontroller & Advanced Processor</option>
                            <option value="PP_LAB">Python Programming Lab & Database Management System Lab</option>
                            <option value="SEM_II">Seminar II</option>
                        </select>
                    </div>
                    <label for="subject_code">Subject Code:</label>

                    <div class="form-group">
                        <select id="subject_code" name="subject_code">
                            <option value="" disabled selected>Choose Your Subject Code</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="total_marks">Total Marks:</label>
                        <input type="number" class="form-control" id="total_marks" name="total_marks" required>
                    </div>

                    <div class="form-group">
                        <label for="obtained_marks">Obtained Marks:</label>
                        <input type="number" class="form-control" id="obtained_marks" name="obtained_marks" required>
                    </div>

                    <div class="form-group">
                        <label for="grade">Grade:</label>
                        <input type="text" class="form-control" id="grade" name="grade" required>
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

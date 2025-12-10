<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
session_start();
include 'config.php';
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
// Get the student's enrollment number from session
$enroll_no = $_SESSION['enroll_no'];

// Fetch student details from the database
$query = "SELECT students.name,students.enroll_no,students.branch_code,subjects.subject_code,subjects.subject_name,marks.total_marks,marks.obtained_marks,marks.grade
          FROM students
          JOIN marks ON students.id = marks.student_id
          JOIN subjects ON marks.subject_id = subjects.id
          WHERE students.enroll_no =? ORDER BY marks.subject_id ASC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $enroll_no);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Check if data exists for the student
if ($result->num_rows >= 0) {
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <style>
        table {
            width:100px;
            border-collapse: collapse;
            margin-left:300px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 10px;
        }
        th, td {
            text-align: center;
        }
        th {
            background-color:#4682B4;
            color:white;
        }
        #marksheet {
            overflow: visible; /* Allow the entire content to be captured */
            width: 1200px;
            text-align:center;
        }
        #downloadBtn{
            width:100px;
            height:50px;
            color:green;
            margin-left:20px;
        }
        </style>
        <title>Student Results</title>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div id="marksheet">
                    <br>
                    <h1 style="text-shadow: 2px 2px 5px #888888;color:#C71585;">Dr.Babasaheb Ambedkar Technological University,Lonere</h1><br>
                    <h2 style="text-shadow: 2px 2px 5px #888888;color:#BC8F8F;">Summer 2024 Result Marksheet</h2><br>
                    <h3>Name: <?php  foreach ($rows as $row) {echo htmlspecialchars($row['name'] ?? '');break;}/*echo htmlspecialchars($student_name??'');*/ ?> | PRN:<?php echo htmlspecialchars($_SESSION['enroll_no']); ?></h3><br>
                    <table>
                        <tr>
                            <th class="msheethead">Branch Code</th>
                            <th class="msheethead">Subject Code</th>
                            <th class="msheethead">Subjects</th>
                            <th class="msheethead">Total Marks</th>
                            <th class="msheethead">Obtained Marks</th>
                            <th class="msheethead">Grade</th>
                        </tr>
                        <?php
                        foreach ($rows as $row){
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['branch_code']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['subject_code']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['total_marks']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['obtained_marks']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['grade']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                    <br>

                </div>
                <br>
                <button id="downloadBtn">Download Marksheet</button>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
                <script>
                    document.getElementById('downloadBtn').addEventListener('click', function() {
                        html2canvas(document.getElementById('marksheet'), {
                            scale: 3,
                            useCORS: true
                        }).then(function(canvas){
                            let link = document.createElement('a');
                            link.href = canvas.toDataURL('image/png');
                            link.download = 'marksheet.png';
                            link.click();
                        });
                    });
                </script>
            </div>
            <footer class="footer">
                <?php include 'footer.php'; ?>
            </footer>
        </div>
    </body>
</html>

<?php
} else {
    echo "<p style='text-align: center;margin-left:300px;'>Results Will Be Displayed Soon.</p>";
}

$stmt->close();
$conn->close();
?>

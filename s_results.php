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

// Get selected semester and academic year from GET parameters (default to current if not set)
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$selected_academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';

// Fetch available semesters and academic years for this student
$semesters_query = "SELECT DISTINCT marks.semester, marks.academic_year 
                    FROM marks 
                    JOIN students ON marks.student_id = students.id 
                    WHERE students.enroll_no = ? 
                    ORDER BY marks.academic_year DESC, marks.semester DESC";
$stmt_sem = $conn->prepare($semesters_query);
$stmt_sem->bind_param('s', $enroll_no);
$stmt_sem->execute();
$semesters_result = $stmt_sem->get_result();
$available_semesters = $semesters_result->fetch_all(MYSQLI_ASSOC);
$stmt_sem->close();

// If no semester selected, use the most recent one
if (empty($selected_semester) && !empty($available_semesters)) {
    $selected_semester = $available_semesters[0]['semester'];
    $selected_academic_year = $available_semesters[0]['academic_year'];
}

// Fetch student details from the database with semester filter
$query = "SELECT students.name,students.enroll_no,students.branch_code,subjects.subject_code,subjects.subject_name,marks.total_marks,marks.obtained_marks,marks.grade,marks.semester,marks.academic_year
          FROM students
          JOIN marks ON students.id = marks.student_id
          JOIN subjects ON marks.subject_id = subjects.id
          WHERE students.enroll_no = ?";
          
// Add semester and academic year filter if selected
if (!empty($selected_semester) && !empty($selected_academic_year)) {
    $query .= " AND marks.semester = ? AND marks.academic_year = ?";
    $query .= " ORDER BY marks.subject_id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $enroll_no, $selected_semester, $selected_academic_year);
} else {
    $query .= " ORDER BY marks.academic_year DESC, marks.semester DESC, marks.subject_id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $enroll_no);
}

$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Calculate GPA for selected semester
$gpa = 0;
$total_points = 0;
$total_credits = 0;
if (!empty($rows)) {
    foreach ($rows as $row) {
        // Skip records with zero or null total_marks to prevent division by zero
        if (empty($row['total_marks']) || $row['total_marks'] == 0) {
            continue;
        }
        
        $percentage = ($row['obtained_marks'] / $row['total_marks']) * 100;
        // Convert percentage to GPA (4.0 scale)
        if ($percentage >= 90) $points = 4.0;
        elseif ($percentage >= 80) $points = 3.5;
        elseif ($percentage >= 70) $points = 3.0;
        elseif ($percentage >= 60) $points = 2.5;
        elseif ($percentage >= 50) $points = 2.0;
        else $points = 1.0;
        
        $credits = 3; // Assuming 3 credits per subject (can be made dynamic)
        $total_points += ($points * $credits);
        $total_credits += $credits;
    }
    if ($total_credits > 0) {
        $gpa = $total_points / $total_credits;
    }
}

// Check if data exists for the student
if ($result->num_rows > 0) {
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
            background-color:#E63946; /* Lighter IMATT College Red */
            color:white;
        }
        #marksheet {
            overflow: visible; /* Allow the entire content to be captured */
            width: 1200px;
            text-align:center;
        }
        #downloadBtn{
            width:150px;
            height:50px;
            background-color:#E63946; /* Lighter IMATT College Red */
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-size:16px;
            margin-left:20px;
            transition: background-color 0.3s;
        }
        #downloadBtn:hover {
            background-color:#D62839; /* Lighter darker red on hover */
        }
        .semester-selector {
            margin: 20px 0;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 5px;
            margin-left: 300px;
            max-width: 600px;
        }
        .semester-selector select, .semester-selector button {
            padding: 8px;
            margin: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .semester-selector button {
            background-color: #C8102E; /* IMATT College Red */
            color: white;
            border: none;
            cursor: pointer;
            padding: 8px 20px;
            border-radius: 4px;
        }
        .semester-selector button:hover {
            background-color: #A01D26; /* Darker red on hover */
        }
        </style>
        <title>Student Results</title>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <!-- Semester Selector -->
                <div class="semester-selector">
                    <h3>Select Semester to View Results</h3>
                    <form method="GET" action="s_results.php">
                        <label for="academic_year">Academic Year:</label>
                        <select name="academic_year" id="academic_year">
                            <option value="">All Years</option>
                            <?php 
                            $unique_years = array_unique(array_column($available_semesters, 'academic_year'));
                            foreach ($unique_years as $year): 
                            ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($selected_academic_year == $year) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <label for="semester">Semester:</label>
                        <select name="semester" id="semester">
                            <option value="">All Semesters</option>
                            <?php foreach ($available_semesters as $sem): ?>
                                <option value="<?php echo htmlspecialchars($sem['semester']); ?>" 
                                        data-year="<?php echo htmlspecialchars($sem['academic_year']); ?>"
                                        <?php echo ($selected_semester == $sem['semester'] && $selected_academic_year == $sem['academic_year']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sem['academic_year'] . ' - ' . $sem['semester']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit">View Results</button>
                    </form>
                </div>
                
                <div id="marksheet">
                    <br>
                    <h1 style="text-shadow: 2px 2px 5px #888888;color:#E63946;">IMATT COLLEGE</h1>
                    <h2 style="text-shadow: 2px 2px 5px #888888;color:#BC8F8F;font-size:18px;">International Management, Accounting, Technology, and Tourism</h2>
                    <p style="color:#666;font-size:14px;">14 Off Hennessy Street, Kingtom, Freetown, Sierra Leone</p>
                    <p style="color:#666;font-size:14px;">Tel: +232 78 900082 | Email: info@imatcollege.com</p>
                    <h2 style="text-shadow: 2px 2px 5px #888888;color:#BC8F8F;">Academic Result Marksheet</h2><br>
                    <h3>Name: <?php  foreach ($rows as $row) {echo htmlspecialchars($row['name'] ?? '');break;}/*echo htmlspecialchars($student_name??'');*/ ?> | Student ID: <?php echo htmlspecialchars($_SESSION['enroll_no']); ?></h3>
                    <?php if (!empty($selected_semester) && !empty($selected_academic_year)): ?>
                        <h3>Academic Year: <?php echo htmlspecialchars($selected_academic_year); ?> | Semester: <?php echo htmlspecialchars($selected_semester); ?></h3>
                        <?php if ($gpa > 0): ?>
                        <div style="background: linear-gradient(135deg, #E63946 0%, #D62839 100%); color: white; padding: 15px; border-radius: 10px; margin: 15px auto; max-width: 300px;">
                            <h3 style="color: white; margin: 0;">Semester GPA: <strong><?php echo number_format($gpa, 2); ?></strong> / 4.0</h3>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <br>
                    <table>
                        <tr>
                            <th class="msheethead">Subject Code</th>
                            <th class="msheethead">Subject Name</th>
                            <th class="msheethead">Total Marks</th>
                            <th class="msheethead">Obtained Marks</th>
                            <th class="msheethead">Grade</th>
                            <th class="msheethead">Semester</th>
                        </tr>
                        <?php
                        foreach ($rows as $row){
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['subject_code']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['total_marks']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['obtained_marks']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['grade']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['semester'] ?? 'N/A') . "</td>";
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

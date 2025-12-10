<?php
session_start();
include 'config.php';
include 'sidebar.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

$message = '';
$message_type = '';

// Handle delete
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $mark_id = intval($_POST['mark_id']);
    $stmt = $conn->prepare("DELETE FROM marks WHERE id = ?");
    $stmt->bind_param("i", $mark_id);
    if ($stmt->execute()) {
        $message = "Grade deleted successfully!";
        $message_type = 'success';
    } else {
        $message = "Error deleting grade";
        $message_type = 'error';
    }
    $stmt->close();
}

// Handle edit
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $mark_id = intval($_POST['mark_id']);
    $obtained_marks = intval($_POST['obtained_marks']);
    $total_marks = intval($_POST['total_marks']);
    
    // Auto-calculate grade
    $percentage = ($obtained_marks / $total_marks) * 100;
    if ($percentage >= 90) $grade = 'A+';
    elseif ($percentage >= 85) $grade = 'A';
    elseif ($percentage >= 80) $grade = 'A-';
    elseif ($percentage >= 75) $grade = 'B+';
    elseif ($percentage >= 70) $grade = 'B';
    elseif ($percentage >= 65) $grade = 'B-';
    elseif ($percentage >= 60) $grade = 'C+';
    elseif ($percentage >= 55) $grade = 'C';
    elseif ($percentage >= 50) $grade = 'C-';
    elseif ($percentage >= 45) $grade = 'D';
    else $grade = 'F';
    
    $stmt = $conn->prepare("UPDATE marks SET obtained_marks = ?, total_marks = ?, grade = ? WHERE id = ?");
    $stmt->bind_param("iisi", $obtained_marks, $total_marks, $grade, $mark_id);
    if ($stmt->execute()) {
        $message = "Grade updated successfully!";
        $message_type = 'success';
    } else {
        $message = "Error updating grade";
        $message_type = 'error';
    }
    $stmt->close();
}

// Filters
$student_filter = isset($_GET['student']) ? trim($_GET['student']) : '';
$semester_filter = isset($_GET['semester']) ? $_GET['semester'] : '';
$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : '';

// Build query
$query = "SELECT m.*, s.name as student_name, s.enroll_no, sub.subject_code, sub.subject_name
          FROM marks m
          JOIN students s ON m.student_id = s.id
          JOIN subjects sub ON m.subject_id = sub.id
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($student_filter)) {
    $query .= " AND (s.enroll_no = ? OR s.name LIKE ?)";
    $params[] = $student_filter;
    $params[] = "%$student_filter%";
    $types .= "ss";
}

if (!empty($semester_filter)) {
    $query .= " AND m.semester = ?";
    $params[] = $semester_filter;
    $types .= "s";
}

if (!empty($subject_filter)) {
    $query .= " AND m.subject_id = ?";
    $params[] = intval($subject_filter);
    $types .= "i";
}

$query .= " ORDER BY m.academic_year DESC, m.semester DESC, s.name ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$grades = $stmt->get_result();

// Get filter options
$semesters_result = $conn->query("SELECT DISTINCT semester FROM marks ORDER BY semester");
$subjects_result = $conn->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View/Edit Grades</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .page-container {
            max-width: 1200px;
            margin: 20px auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-header h2 {
            color: #E63946;
            margin: 0;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #E63946 0%, #D62839 100%);
            color: white;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-danger { background: #dc3545; color: white; }
        .btn-edit { background: #17a2b8; color: white; }
        .btn-success { background: #28a745; color: white; }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 13px;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #E63946;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #E63946;
            color: white;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .grade-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .grade-A { background: #28a745; color: white; }
        .grade-B { background: #17a2b8; color: white; }
        .grade-C { background: #ffc107; color: #333; }
        .grade-D { background: #fd7e14; color: white; }
        .grade-F { background: #dc3545; color: white; }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        
        .actions { display: flex; gap: 5px; }
        
        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            padding: 15px 25px;
            background: linear-gradient(135deg, #E63946 0%, #D62839 100%);
            color: white;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-item .number { font-size: 24px; font-weight: bold; }
        .stat-item .label { font-size: 12px; opacity: 0.9; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal.show { display: flex; }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 400px;
            width: 90%;
        }
        
        .modal-content h3 {
            color: #E63946;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="page-container">
                <div class="page-header">
                    <h2>📊 View/Edit Grades</h2>
                    <a href="a_results.php" class="btn btn-primary">+ Upload New Grade</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="number"><?php echo $grades->num_rows; ?></div>
                        <div class="label">Total Grades</div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card">
                    <form method="GET" action="a_view_grades.php">
                        <div class="filters">
                            <div class="filter-group" style="flex: 2;">
                                <label>Student (ID or Name)</label>
                                <input type="text" name="student" placeholder="Search student..." 
                                       value="<?php echo htmlspecialchars($student_filter); ?>">
                            </div>
                            <div class="filter-group">
                                <label>Semester</label>
                                <select name="semester">
                                    <option value="">All Semesters</option>
                                    <?php while ($sem = $semesters_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($sem['semester']); ?>"
                                                <?php echo $semester_filter == $sem['semester'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sem['semester']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Subject</label>
                                <select name="subject">
                                    <option value="">All Subjects</option>
                                    <?php while ($sub = $subjects_result->fetch_assoc()): ?>
                                        <option value="<?php echo $sub['id']; ?>"
                                                <?php echo $subject_filter == $sub['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sub['subject_code'] . ' - ' . $sub['subject_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="a_view_grades.php" class="btn" style="background: #6c757d; color: white;">Clear</a>
                        </div>
                    </form>
                </div>
                
                <!-- Grades Table -->
                <div class="card">
                    <?php if ($grades->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Semester</th>
                                <th>Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($grade = $grades->fetch_assoc()): 
                                $grade_class = 'grade-' . substr($grade['grade'], 0, 1);
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($grade['student_name']); ?></strong><br>
                                    <small style="color: #666;">ID: <?php echo htmlspecialchars($grade['enroll_no']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($grade['subject_name']); ?><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($grade['subject_code']); ?></small>
                                </td>
                                <td><?php echo $grade['obtained_marks']; ?>/<?php echo $grade['total_marks']; ?></td>
                                <td><span class="grade-badge <?php echo $grade_class; ?>"><?php echo htmlspecialchars($grade['grade']); ?></span></td>
                                <td><?php echo htmlspecialchars($grade['semester']); ?></td>
                                <td><?php echo htmlspecialchars($grade['academic_year']); ?></td>
                                <td class="actions">
                                    <button class="btn btn-edit btn-sm" onclick="editGrade(<?php echo $grade['id']; ?>, <?php echo $grade['obtained_marks']; ?>, <?php echo $grade['total_marks']; ?>)">Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this grade?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="mark_id" value="<?php echo $grade['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <h3>No grades found</h3>
                        <p>Try adjusting your filters or <a href="a_results.php">upload a new grade</a>.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <footer class="footer">
            <?php include 'footer.php';?>
        </footer>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h3>Edit Grade</h3>
            <form method="POST" action="a_view_grades.php<?php echo !empty($student_filter) ? '?student=' . urlencode($student_filter) : ''; ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="mark_id" id="edit_mark_id">
                <div class="form-group">
                    <label>Total Marks</label>
                    <input type="number" name="total_marks" id="edit_total_marks" required>
                </div>
                <div class="form-group">
                    <label>Obtained Marks</label>
                    <input type="number" name="obtained_marks" id="edit_obtained_marks" required>
                </div>
                <p style="color: #666; font-size: 12px; margin-bottom: 15px;">Grade will be auto-calculated based on marks.</p>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn" onclick="closeModal()" style="background: #6c757d; color: white;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editGrade(id, obtained, total) {
            document.getElementById('edit_mark_id').value = id;
            document.getElementById('edit_obtained_marks').value = obtained;
            document.getElementById('edit_total_marks').value = total;
            document.getElementById('editModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>


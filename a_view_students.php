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
    $enroll_no = $_POST['enroll_no'];
    
    $conn->begin_transaction();
    try {
        // Get student ID first
        $stmt = $conn->prepare("SELECT id FROM students WHERE enroll_no = ?");
        $stmt->bind_param("s", $enroll_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
        
        if ($student) {
            // Delete marks first
            $stmt = $conn->prepare("DELETE FROM marks WHERE student_id = ?");
            $stmt->bind_param("i", $student['id']);
            $stmt->execute();
            $stmt->close();
            
            // Delete from students
            $stmt = $conn->prepare("DELETE FROM students WHERE enroll_no = ?");
            $stmt->bind_param("s", $enroll_no);
            $stmt->execute();
            $stmt->close();
        }
        
        // Delete from users
        $stmt = $conn->prepare("DELETE FROM users WHERE enroll_no = ?");
        $stmt->bind_param("s", $enroll_no);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        $message = "Student deleted successfully!";
        $message_type = 'success';
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Search/Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';

// Build query
$query = "SELECT u.*, s.branch_code, 
          (SELECT COUNT(*) FROM marks m JOIN students st ON m.student_id = st.id WHERE st.enroll_no = u.enroll_no) as grade_count
          FROM users u 
          LEFT JOIN students s ON u.enroll_no = s.enroll_no 
          WHERE u.role = 'client'";

$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (u.username LIKE ? OR u.enroll_no LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($course_filter)) {
    $query .= " AND u.course = ?";
    $params[] = $course_filter;
    $types .= "s";
}

if (!empty($year_filter)) {
    $query .= " AND u.c_year = ?";
    $params[] = $year_filter;
    $types .= "s";
}

$query .= " ORDER BY u.username ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();

// Get unique courses and years for filters
$courses_result = $conn->query("SELECT DISTINCT course FROM users WHERE role = 'client' AND course IS NOT NULL ORDER BY course");
$years_result = $conn->query("SELECT DISTINCT c_year FROM users WHERE role = 'client' AND c_year IS NOT NULL ORDER BY c_year");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students</title>
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
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
        }
        
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
            color: #333;
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
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .badge-primary {
            background: #E63946;
            color: white;
        }
        
        .badge-secondary {
            background: #e9ecef;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
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
        
        .stat-item .number {
            font-size: 24px;
            font-weight: bold;
        }
        
        .stat-item .label {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .empty-state h3 {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="page-container">
                <div class="page-header">
                    <h2>👥 View All Students</h2>
                    <a href="a_students.php" class="btn btn-primary">+ Add New Student</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="number"><?php echo $students->num_rows; ?></div>
                        <div class="label">Total Students</div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card">
                    <form method="GET" action="a_view_students.php">
                        <div class="filters">
                            <div class="filter-group" style="flex: 2;">
                                <label>Search</label>
                                <input type="text" name="search" placeholder="Search by name or ID..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="filter-group">
                                <label>Course</label>
                                <select name="course">
                                    <option value="">All Courses</option>
                                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($course['course']); ?>"
                                                <?php echo $course_filter == $course['course'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['course']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Year</label>
                                <select name="year">
                                    <option value="">All Years</option>
                                    <?php while ($year = $years_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($year['c_year']); ?>"
                                                <?php echo $year_filter == $year['c_year'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($year['c_year']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="a_view_students.php" class="btn" style="background: #6c757d; color: white;">Clear</a>
                        </div>
                    </form>
                </div>
                
                <!-- Students Table -->
                <div class="card">
                    <?php if ($students->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Branch Code</th>
                                <th>Grades</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['enroll_no']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['course'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['c_year'] ?? '-'); ?></td>
                                <td><span class="badge badge-secondary"><?php echo htmlspecialchars($student['branch_code'] ?? '-'); ?></span></td>
                                <td><span class="badge badge-primary"><?php echo $student['grade_count']; ?></span></td>
                                <td class="actions">
                                    <a href="a_view_grades.php?student=<?php echo urlencode($student['enroll_no']); ?>" 
                                       class="btn btn-info">View Grades</a>
                                    <form method="POST" style="display:inline;" 
                                          onsubmit="return confirm('Delete student <?php echo htmlspecialchars($student['username']); ?>? This will also delete all their grades!')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="enroll_no" value="<?php echo htmlspecialchars($student['enroll_no']); ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <h3>No students found</h3>
                        <p>Try adjusting your filters or <a href="a_students.php">add a new student</a>.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <footer class="footer">
            <?php include 'footer.php';?>
        </footer>
    </div>
</body>
</html>


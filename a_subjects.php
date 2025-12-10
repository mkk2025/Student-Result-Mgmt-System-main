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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $subject_code = trim($_POST['subject_code']);
                $subject_name = trim($_POST['subject_name']);
                
                if (empty($subject_code) || empty($subject_name)) {
                    $message = "All fields are required";
                    $message_type = 'error';
                } else {
                    // Check if subject code exists
                    $check = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ?");
                    $check->bind_param("s", $subject_code);
                    $check->execute();
                    if ($check->get_result()->num_rows > 0) {
                        $message = "Subject code already exists";
                        $message_type = 'error';
                    } else {
                        $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)");
                        $stmt->bind_param("ss", $subject_code, $subject_name);
                        if ($stmt->execute()) {
                            $message = "Subject added successfully!";
                            $message_type = 'success';
                        } else {
                            $message = "Error adding subject";
                            $message_type = 'error';
                        }
                        $stmt->close();
                    }
                    $check->close();
                }
                break;
                
            case 'delete':
                $subject_id = intval($_POST['subject_id']);
                
                // Check if subject has marks
                $check = $conn->prepare("SELECT COUNT(*) as count FROM marks WHERE subject_id = ?");
                $check->bind_param("i", $subject_id);
                $check->execute();
                $count = $check->get_result()->fetch_assoc()['count'];
                $check->close();
                
                if ($count > 0) {
                    $message = "Cannot delete subject with existing grades. Delete grades first.";
                    $message_type = 'error';
                } else {
                    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
                    $stmt->bind_param("i", $subject_id);
                    if ($stmt->execute()) {
                        $message = "Subject deleted successfully!";
                        $message_type = 'success';
                    } else {
                        $message = "Error deleting subject";
                        $message_type = 'error';
                    }
                    $stmt->close();
                }
                break;
                
            case 'edit':
                $subject_id = intval($_POST['subject_id']);
                $subject_code = trim($_POST['subject_code']);
                $subject_name = trim($_POST['subject_name']);
                
                $stmt = $conn->prepare("UPDATE subjects SET subject_code = ?, subject_name = ? WHERE id = ?");
                $stmt->bind_param("ssi", $subject_code, $subject_name, $subject_id);
                if ($stmt->execute()) {
                    $message = "Subject updated successfully!";
                    $message_type = 'success';
                } else {
                    $message = "Error updating subject";
                    $message_type = 'error';
                }
                $stmt->close();
                break;
        }
    }
}

// Fetch all subjects
$subjects = $conn->query("SELECT s.*, COUNT(m.id) as grade_count FROM subjects s LEFT JOIN marks m ON s.id = m.subject_id GROUP BY s.id ORDER BY s.subject_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .page-container {
            max-width: 1000px;
            margin: 20px auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
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
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background: #138496;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .card h3 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #E63946;
            padding-bottom: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 2fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group input:focus {
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
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
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
            gap: 10px;
        }
        
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
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-content h3 {
            color: #E63946;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="page-container">
                <div class="page-header">
                    <h2>📚 Manage Subjects</h2>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add Subject Form -->
                <div class="card">
                    <h3>Add New Subject</h3>
                    <form method="POST" action="a_subjects.php">
                        <input type="hidden" name="action" value="add">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Subject Code</label>
                                <input type="text" name="subject_code" placeholder="e.g., CS101" required>
                            </div>
                            <div class="form-group">
                                <label>Subject Name</label>
                                <input type="text" name="subject_name" placeholder="e.g., Introduction to Programming" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Subject</button>
                        </div>
                    </form>
                </div>
                
                <!-- Subjects List -->
                <div class="card">
                    <h3>All Subjects (<?php echo $subjects->num_rows; ?>)</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Subject Name</th>
                                <th>Grades</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($subject = $subjects->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                <td><span class="badge"><?php echo $subject['grade_count']; ?> grades</span></td>
                                <td class="actions">
                                    <button class="btn btn-edit" onclick="editSubject(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['subject_code']); ?>', '<?php echo htmlspecialchars(addslashes($subject['subject_name'])); ?>')">Edit</button>
                                    <?php if ($subject['grade_count'] == 0): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this subject?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
            <h3>Edit Subject</h3>
            <form method="POST" action="a_subjects.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="subject_id" id="edit_subject_id">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Subject Code</label>
                    <input type="text" name="subject_code" id="edit_subject_code" required>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" id="edit_subject_name" required>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn" onclick="closeModal()" style="background: #6c757d; color: white;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editSubject(id, code, name) {
            document.getElementById('edit_subject_id').value = id;
            document.getElementById('edit_subject_code').value = code;
            document.getElementById('edit_subject_name').value = name;
            document.getElementById('editModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        // Close modal on outside click
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>


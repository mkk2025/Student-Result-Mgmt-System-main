<?php
// Sidebar component - outputs only sidebar markup
// Must be included AFTER session_start() and authentication checks
// The including page is responsible for HTML document structure

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Get the current page name
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar Styles -->
<style>
    /* Mobile Menu Button */
    .menu-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1001;
        background: #E63946;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 18px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .menu-toggle:hover {
        background: #D62839;
    }
    
    /* Sidebar Overlay for Mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }
    
    .sidebar-overlay.show {
        display: block;
    }
    
    /* Enhanced Sidebar */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background: linear-gradient(180deg, #E63946 0%, #D62839 100%);
        padding-top: 20px;
        z-index: 1000;
        overflow-y: auto;
        transition: transform 0.3s ease;
    }
    
    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sidebar ul li {
        margin: 0;
    }
    
    .sidebar ul li a {
        display: block;
        padding: 15px 25px;
        color: rgba(255,255,255,0.9);
        text-decoration: none;
        font-size: 15px;
        transition: all 0.3s;
        border-left: 4px solid transparent;
    }
    
    .sidebar ul li a:hover {
        background: rgba(255,255,255,0.1);
        border-left-color: #FFD700;
        color: white;
    }
    
    .sidebar ul li a.active {
        background: rgba(255,255,255,0.15);
        border-left-color: #FFD700;
        color: white;
        font-weight: 500;
    }
    
    .sidebar-section {
        padding: 10px 25px;
        font-size: 11px;
        text-transform: uppercase;
        color: rgba(255,255,255,0.5);
        letter-spacing: 1px;
        margin-top: 15px;
    }
    
    .sidebar-logo {
        text-align: center;
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 10px;
    }
    
    .sidebar-logo img {
        max-width: 120px;
        border-radius: 10px;
    }
    
    .sidebar-user {
        padding: 15px 25px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 10px;
    }
    
    .sidebar-user .name {
        color: white;
        font-weight: 600;
        font-size: 14px;
    }
    
    .sidebar-user .role {
        color: rgba(255,255,255,0.7);
        font-size: 12px;
        margin-top: 3px;
    }
    
    .logout-btn {
        margin: 20px;
        display: block;
        padding: 12px 20px;
        background: rgba(255,255,255,0.1);
        color: white;
        text-decoration: none;
        text-align: center;
        border-radius: 8px;
        transition: background 0.3s;
    }
    
    .logout-btn:hover {
        background: rgba(255,255,255,0.2);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .menu-toggle {
            display: block;
        }
        
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.open {
            transform: translateX(0);
        }
        
        .container {
            margin-left: 0 !important;
        }
        
        .content {
            padding: 70px 15px 20px 15px !important;
        }
    }
</style>

<!-- Mobile Menu Toggle -->
<button class="menu-toggle" onclick="toggleSidebar()">☰ Menu</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="sidebar">
    <div class="sidebar-logo">
        <img src="IMATT-LOGO-PNG.png" alt="IMATT College Logo">
    </div>
    
    <div class="sidebar-user">
        <div class="name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div class="role"><?php echo $_SESSION['role'] == 'admin' ? '👨‍🏫 Lecturer' : '👨‍🎓 Student'; ?></div>
    </div>
    
    <ul>
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <div class="sidebar-section">Dashboard</div>
            <li><a href="a_dashboard.php" class="<?php if ($currentPage == 'a_dashboard.php') echo 'active'; ?>">📊 Dashboard</a></li>
            
            <div class="sidebar-section">Students</div>
            <li><a href="a_view_students.php" class="<?php if ($currentPage == 'a_view_students.php') echo 'active'; ?>">👥 View Students</a></li>
            <li><a href="a_students.php" class="<?php if ($currentPage == 'a_students.php') echo 'active'; ?>">➕ Add Student</a></li>
            
            <div class="sidebar-section">Grades</div>
            <li><a href="a_results.php" class="<?php if ($currentPage == 'a_results.php') echo 'active'; ?>">📝 Upload Grades</a></li>
            <li><a href="a_view_grades.php" class="<?php if ($currentPage == 'a_view_grades.php') echo 'active'; ?>">📋 View/Edit Grades</a></li>
            
            <div class="sidebar-section">Subjects</div>
            <li><a href="a_subjects.php" class="<?php if ($currentPage == 'a_subjects.php') echo 'active'; ?>">📚 Manage Subjects</a></li>
            
            <div class="sidebar-section">Settings</div>
            <li><a href="cpassword.php" class="<?php if ($currentPage == 'cpassword.php') echo 'active'; ?>">🔑 Change Password</a></li>
            
        <?php else: ?>
            <div class="sidebar-section">Dashboard</div>
            <li><a href="dashboard.php" class="<?php if ($currentPage == 'dashboard.php') echo 'active'; ?>">📊 Dashboard</a></li>
            
            <div class="sidebar-section">Academics</div>
            <li><a href="s_results.php" class="<?php if ($currentPage == 's_results.php') echo 'active'; ?>">📜 View Results</a></li>
            
            <div class="sidebar-section">Settings</div>
            <li><a href="cpassword.php" class="<?php if ($currentPage == 'cpassword.php') echo 'active'; ?>">🔑 Change Password</a></li>
        <?php endif; ?>
    </ul>
    
    <a href="logout.php" class="logout-btn">🚪 Logout</a>
</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
        document.querySelector('.sidebar-overlay').classList.toggle('show');
    }
    
    function closeSidebar() {
        document.querySelector('.sidebar').classList.remove('open');
        document.querySelector('.sidebar-overlay').classList.remove('show');
    }
</script>

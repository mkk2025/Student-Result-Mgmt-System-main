<?php
// Test Supabase Connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Supabase Connection</h2>";

try {
    include 'config.php';
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Connection established successfully!</p>";
        
        // Test 1: Check if tables exist
        echo "<h3>Checking Database Tables:</h3>";
        $tables = ['users', 'students', 'subjects', 'marks'];
        
        foreach ($tables as $table) {
            try {
                $result = $conn->query("SELECT COUNT(*) as count FROM \"$table\"");
                if ($result) {
                    $row = $result->fetch_assoc();
                    $count = $row['count'] ?? 0;
                    echo "<p>✅ Table '$table' exists - $count records</p>";
                } else {
                    echo "<p style='color: orange;'>⚠️ Table '$table' might not exist</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Table '$table' error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        
        // Test 2: Try a simple query
        echo "<h3>Testing Query:</h3>";
        try {
            $result = $conn->query('SELECT COUNT(*) as total FROM "users"');
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<p>✅ Query successful! Total users: " . ($row['total'] ?? 0) . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Query error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Test 3: Test prepared statement
        echo "<h3>Testing Prepared Statement:</h3>";
        try {
            $stmt = $conn->prepare('SELECT * FROM "users" WHERE "enroll_no" = ? LIMIT 1');
            $stmt->bind_param('s', '100');
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $user = $result->fetch_assoc();
                if ($user) {
                    echo "<p>✅ Prepared statement works! Found user: " . htmlspecialchars($user['username'] ?? 'N/A') . "</p>";
                } else {
                    echo "<p>⚠️ Prepared statement works but no user found with ID 100</p>";
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Prepared statement error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>


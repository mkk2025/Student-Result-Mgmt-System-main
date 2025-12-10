<?php
$servername = "127.0.0.1";
$username = "srms_user";
$password = "srms_pass123"; // Database password
$database = "srms"; // Your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

<?php
// ============================================
// CLOUD DATABASE CONFIGURATION TEMPLATE
// ============================================
// Copy this file to config.php and update with your credentials

// ============================================
// OPTION 1: SUPABASE (PostgreSQL) - RECOMMENDED
// ============================================
// Get credentials from: https://supabase.com → Project Settings → Database

/*
$servername = "db.xxxxx.supabase.co";
$username = "postgres";
$password = "your-password-here";
$database = "postgres";
$port = "5432";

// PostgreSQL connection
$conn = pg_connect("host=$servername port=$port dbname=$database user=$username password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
*/

// ============================================
// OPTION 2: DB4FREE.NET (MySQL) - FREE MYSQL
// ============================================
// Sign up at: https://db4free.net

/*
$servername = "db4free.net";
$username = "your-username";
$password = "your-password";
$database = "srms";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
*/

// ============================================
// OPTION 3: PLANETSCALE (MySQL) - FREE TIER
// ============================================
// Sign up at: https://planetscale.com

/*
$servername = "xxxxx.psdb.cloud";
$username = "xxxxx";
$password = "xxxxx";
$database = "srms";

// PlanetScale requires SSL
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
*/

// ============================================
// OPTION 4: LOCAL DATABASE (Current Setup)
// ============================================
$servername = "127.0.0.1";
$username = "srms_user";
$password = "srms_pass123";
$database = "srms";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>


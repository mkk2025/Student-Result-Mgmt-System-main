# Free Database Integration Guide
## Recommended: Supabase (PostgreSQL) - Best Free Option

### Why Supabase?
✅ **Truly Free** - No credit card required  
✅ **500MB Database** - More than enough for student records  
✅ **Unlimited API Requests** - No usage limits  
✅ **Modern PostgreSQL** - Better than MySQL  
✅ **Easy Setup** - 5-minute setup  
✅ **Built-in Security** - SSL by default  
✅ **Free Forever** - No hidden costs  

### Alternative: db4free.net (MySQL)
✅ **Free MySQL 8.0** - If you prefer MySQL  
✅ **No Credit Card** - Completely free  
⚠️ **Limited** - 200MB storage, slower performance  

---

## Setup Instructions for Supabase

### Step 1: Create Supabase Account
1. Go to https://supabase.com
2. Click "Start your project"
3. Sign up with GitHub/Google (free)
4. Create a new project

### Step 2: Get Database Credentials
1. Go to Project Settings → Database
2. Copy these details:
   - **Host**: `db.xxxxx.supabase.co`
   - **Database**: `postgres`
   - **Port**: `5432`
   - **User**: `postgres`
   - **Password**: (shown in settings)

### Step 3: Update config.php
Replace your `config.php` with PostgreSQL connection:

```php
<?php
// Supabase PostgreSQL Connection
$servername = "db.xxxxx.supabase.co";
$username = "postgres";
$password = "your-password-here";
$database = "postgres";
$port = "5432";

// Create connection using PostgreSQL
$conn = pg_connect("host=$servername port=$port dbname=$database user=$username password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>
```

### Step 4: Migrate Database Schema
You'll need to convert MySQL schema to PostgreSQL. Use the migration script provided.

---

## Setup Instructions for db4free.net (MySQL)

### Step 1: Create Account
1. Go to https://db4free.net
2. Click "Sign up"
3. Fill in the form
4. Verify email

### Step 2: Create Database
1. Login to https://db4free.net/phpMyAdmin
2. Create new database: `srms`
3. Note your credentials

### Step 3: Update config.php
```php
<?php
// db4free.net MySQL Connection
$servername = "db4free.net";
$username = "your-username";
$password = "your-password";
$database = "srms";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

---

## My Recommendation: **Supabase**

**Reasons:**
1. More reliable and modern
2. Better performance
3. Free forever with no hidden costs
4. Better security features
5. Easy to scale later

**Migration is simple** - I can help convert the MySQL schema to PostgreSQL if you choose Supabase.

---

## Quick Start Commands

### Export Current Database
```bash
mysqldump -u srms_user -psrms_pass123 srms > srms_backup.sql
```

### Test Connection
```php
<?php
// Test your new database connection
include 'config.php';
if ($conn) {
    echo "✅ Database connected successfully!";
} else {
    echo "❌ Connection failed!";
}
?>
```


# Supabase Integration Guide

## Step 1: Get Your Supabase Credentials

1. Go to your Supabase Dashboard: https://supabase.com/dashboard
2. Select your project
3. Go to **Settings** → **Database**
4. Find the **Connection string** section
5. Copy these details:
   - **Host**: `db.xxxxx.supabase.co`
   - **Port**: `5432`
   - **Database**: `postgres`
   - **User**: `postgres`
   - **Password**: (Click "Reveal" to see it)

## Step 2: Create Database Schema

1. In Supabase Dashboard, go to **SQL Editor**
2. Click **New Query**
3. Copy and paste the contents of `create_database_postgresql.sql`
4. Click **Run** (or press Ctrl+Enter)
5. You should see "Success. No rows returned"

## Step 3: Update config.php

1. Open `config.supabase.php`
2. Replace these values:
   ```php
   $servername = "YOUR_SUPABASE_HOST"; // e.g., db.xxxxx.supabase.co
   $password = "YOUR_SUPABASE_PASSWORD";
   ```
3. Save the file
4. **Rename** `config.supabase.php` to `config.php` (backup your old config.php first!)

## Step 4: Test Connection

Create a test file `test_connection.php`:

```php
<?php
include 'config.php';
if ($conn) {
    echo "✅ Connected to Supabase successfully!";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "<br>Users in database: " . $row['count'];
} else {
    echo "❌ Connection failed!";
}
?>
```

Visit: `http://localhost:8000/test_connection.php`

## Step 5: Migrate Existing Data (Optional)

If you have data in your local MySQL database:

1. Export from MySQL:
   ```bash
   mysqldump -u srms_user -psrms_pass123 srms > local_data.sql
   ```

2. Convert and import to PostgreSQL (I can help with this if needed)

## Troubleshooting

### Connection Error
- Check firewall settings in Supabase
- Verify credentials are correct
- Ensure you're using the correct host (not the API URL)

### Schema Errors
- Make sure you ran the PostgreSQL schema script
- Check Supabase SQL Editor for error messages

### PHP PDO Extension
If you get "Class 'PDO' not found":
```bash
sudo apt-get install php-pgsql
sudo service php-fpm restart  # or apache2 restart
```

## Security Notes

⚠️ **Important:**
- Never commit `config.php` with real credentials to Git
- Use environment variables in production
- Enable SSL in Supabase (it's on by default)

## Next Steps

Once connected:
1. Test login with existing accounts (ID: 100, 470)
2. Test signup functionality
3. Verify data is being saved to Supabase

---

**Need Help?** Share your Supabase credentials (host and password) and I'll update the config file for you!


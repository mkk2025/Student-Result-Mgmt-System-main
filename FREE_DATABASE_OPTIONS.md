# Free Database Hosting Options
## For IMATT College Student Result Management System

### Recommended Free Database Services

#### 1. **PlanetScale** (Recommended)
- **Free Tier**: 1 database, 1GB storage, 1 billion row reads/month
- **MySQL Compatible**: Yes (Vitess-based)
- **Connection**: HTTPS API or MySQL protocol
- **Setup**: 
  - Sign up at https://planetscale.com
  - Create database
  - Get connection string
  - Update `config.php` with connection details

#### 2. **Aiven for MySQL**
- **Free Tier**: 1 month free trial, then pay-as-you-go
- **MySQL Compatible**: Yes
- **Setup**: https://aiven.io

#### 3. **Railway**
- **Free Tier**: $5 credit monthly
- **MySQL Compatible**: Yes
- **Setup**: https://railway.app

#### 4. **Supabase** (PostgreSQL)
- **Free Tier**: 500MB database, unlimited API requests
- **MySQL Compatible**: No (PostgreSQL, but can migrate)
- **Setup**: https://supabase.com

#### 5. **Free MySQL Hosting**
- **db4free.net**: Free MySQL 8.0 database
- **remotemysql.com**: Free MySQL hosting
- **freesqldatabase.com**: Free MySQL database

### Configuration Example for Free Database

Update `config.php`:

```php
<?php
// Example for PlanetScale or other cloud database
$servername = "your-db-host.planetscale.com"; // or your provider's host
$username = "your-username";
$password = "your-password";
$database = "srms";

// For SSL connections (recommended)
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

### Migration Steps

1. **Export local database:**
   ```bash
   mysqldump -u srms_user -p srms > srms_backup.sql
   ```

2. **Import to cloud database:**
   ```bash
   mysql -h your-host -u username -p database_name < srms_backup.sql
   ```

3. **Update config.php** with new credentials

4. **Test connection** and verify data

---

**Note**: For production, always use secure connections (SSL) and keep credentials safe.


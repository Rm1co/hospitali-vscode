# MariaDB Authentication Error Fix Guide

## Error Message
```
Database connection failed: SQLSTATE[HY000] [2054] 
The server requested authentication method unknown to the client [auth_gssapi_client]
```

## What Causes This?

Your MariaDB server is using an authentication method that your PHP PDO MySQL extension doesn't support. This happens when:
- MariaDB is using GSSAPI or other modern auth methods
- PHP MySQL driver is expecting `mysql_native_password`
- Connection credentials are wrong
- MariaDB service isn't running

## Solution 1: Quick Fix Using MariaDB Command Line (Recommended)

### Step 1: Open MariaDB Command Prompt
1. Open Command Prompt or PowerShell (as Administrator)
2. Navigate to your MariaDB bin folder:
   ```bash
   cd "C:\Program Files\MariaDB 11.0\bin"
   ```
   (Adjust version number if different)

### Step 2: Connect to MariaDB
```bash
mysql -u root -p
```
When prompted, enter your MariaDB root password (default: no password or Aa133542)

### Step 3: Execute These Commands
```sql
-- Create database
CREATE DATABASE IF NOT EXISTS hospital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Reset root user with proper authentication
DROP USER IF EXISTS 'root'@'localhost';
CREATE USER 'root'@'localhost' IDENTIFIED BY 'Aa133542';

-- Grant all privileges
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;

-- Reload permissions
FLUSH PRIVILEGES;

-- Test connection
SELECT 'Success! MariaDB is ready!' AS Status;
```

### Step 4: Exit MariaDB
```bash
exit
```

### Step 5: Verify Connection
```bash
mysql -u root -p -e "SHOW DATABASES;"
```
You should see `hospital` in the list.

## Solution 2: Using Diagnostic Tool (Easiest)

The system includes an automatic diagnostic tool:

1. **Open browser and visit:**
   ```
   http://localhost/hospitali-vscode-1/diagnostic.php
   ```

2. **The tool will:**
   - Test if MariaDB is running
   - Try common credentials automatically
   - Show exactly what's broken
   - Suggest fixes

3. **Fix the issues shown** (start with green ✅ results)

## Solution 3: Direct Database Configuration

Edit `backend/php/DatabaseConnector.php` to use your actual credentials:

```php
// Line ~28 - Change the password:
$pass = getenv('DB_PASS') ?: 'YOUR_ACTUAL_PASSWORD';

// Other connection details:
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'hospital';
$user = getenv('DB_USER') ?: 'root';
```

## MariaDB Service Management

### Check if MariaDB is Running

**Command Prompt (Admin):**
```bash
net start | find "MariaDB"
```
- If found: ✅ Running
- If not found: ❌ Stopped

### Start MariaDB Service

**Option 1 - Command Prompt (as Administrator):**
```bash
net start MariaDB
```

**Option 2 - Windows Services GUI:**
- Press `Windows + R`
- Type: `services.msc`
- Find "MariaDB"
- Right-click → Start

### Restart MariaDB
```bash
net stop MariaDB
net start MariaDB
```

## Step-by-Step Verification

### Check 1: MariaDB Connection
```bash
mysql -u root -p
```
✅ If you get `mysql>` prompt, connection works!
❌ If you get "Access denied", check password

### Check 2: Database Exists
```bash
mysql -u root -p -e "SHOW DATABASES;"
```
✅ You should see `hospital` in the list
❌ If not, run the SQL commands from Solution 1

### Check 3: Database Tables
```bash
mysql -u root -p hospital -e "SHOW TABLES;"
```
✅ Should see ~8 tables (patients, appointments, etc.)
❌ If empty, tables need to be created from schema

### Check 4: User Authentication Method
```bash
mysql -u root -p -e "SELECT user, host FROM mysql.user WHERE user='root';"
```
✅ Should see `root@localhost`
❌ If nothing, user doesn't exist

## Common Issues & Solutions

### Issue: "Access denied for user 'root'"
**Causes:** Wrong password, no password set, or user doesn't exist

**Fix:**
```bash
# Try without password
mysql -u root

# If that works, set password:
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'Aa133542';"

# If that fails, reset user:
mysql -u root -e "DROP USER IF EXISTS 'root'@'localhost'; CREATE USER 'root'@'localhost' IDENTIFIED BY 'Aa133542';"
```

### Issue: "Unknown database 'hospital'"
**Cause:** Database hasn't been created

**Fix:**
```bash
mysql -u root -p -e "CREATE DATABASE hospital CHARACTER SET utf8mb4;"
```

### Issue: "Can't connect to MySQL server on '127.0.0.1'"
**Cause:** MariaDB service isn't running or wrong port

**Fix:**
```bash
# Start service
net start MariaDB

# Or if using different port, update DatabaseConnector.php:
$port = 3307; // Change if MariaDB runs on different port
```

### Issue: Connection timeout (30+ seconds then fails)
**Cause:** MariaDB is trying to reverse-resolve hostname

**Fix:** Edit `C:\Program Files\MariaDB 11.0\data\my.ini`:
```ini
[mysqld]
skip-name-resolve
```
Then restart: `net stop MariaDB` and `net start MariaDB`

### Issue: "SQLSTATE[HY000]: General error"
**Causes:** 
- MariaDB crashed or stopped
- Permissions issue
- MySQL driver missing

**Fix:**
- Check MariaDB service is running: `net start MariaDB`
- Try diagnostic tool: `http://localhost/hospitali-vscode-1/diagnostic.php`

## Troubleshooting Table

| Error | Cause | Fix |
|-------|-------|-----|
| Access denied | Wrong password | Check DatabaseConnector.php or use correct password |
| Unknown database | Not created | `CREATE DATABASE hospital;` |
| Can't connect | Service stopped | `net start MariaDB` |
| Timeout | DNS resolution | Add `skip-name-resolve` to my.ini |
| auth_gssapi_client | Wrong auth method | Reset user (see Solution 1) |
| Port 3306 in use | Another service using port | Change port in my.ini or stop other service |

## Final Verification

After all fixes, test the complete flow:

1. **Visit signup page:**
   ```
   http://localhost/hospitali-vscode-1/
   ```

2. **Try signing up:**
   - Fill in email, password, confirm password
   - Click "Send Verification Code"

3. **Check results:**
   - ✅ Get email with code → Database working!
   - ❌ Get error → Check diagnostic.php

4. **Complete signup:**
   - Enter 6-digit code
   - Click "Sign Up"
   - Should redirect to login

5. **Test login:**
   - Use credentials just created
   - Click "Login"
   - Should see patient dashboard

## Database Credentials Reference

**File:** `backend/php/DatabaseConnector.php`

**Current Settings:**
- Host: `127.0.0.1` (localhost)
- Port: `3306` (default MariaDB)
- Database: `hospital`
- User: `root`
- Password: `Aa133542`

**Email Settings (PHPMailer):**
- Provider: Gmail SMTP
- Email: `brian.ikubu@strathmore.edu`
- App Password: `vahi auht awkv kyri`

## MariaDB vs MySQL

| Feature | MariaDB | MySQL |
|---------|---------|-------|
| Default Port | 3306 | 3306 |
| Command | mysql | mysql |
| Auth Method | mysql_native_password | caching_sha2 (8.0+) |
| Service Name | MariaDB | MySQL80, MySQL57 |
| PDO DSN | mysql: | mysql: |
| Our System | ✅ Works | ✅ Works |

**Both work with our system!**

## Production Recommendations

For live deployment:
- Use environment variables for credentials
- Implement connection pooling
- Enable SSL/TLS connections
- Restrict user privileges per application
- Use secrets management tools
- Set strong passwords
- Regular database backups

## Need More Help?

1. **Run diagnostic tool:**
   - `http://localhost/hospitali-vscode-1/diagnostic.php`
   - Shows exact problem and solutions

2. **Check configuration files:**
   - `backend/php/DatabaseConnector.php` - Connection settings
   - `SETUP_GUIDE.md` - General setup
   - `QUICK_START.md` - Quick reference

3. **Check logs:**
   - Apache errors: `C:\Apache24\logs\error.log`
   - Browser console: F12 → Console

4. **Verify MariaDB:**
   - `mysql -u root -p -e "SELECT VERSION();"`
   - `mysql -u root -p -e "STATUS;"`

The diagnostic tool is your best friend! 🔧
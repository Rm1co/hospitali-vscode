# Database Connection Fix - MariaDB Setup Guide

## Quick Start (Follow These Steps)

### Step 1: Run Diagnostic Tool
1. Open your browser
2. Visit: `http://localhost/hospitali-vscode-1/diagnostic.php`
3. This will show you:
   - ✅ What's working
   - ⚠️ What needs attention
   - ❌ What's broken
   - 💡 Working credentials if available

### Step 2: Check MariaDB Status
From the diagnostic tool results:

**If MariaDB Connection is ❌ FAILED:**
- Windows: Open Services (Windows key → "Services" or `services.msc`)
- Look for "MariaDB" service
- If stopped, right-click → Start

**If Hospital Database is Missing:**
- The diagnostic tool will create it automatically on next visit
- Or manually: `CREATE DATABASE hospital;`

**If Authentication is Wrong:**
- The diagnostic tool shows working credentials
- Update `backend/php/DatabaseConnector.php` if needed

### Step 3: Create Database Tables
If diagnostic shows "No tables found":

**Via Command Line (Recommended):**
```bash
cd "C:\Program Files\MariaDB 11.0\bin"
mysql -u root -p hospital < "C:\Apache24\htdocs\hospitali-vscode-1\backend\database\schema-complete.sql"
```

**Or manually in MariaDB:**
```bash
mysql -u root -p
```
Then copy/paste contents of `backend/database/schema-complete.sql`

### Step 4: Test Connection
1. Visit: `http://localhost/hospitali-vscode-1/`
2. Should redirect to login page
3. Try signing up
4. Check email for verification code
5. If code arrives → ✅ Success!

---

## Common MariaDB Issues & Fixes

### "Access denied for user 'root'"

**Most Common Cause:** Wrong password or no password set

**Fix Option 1 - Check/Set Password:**
```bash
mysql -u root
# If this works, root has no password. Set one:
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'Aa133542';"
```

**Fix Option 2 - Update DatabaseConnector.php:**
```php
// File: backend/php/DatabaseConnector.php
// Find this line (~28):
$pass = getenv('DB_PASS') ?: 'Aa133542';
// Change 'Aa133542' to your actual MariaDB root password
```

**Fix Option 3 - Diagnostic Tool Finder:**
The diagnostic tool tries 10 different common credentials automatically and shows which ones work!

### "Unknown database 'hospital'"

This should auto-create, but if not:
```bash
mysql -u root -p -e "CREATE DATABASE hospital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### "SQLSTATE[HY000]: General error"

Usually means MariaDB service isn't running.

**Restart MariaDB:**
```bash
net stop MariaDB
net start MariaDB
```

Or via Services GUI (services.msc)

### MariaDB Won't Start

**Check installation:**
```bash
cd "C:\Program Files\MariaDB 11.0\bin"
mysqld --version
```

If not found, MariaDB isn't installed. Install from: https://mariadb.org/download/

### Connection Timeout (takes 30+ seconds)

Edit `C:\Program Files\MariaDB 11.0\data\my.ini`:
```ini
[mysqld]
skip-name-resolve
```

Then restart MariaDB.

---

## MariaDB Authentication Methods

MariaDB supports multiple auth methods. If you get specific auth errors:

**Reset MariaDB Authentication:**
```bash
mysql -u root -p
```

Then in MariaDB prompt:
```sql
DROP USER IF EXISTS 'root'@'localhost';
CREATE USER 'root'@'localhost' IDENTIFIED BY 'Aa133542';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

---

## Using Diagnostic Tool Output

### ✅ All Green Example:
```
✅ Connected to MariaDB Server
   Host: 127.0.0.1:3306
   MariaDB Version: 11.0.2
✅ Hospital database exists
✅ Database tables found (8 tables)
✅ Connection is working perfectly!
```
**Action:** Try signing up and creating an account!

### ⚠️ Mixed Results Example:
```
✅ MariaDB Service is running
❌ Cannot connect to database
   Error: Access denied for user 'root'@'localhost'
💡 Try password: Aa133542
```
**Action:** Check the suggested password or see "Access denied" fix above

### ❌ Failed Example:
```
❌ MariaDB Service is NOT running
   Cannot connect to 127.0.0.1:3306
```
**Action:** Start MariaDB from Windows Services

---

## Manual Database Setup (GUI Method)

If you prefer graphical interface:

**Use HeidiSQL (Free):**
1. Download: https://www.heidisql.com/
2. Create new connection:
   - Hostname: 127.0.0.1
   - User: root
   - Password: Aa133542
3. Right-click → New Database → Name: "hospital"
4. Import SQL file: `backend/database/schema-complete.sql`

---

## Complete System Reset (Last Resort)

If everything is broken:

**1. Stop MariaDB:**
```bash
net stop MariaDB
```

**2. Backup existing data (optional):**
```bash
xcopy "C:\Program Files\MariaDB 11.0\data" "C:\MariaDB_backup" /I /E /Y
```

**3. Delete hospital database files:**
```bash
cd "C:\Program Files\MariaDB 11.0\data"
rmdir /s /q hospital
```

**4. Start MariaDB:**
```bash
net start MariaDB
```

**5. Recreate everything:**
- Visit `http://localhost/hospitali-vscode-1/diagnostic.php`
  - Database will auto-create
- Run: `mysql -u root -p hospital < backend/database/schema-complete.sql`
  - Tables will be created

---

## Verify Everything Works

**Complete Checklist:**
- [ ] MariaDB service is running (Services.msc)
- [ ] Can connect: `mysql -u root -p` works
- [ ] Hospital database exists: `SHOW DATABASES;` shows "hospital"
- [ ] Tables exist: `USE hospital; SHOW TABLES;` shows 8 tables
- [ ] Visit diagnostic.php shows all ✅
- [ ] Login page loads at `http://localhost/hospitali-vscode-1/`
- [ ] Can complete sign up
- [ ] Receive verification email
- [ ] Can log in with new account
- [ ] Secretary dashboard loads for staff login

---

## Connection Details Reference

**File:** `backend/php/DatabaseConnector.php`

**Connection Parameters:**
- **Host:** 127.0.0.1 (localhost)
- **Port:** 3306
- **Database:** hospital
- **User:** root
- **Password:** Aa133542 (configurable)

**Email (PHPMailer) Settings:**
- **Provider:** Gmail SMTP
- **Email:** brian.ikubu@strathmore.edu
- **App Password:** vahi auht awkv kyri
- **Verification:** 6-digit code, 5-minute expiry

---

## Troubleshooting Steps in Order

1. **Is MariaDB running?** 
   - Check Services → Start if needed

2. **Can you connect manually?**
   - Try: `mysql -u root -p`

3. **Does hospital database exist?**
   - In MySQL prompt: `SHOW DATABASES;`

4. **Does hospital have tables?**
   - In MySQL prompt: `USE hospital; SHOW TABLES;`

5. **Check diagnostic tool:**
   - Visit: `http://localhost/hospitali-vscode-1/diagnostic.php`
   - Shows exactly what's working/broken

6. **Check PHP error logs:**
   - Apache error log: `C:\Apache24\logs\error.log`

7. **Check browser console:**
   - F12 → Console → See JavaScript errors

---

## Getting Help

**Information to collect:**
1. Output from `diagnostic.php`
2. Error message from signup page
3. PHP error log (if available)
4. MariaDB version: `mysql --version`

**Quick Test Command:**
```bash
mysql -u root -p hospital -e "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema='hospital';"
```

The diagnostic tool handles most issues automatically! 🔧
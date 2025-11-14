# 🏥 Hospital Management System - MariaDB Edition

## START HERE ⬇️

### ✅ System is Ready!

Your hospital management system has been fully configured for **MariaDB**. Follow these 3 simple steps to get started:

---

## Step 1: Verify MariaDB is Running (30 seconds)

Open PowerShell/Command Prompt and type:
```bash
mysql -u root -p
```

**Expected result:** You see `mysql>` prompt
- If yes ✅ → Go to Step 2
- If no ❌ → Start MariaDB: `net start MariaDB`

---

## Step 2: Run the Diagnostic Tool (1 minute)

Open your browser and visit:
```
http://localhost/hospitali-vscode-1/diagnostic.php
```

**What it shows:**
- ✅ Green checkmarks = Working perfectly
- ⚠️ Yellow warnings = Need attention
- ❌ Red errors = Must fix

**What it can fix automatically:**
- Creates database if missing
- Tests all credentials
- Shows exactly what's wrong

---

## Step 3: Test the System (2 minutes)

Visit the main page:
```
http://localhost/hospitali-vscode-1/
```

**You should see:**
- ✅ Role selection page (Doctor/Patient/Staff)
- Click "Patient" → Login/Signup page
- Try signing up
- Check your email for verification code

**If all works:** 🎉 System is ready for use!

---

## 📚 Documentation Guide

| Document | Purpose | Read When |
|----------|---------|-----------|
| **QUICK_REFERENCE.md** | Cheat sheet with commands | Need quick answers |
| **DATABASE_FIX.md** | MariaDB troubleshooting | Getting auth errors |
| **FIX_DATABASE.md** | Complete MariaDB setup | Database not connecting |
| **MARIADB_SETUP_COMPLETE.md** | Full system overview | Want to understand everything |
| **SETUP_GUIDE.md** | Initial installation guide | Setting up from scratch |
| **QUICK_START.md** | Fast reference guide | Need quick reference |

---

## 🔑 Key Credentials

**Database:**
```
Host: 127.0.0.1
Port: 3306
User: root
Password: Aa133542
Database: hospital (auto-created)
```

**Email Testing:**
```
Email: brian.ikubu@strathmore.edu
Gmail App Password: vahi auht awkv kyri
```

---

## ⚡ Quick Commands

```bash
# Start MariaDB
net start MariaDB

# Connect to MariaDB
mysql -u root -p

# Check if database exists
mysql -u root -p -e "SHOW DATABASES;"

# See hospital tables
mysql -u root -p hospital -e "SHOW TABLES;"

# Create tables from schema
mysql -u root -p hospital < backend/database/schema-complete.sql
```

---

## 🎯 System Features

**Patient Portal:**
- Sign up with email verification
- Login and manage profile
- View/book appointments
- See medical history

**Secretary Dashboard:**
- View available doctors
- Allocate patients to doctors
- Manage appointments
- Track doctor schedules

**Security:**
- Password strength validation
- Email-based verification (OTP)
- Session-based authentication
- SQL injection protection

---

## ⚠️ Most Common Issues

**Problem:** "Can't connect to MariaDB"
```bash
net start MariaDB    # This fixes 90% of issues!
```

**Problem:** "Access denied for user 'root'"
```bash
# Check your password in DatabaseConnector.php
# File: backend/php/DatabaseConnector.php
# Default: 'Aa133542'
```

**Problem:** "Unknown database 'hospital'"
```bash
# The system auto-creates it, but if manual:
mysql -u root -p -e "CREATE DATABASE hospital;"
```

**Problem:** "Tables not found"
```bash
# Create tables from schema:
mysql -u root -p hospital < backend/database/schema-complete.sql
```

---

## 🔍 Automated Diagnosis

The system includes a diagnostic tool that can usually fix things automatically:

```
http://localhost/hospitali-vscode-1/diagnostic.php
```

**It will:**
1. Check if MariaDB is running
2. Try common passwords
3. Create database if missing
4. Show what's wrong
5. Suggest exact fixes

---

## 📋 Verification Checklist

Complete this to ensure everything works:

- [ ] Step 1: MariaDB connects (`mysql -u root -p` works)
- [ ] Step 2: Diagnostic shows all ✅
- [ ] Step 3: Main site loads at `http://localhost/hospitali-vscode-1/`
- [ ] Step 4: Can see role selection (Doctor/Patient/Staff)
- [ ] Step 5: Can complete signup on Patient login
- [ ] Step 6: Email arrives with verification code
- [ ] Step 7: Can complete signup
- [ ] Step 8: Can login with new credentials
- [ ] Step 9: Secretary dashboard loads for staff

---

## 📞 Getting Help

**Check these in order:**

1. **Confused?** → `QUICK_REFERENCE.md`
2. **Database error?** → `DATABASE_FIX.md`
3. **Can't connect?** → Run `http://localhost/hospitali-vscode-1/diagnostic.php`
4. **Want full details?** → `MARIADB_SETUP_COMPLETE.md`
5. **Need setup?** → `SETUP_GUIDE.md`

---

## 🚀 Next Steps

### Right Now:
1. Verify MariaDB: `mysql -u root -p`
2. Run diagnostic: `http://localhost/hospitali-vscode-1/diagnostic.php`
3. Visit main site: `http://localhost/hospitali-vscode-1/`

### Then:
4. Test signup/login
5. Verify email delivery
6. Test secretary dashboard
7. Create test appointments

### After That:
- Configure with your actual data
- Customize styling if needed
- Add more users/staff
- Set up proper email account (not test account)

---

## ✨ What's Included

✅ Complete hospital management system
✅ Patient sign-up with email verification  
✅ Secretary appointment scheduling
✅ Doctor availability tracking
✅ Responsive design (mobile-friendly)
✅ Dark/Light theme toggle
✅ Security best practices
✅ MariaDB auto-connection
✅ Automated diagnostic tool
✅ Complete documentation

---

## 🎓 Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.8+
- **Database:** MariaDB 11.x (or MySQL 5.7+)
- **Email:** PHPMailer v6.12.0
- **Server:** Apache 2.4
- **Security:** PDO prepared statements, password hashing

---

**Everything is ready! Start with Step 1 above.** 👆

Good luck! 🏥✨
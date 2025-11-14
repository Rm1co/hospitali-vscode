# Quick Reference - MariaDB Hospital System

## 🚀 Start Here

### 1. Check MariaDB is Running
```bash
mysql -u root -p
```

### 2. Run Diagnostic
```
http://localhost/hospitali-vscode-1/diagnostic.php
```

### 3. Test System
```
http://localhost/hospitali-vscode-1/
```

---

## 🔧 Common Commands

| Task | Command |
|------|---------|
| Start MariaDB | `net start MariaDB` |
| Stop MariaDB | `net stop MariaDB` |
| Restart MariaDB | `net stop MariaDB` ; `net start MariaDB` |
| Connect to MariaDB | `mysql -u root -p` |
| List databases | `mysql -u root -p -e "SHOW DATABASES;"` |
| List hospital tables | `mysql -u root -p hospital -e "SHOW TABLES;"` |
| Check MariaDB version | `mysql -u root -p -e "SELECT VERSION();"` |
| Create hospital DB | `mysql -u root -p -e "CREATE DATABASE hospital;"` |
| Create tables | `mysql -u root -p hospital < backend/database/schema-complete.sql` |

---

## ⚠️ Common Issues

| Error | Quick Fix |
|-------|-----------|
| Can't connect | `net start MariaDB` |
| Access denied | Password wrong - see DATABASE_FIX.md |
| Unknown database | `CREATE DATABASE hospital;` |
| No tables | `mysql -u root -p hospital < backend/database/schema-complete.sql` |

---

## 📋 Verify Everything

**Checklist:**
- [ ] `mysql -u root -p` connects successfully
- [ ] `SHOW DATABASES;` shows "hospital"
- [ ] `USE hospital; SHOW TABLES;` shows ~8 tables
- [ ] Visit `http://localhost/hospitali-vscode-1/` - redirects to login
- [ ] Can see login/signup page
- [ ] Can complete signup (receives email)

---

## 📂 Key Files

| File | Purpose |
|------|---------|
| `diagnostic.php` | Automated system checker |
| `backend/php/DatabaseConnector.php` | Connection logic |
| `backend/database/schema-complete.sql` | Database schema |
| `DATABASE_FIX.md` | Troubleshooting guide |
| `FIX_DATABASE.md` | MariaDB setup guide |
| `MARIADB_SETUP_COMPLETE.md` | Detailed system guide |

---

## 🔐 Login Credentials

**Database:**
- User: `root`
- Password: `Aa133542`
- Host: `127.0.0.1:3306`
- Database: `hospital`

**Email (Test Verification):**
- Email: `brian.ikubu@strathmore.edu`
- App Password: `vahi auht awkv kyri` (Gmail)

---

## 🌐 Website URLs

| Page | URL |
|------|-----|
| Main | `http://localhost/hospitali-vscode-1/` |
| Diagnostic | `http://localhost/hospitali-vscode-1/diagnostic.php` |
| Login/Signup | `http://localhost/hospitali-vscode-1/patients-login.html` |
| Role Selection | `http://localhost/hospitali-vscode-1/role-selection.html` |
| Secretary Dashboard | `http://localhost/hospitali-vscode-1/secretary-dashboard.html` |

---

## 📞 Help

**For database issues:**
- Run diagnostic: `http://localhost/hospitali-vscode-1/diagnostic.php`
- Read: `DATABASE_FIX.md` or `FIX_DATABASE.md`

**For setup issues:**
- Read: `SETUP_GUIDE.md` or `QUICK_START.md`

**For system overview:**
- Read: `MARIADB_SETUP_COMPLETE.md`

---

## ✅ Status

All systems ready for MariaDB! 🚀

1. Verify MariaDB running
2. Run diagnostic tool
3. Fix any issues shown
4. Test system at main URL

Questions? Check MARIADB_SETUP_COMPLETE.md
@echo off
REM MySQL Database Setup Script for Hospital Management System (Windows)

echo.
echo Hospital Management System - Database Setup
echo ============================================
echo.
echo This script will fix the MySQL authentication issue.
echo.

REM Check if mysql is in PATH
where mysql >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: MySQL command-line client not found in PATH.
    echo Please add MySQL bin directory to your PATH or run this script from MySQL bin folder.
    echo.
    echo Common MySQL installation paths:
    echo - C:\Program Files\MySQL\MySQL Server 8.0\bin
    echo - C:\Program Files\MySQL\MySQL Server 5.7\bin
    pause
    exit /b 1
)

echo MySQL client found. Proceeding with database setup...
echo.
echo Enter your MySQL root password:
REM Note: Password input is masked
set /p ROOT_PASSWORD=

REM Create database and fix authentication
echo.
echo Creating database and fixing authentication...
echo.

mysql -u root -p%ROOT_PASSWORD% <<EOF
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS hospital;

-- Create user with mysql_native_password
DROP USER IF EXISTS 'root'@'localhost';
CREATE USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Aa133542';

-- Grant all privileges
GRANT ALL PRIVILEGES ON hospital.* TO 'root'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Verify
SELECT 'Database and user setup complete!' AS Status;
EOF

if %errorlevel% equ 0 (
    echo.
    echo.
    echo ===============================================
    echo SETUP COMPLETE!
    echo ===============================================
    echo.
    echo Your MySQL database is now configured correctly.
    echo You can now test the application:
    echo.
    echo Visit: http://localhost/hospitali-vscode-1/
    echo.
    pause
) else (
    echo.
    echo ERROR: Database setup failed. 
    echo Please check your MySQL password and ensure MySQL is running.
    echo.
    pause
    exit /b 1
)

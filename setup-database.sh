#!/bin/bash
# MySQL Database Setup Script for Hospital Management System

echo "Hospital Management System - Database Setup"
echo "============================================="
echo ""
echo "This script will help you fix the MySQL authentication issue."
echo ""

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo "ERROR: MySQL client not found. Please install MySQL."
    exit 1
fi

echo "MySQL found. Proceeding with database setup..."
echo ""
echo "Please enter your MySQL root password:"
read -s ROOT_PASSWORD

echo ""
echo "Creating database and fixing authentication..."

# Run MySQL commands
mysql -u root -p"$ROOT_PASSWORD" <<EOF
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS hospital;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Aa133542';

-- Grant all privileges
GRANT ALL PRIVILEGES ON hospital.* TO 'root'@'localhost';

-- Flush privileges to reload the grant tables
FLUSH PRIVILEGES;

-- Verify connection
SELECT 'Database setup complete!' AS Status;
EOF

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Database setup completed successfully!"
    echo "You can now test your connection by visiting: http://localhost/hospitali-vscode-1/"
else
    echo ""
    echo "❌ Error during database setup. Please check your MySQL password."
    exit 1
fi

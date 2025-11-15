-- Add email and password_hash columns to staff table if they don't exist
USE hospitali_db;

ALTER TABLE staff 
ADD COLUMN IF NOT EXISTS email VARCHAR(255) UNIQUE AFTER last_name,
ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) AFTER department;

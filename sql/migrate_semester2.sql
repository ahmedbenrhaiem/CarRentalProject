-- ============================================
-- Car Rental System - Semester 2 Migration
-- Run this AFTER your existing init.sql
-- ============================================

USE CarRental;

-- Add new columns to users table
ALTER TABLE users
    ADD COLUMN email VARCHAR(100) DEFAULT NULL AFTER username,
    ADD COLUMN first_name VARCHAR(50) DEFAULT NULL AFTER email,
    ADD COLUMN last_name VARCHAR(50) DEFAULT NULL AFTER first_name,
    ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER last_name,
    ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER phone,
    ADD COLUMN city VARCHAR(100) DEFAULT NULL AFTER address,
    ADD COLUMN postal_code VARCHAR(20) DEFAULT NULL AFTER city,
    ADD COLUMN country VARCHAR(100) DEFAULT NULL AFTER postal_code,
    ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER country,
    ADD COLUMN activation_token VARCHAR(64) DEFAULT NULL AFTER is_active,
    ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL AFTER activation_token,
    ADD COLUMN reset_token_expires DATETIME DEFAULT NULL AFTER reset_token,
    ADD COLUMN pending_email VARCHAR(100) DEFAULT NULL AFTER reset_token_expires,
    ADD COLUMN email_token VARCHAR(64) DEFAULT NULL AFTER pending_email,
    ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER email_token;

-- Extend password column for bcrypt hashes
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Populate existing users with sample profile data
UPDATE users SET
    email = CONCAT(username, '@example.com'),
    first_name = CASE username
        WHEN 'admin' THEN 'Admin' WHEN 'user1' THEN 'John' WHEN 'user2' THEN 'Jane'
        WHEN 'user3' THEN 'Bob' WHEN 'user4' THEN 'Alice' WHEN 'user5' THEN 'Charlie'
        WHEN 'user6' THEN 'Diana' WHEN 'user7' THEN 'Edward' WHEN 'user8' THEN 'Fiona'
        WHEN 'user9' THEN 'George' WHEN 'user10' THEN 'Helen' ELSE username END,
    last_name = CASE username
        WHEN 'admin' THEN 'System' WHEN 'user1' THEN 'Smith' WHEN 'user2' THEN 'Doe'
        WHEN 'user3' THEN 'Johnson' WHEN 'user4' THEN 'Williams' WHEN 'user5' THEN 'Brown'
        WHEN 'user6' THEN 'Jones' WHEN 'user7' THEN 'Davis' WHEN 'user8' THEN 'Miller'
        WHEN 'user9' THEN 'Wilson' WHEN 'user10' THEN 'Moore' ELSE 'User' END,
    city = 'Wroclaw',
    country = 'Poland',
    is_active = 1;

-- Add sample rental history for statistics
INSERT INTO rentals (user_id, car_id, rented_at, released_at) VALUES
(2, 1, '2025-06-01 10:00:00', '2025-06-05 10:00:00'),
(2, 3, '2025-06-10 10:00:00', '2025-06-15 10:00:00'),
(2, 5, '2025-07-01 10:00:00', '2025-07-05 10:00:00'),
(3, 1, '2025-07-10 10:00:00', '2025-07-15 10:00:00'),
(3, 2, '2025-07-20 10:00:00', '2025-07-25 10:00:00'),
(4, 1, '2025-08-01 10:00:00', '2025-08-05 10:00:00'),
(4, 4, '2025-08-10 10:00:00', '2025-08-15 10:00:00'),
(4, 6, '2025-08-20 10:00:00', '2025-08-25 10:00:00'),
(4, 7, '2025-09-01 10:00:00', '2025-09-05 10:00:00'),
(5, 2, '2025-09-10 10:00:00', '2025-09-15 10:00:00'),
(6, 3, '2025-09-20 10:00:00', '2025-09-25 10:00:00'),
(7, 8, '2025-10-01 10:00:00', '2025-10-05 10:00:00'),
(8, 9, '2025-10-10 10:00:00', '2025-10-15 10:00:00'),
(9, 10, '2025-10-20 10:00:00', '2025-10-25 10:00:00');

SELECT 'Migration completed successfully!' AS status;

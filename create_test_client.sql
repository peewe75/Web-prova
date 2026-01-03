-- SQL Script to create test client user
-- Execute this in phpMyAdmin on Hostinger

-- Check if user exists first
SELECT * FROM users WHERE email = 'testclient@example.com';

-- If not exists, insert:
INSERT INTO users (first_name, last_name, email, password_hash, role, created_at) 
VALUES (
    'Test', 
    'Client', 
    'testclient@example.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password123
    'client', 
    NOW()
);

-- Verify user was created
SELECT id, email, role, first_name, last_name FROM users WHERE email = 'testclient@example.com';

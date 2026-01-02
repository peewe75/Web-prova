<?php
require_once 'config.php';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Users Table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50),
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'client') DEFAULT 'client',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'users' check/create OK.<br>";

    // 2. Leads Table
    $sql = "CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        email VARCHAR(100),
        phone VARCHAR(20),
        topic VARCHAR(100),
        message TEXT,
        status ENUM('new', 'contacted', 'converted', 'archived') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )";
    $conn->exec($sql);
    echo "Table 'leads' check/create OK.<br>";

    // 3. Cases Table (Missing in previous version)
    $sql = "CREATE TABLE IF NOT EXISTS cases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status VARCHAR(50) DEFAULT 'Aperta',
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (user_id)
    )";
    $conn->exec($sql);
    echo "Table 'cases' check/create OK.<br>";

    // 4. Appointments (Existing) - Table for confirmed appointments
    $sql = "CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        date DATETIME NOT NULL,
        type VARCHAR(100),
        status VARCHAR(50) DEFAULT 'Programmato',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )";
    $conn->exec($sql);
    echo "Table 'appointments' check/create OK.<br>";

    // 4b. Appointment Requests (NEW)
    $sql = "CREATE TABLE IF NOT EXISTS appointment_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        requested_date DATE,
        requested_time TIME,
        type VARCHAR(100),
        notes TEXT,
        status ENUM('pending','accepted','rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (client_id)
    )";
    $conn->exec($sql);
    echo "Table 'appointment_requests' check/create OK.<br>";

    // 5. Documents Table
    $sql = "CREATE TABLE IF NOT EXISTS documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        file_path VARCHAR(255),
        file_type VARCHAR(20),
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )";
    $conn->exec($sql);
    echo "Table 'documents' check/create OK.<br>";

    // 6. Legal Notes (Renamed from client_notes to match request)
    $sql = "CREATE TABLE IF NOT EXISTS legal_notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (client_id)
    )";
    $conn->exec($sql);
    echo "Table 'legal_notes' check/create OK.<br>";
    
    // 7. Blog Posts (Ensure it exists)
    $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        content TEXT,
        status ENUM('draft', 'published', 'archived', 'suggestion') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'blog_posts' check/create OK.<br>";

    // 8. Activity Log (Problem 4 & 18)
    $sql = "CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(255) NOT NULL,
        action_type VARCHAR(50) DEFAULT 'info',
        details TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )";
    $conn->exec($sql);
    echo "Table 'activity_log' check/create OK.<br>";

    // 9. Messages (Problem 10 & 11)
    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        recipient_id INT NOT NULL,
        content TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        attachment_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (recipient_id),
        INDEX (sender_id)
    )";
    $conn->exec($sql);
    echo "Table 'messages' check/create OK.<br>";

    // 10. Password Resets (Problem 15)
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expiry DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (email)
    )";
    $conn->exec($sql);
    echo "Table 'password_resets' check/create OK.<br>";

    // 11. Settings (Added recently)
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        key_name VARCHAR(50) UNIQUE NOT NULL,
        value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'settings' check/create OK.<br>";

    // 12. Invoices (NEW)
    $sql = "CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        number VARCHAR(50),
        date DATE,
        amount DECIMAL(10,2),
        status ENUM('paid','unpaid'),
        pdf_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (client_id)
    )";
    $conn->exec($sql);
    echo "Table 'invoices' check/create OK.<br>";

    // 13. Notifications (NEW)
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        user_type ENUM('admin','client'),
        type VARCHAR(50),
        title VARCHAR(255),
        message TEXT,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )";
    $conn->exec($sql);
    echo "Table 'notifications' check/create OK.<br>";

    echo "Database setup completed successfully (FK constraints removed for compatibility).";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

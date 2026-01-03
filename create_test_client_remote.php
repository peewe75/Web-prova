<?php
// Script to create test client user on Hostinger
require_once __DIR__ . '/api/config.php';

try {
    $email = 'testclient@example.com';
    $password = 'password123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "✅ User already exists with ID: " . $existing['id'] . "<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
    } else {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute(['Test', 'Client', $email, $hashed_password, 'client']);
        $userId = $pdo->lastInsertId();
        
        echo "✅ Test client created successfully!<br>";
        echo "ID: $userId<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
        echo "<br><strong>You can now login at: <a href='login.html'>login.html</a></strong><br>";
    }
    
    echo "<br><hr><br>";
    echo "<strong style='color: red;'>⚠️ IMPORTANT: Delete this file immediately for security!</strong><br>";
    echo "File to delete: create_test_client_remote.php";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

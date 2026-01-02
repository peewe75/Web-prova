<?php
// FILE: api/config.php (PRODUCTION VERSION)
// Modify this file with your HOSTINGER database details

$host = 'localhost'; // Usually 'localhost' is correct for Hostinger, but check your dashboard
$db_name = 'u413244960_website'; // Verify this matches your Hostinger DB Name
$username = 'u413244960_user';   // CHANGE THIS to your Hostinger DB Username
$password = 'YOUR_DB_PASSWORD';  // CHANGE THIS to your Hostinger DB Password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, don't show specific errors to users for security
    // error_log($e->getMessage()); // Log error to file
    http_response_code(500);
    die(json_encode(["success" => false, "message" => "Database Connection Error"]));
}
?>

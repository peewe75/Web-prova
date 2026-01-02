<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Check Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    exit;
}

// 1. Validate Inputs
if (!isset($_POST['user_id']) || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or File']);
    exit;
}

$user_id = $_POST['user_id'];
$file = $_FILES['file'];

// 2. Validate File
$allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, JPG, PNG, DOCX allowed.']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) { // 5MB
    echo json_encode(['success' => false, 'message' => 'File too large (Max 5MB)']);
    exit;
}

// 3. Prepare Upload Directory
$target_dir = __DIR__ . "/../uploads/" . $user_id . "/";
if (!file_exists($target_dir)) {
    if (!mkdir($target_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Server Error: Cannot create upload directory']);
        exit;
    }
}

// 4. Move File
$filename = uniqid() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($file['name'])); // Sanitize name
$target_file = $target_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_file)) {
    
    // 5. Save to DB
    try {
        $stmt = $pdo->prepare("INSERT INTO documents (user_id, title, file_path, file_type) VALUES (?, ?, ?, ?)");
        $public_path = "uploads/$user_id/$filename"; // Relative path for frontend
        $stmt->execute([$user_id, $file['name'], $public_path, $file['type']]);

        // 6. Log Activity
        $logStmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, action_type, details) VALUES (?, 'Caricato Documento', 'upload', ?)");
        $logStmt->execute([$user_id, 'File: ' . $file['name']]);

        echo json_encode([
            'success' => true, 
            'message' => 'Upload Successful', 
            'file_path' => $public_path,
            'title' => $file['name'],
            'file_type' => $file['type'],
            'upload_date' => date('Y-m-d H:i:s')
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Upload failed (File write error)']);
}
?>

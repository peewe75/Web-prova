<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // Read JSON payload from N8N
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception("Invalid JSON Payload");
    }

    // Validate required fields
    if (empty($data['title'])) {
        throw new Exception("Title is required");
    }

    $title = $data['title'];
    $content = $data['content'] ?? ''; // Can be empty
    $category = $data['category'] ?? 'General';
    $status = 'suggestion'; // Always force as suggestion from external sources

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, category, content, status) VALUES (:title, :category, :content, :status)");
    $stmt->execute([
        ':title' => $title,
        ':category' => $category,
        ':content' => $content,
        ':status' => $status
    ]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'News received and saved as suggestion',
        'id' => $newId
    ]);

} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500); // Server Error
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>

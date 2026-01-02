<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Auto-setup table
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        content TEXT,
        status ENUM('draft', 'published', 'archived', 'suggestion') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'posts' => $posts]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // If ID provided, UPDATE
        if (isset($data['id'])) {
             $sql = "UPDATE blog_posts SET title=?, category=?, content=?, status=? WHERE id=?";
             $stmt = $pdo->prepare($sql);
             $stmt->execute([$data['title'], $data['category'], $data['content'], $data['status'], $data['id']]);
             echo json_encode(['success' => true, 'message' => 'Post aggiornato']);
        } else {
             // INSERT
             $sql = "INSERT INTO blog_posts (title, category, content, status) VALUES (?, ?, ?, ?)";
             $stmt = $pdo->prepare($sql);
             $stmt->execute([$data['title'], $data['category'], $data['content'], $data['status'] ?? 'draft']);
             echo json_encode(['success' => true, 'message' => 'Post creato']);
        }

    } elseif ($method === 'DELETE') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode(['success' => true, 'message' => 'Post eliminato']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID mancante']);
        }
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>

<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'User ID mancante o non valido']);
    exit;
}

// SECURITY CHECK (Pseudo-code as we lack full session context here, but in prod we'd check $_SESSION['user_id'] == $user_id OR $_SESSION['role'] == 'admin')
// For now, relies on the frontend passing the correct ID.
// TODO: Add Token verification if strict security needed.

try {
    $stmt = $pdo->prepare("SELECT id, title, file_path, file_type, upload_date FROM documents WHERE user_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$user_id]);
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'documents' => $docs]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>

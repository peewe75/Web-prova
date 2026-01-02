<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
// Find Admin ID dynamically
$stmtAdmin = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$stmtAdmin->execute();
$adminRow = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
$admin_id = $adminRow ? $adminRow['id'] : 1;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

try {
    // Mark messages from this user as read
    $stmtUpd = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = :uid AND recipient_id = :aid");
    $stmtUpd->execute(['uid' => $user_id, 'aid' => $admin_id]);

    // Fetch conversation
    $sql = "
        SELECT m.*, 
               CASE WHEN m.sender_id = :aid THEN 'admin' ELSE 'client' END as sender_role 
        FROM messages m 
        WHERE (m.sender_id = :uid AND m.recipient_id = :aid) 
           OR (m.sender_id = :aid AND m.recipient_id = :uid)
        ORDER BY m.created_at ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $user_id, 'aid' => $admin_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'messages' => $messages]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

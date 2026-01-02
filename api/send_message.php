<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$user_id = $data['user_id'];
$message = $data['message'];
$subject = $data['subject'] ?? 'Nuovo Messaggio';
$sender_role = $data['sender_role'] ?? 'client';

try {
    // Determine Sender/Recipient
    $sender_id = $user_id;

    // Find the Admin ID dynamically
    $stmtAdmin = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmtAdmin->execute();
    $adminRow = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
    $adminId = $adminRow ? $adminRow['id'] : 1; // Fallback to 1 if no admin found

    $recipient_id = $adminId; 
    
    if ($sender_role === 'admin') {
         $sender_id = $adminId; 
         $recipient_id = $user_id; 
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, content, is_read) VALUES (:sid, :rid, :msg, 0)");
    $stmt->execute([
        'sid' => $sender_id,
        'rid' => $recipient_id,
        'msg' => $message
    ]);

    $newId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Messaggio inviato',
        'data' => [
            'id' => $newId,
            'sender_id' => $sender_id,
            'recipient_id' => $recipient_id,
            'content' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>

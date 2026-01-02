<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // 1. Pending Appointments (Richiesto)
    $stmtApp = $pdo->query("
        SELECT a.id, a.created_at, u.first_name, u.last_name, u.id as user_id, 'appointment' as type 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.status = 'Richiesto' 
        ORDER BY a.created_at DESC
    ");
    $appointments = $stmtApp->fetchAll(PDO::FETCH_ASSOC);

    // 2. Recent Documents (Last 24h)
    $stmtDoc = $pdo->query("
        SELECT d.id, d.upload_date as created_at, d.title, d.file_path, u.first_name, u.last_name, u.id as user_id, 'document' as type 
        FROM documents d 
        JOIN users u ON d.user_id = u.id 
        ORDER BY d.upload_date DESC LIMIT 5
    ");
    $documents = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);

    // 3. Unread Messages
    $stmtMsg = $pdo->query("
        SELECT m.id, m.created_at, m.message as subject, u.first_name, u.last_name, u.id as user_id, 'message' as type 
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.sender_role = 'client' AND m.is_read = 0 
        ORDER BY m.created_at DESC
    ");
    $messages = $stmtMsg->fetchAll(PDO::FETCH_ASSOC);

    // Merge & Sort
    $all = array_merge($appointments, $documents, $messages);
    
    // Sort by created_at DESC
    usort($all, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    echo json_encode(['success' => true, 'notifications' => $all]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>

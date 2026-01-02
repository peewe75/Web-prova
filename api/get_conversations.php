<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // 1. Get all users who have exchanged messages with Admin (ID 1)
    // Or just all clients? Better: Clients with messages.
    
    // Find Admin ID dynamically
    $stmtAdmin = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmtAdmin->execute();
    $adminRow = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
    $admin_id = $adminRow ? $adminRow['id'] : 1;


    // Get all conversation partners (users who sent to admin OR received from admin)
    // We use a UNION to get all unique user IDs interacting with admin
    $sqlData = "
        SELECT DISTINCT 
            CASE 
                WHEN sender_id = :aid THEN recipient_id 
                ELSE sender_id 
            END as partner_id
        FROM messages 
        WHERE sender_id = :aid OR recipient_id = :aid
    ";
    
    $stmtData = $pdo->prepare($sqlData);
    $stmtData->execute(['aid' => $admin_id]);
    $partners = $stmtData->fetchAll(PDO::FETCH_COLUMN);
    
    $conversations = [];
    
    if (!empty($partners)) {
        // Now fetch details for each partner
        // Optimized loop or IN clause could be used. Loop is simpler for logic here.
        foreach ($partners as $pid) {
            // Get User Info
            $stmtUser = $pdo->prepare("SELECT id, first_name, last_name, email FROM users WHERE id = :uid");
            $stmtUser->execute(['uid' => $pid]);
            $u = $stmtUser->fetch(PDO::FETCH_ASSOC);
            
            if (!$u) continue; // Skip if user deleted
            
            // Get Last Message
            $stmtLast = $pdo->prepare("SELECT content, created_at FROM messages WHERE (sender_id = :pid AND recipient_id = :aid) OR (sender_id = :aid AND recipient_id = :pid) ORDER BY created_at DESC LIMIT 1");
            $stmtLast->execute(['pid' => $pid, 'aid' => $admin_id]);
            $lastMsg = $stmtLast->fetch(PDO::FETCH_ASSOC);
            
            // Count Unread
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = :pid AND recipient_id = :aid AND is_read = 0");
            $stmtCount->execute(['pid' => $pid, 'aid' => $admin_id]);
            $unread = $stmtCount->fetchColumn();
            
            $conversations[] = [
                'id' => $u['id'],
                'first_name' => $u['first_name'],
                'last_name' => $u['last_name'],
                'email' => $u['email'], // Added for clarity
                'last_message' => $lastMsg ? $lastMsg['content'] : '',
                'last_time' => $lastMsg ? $lastMsg['created_at'] : '',
                'unread_count' => $unread
            ];
        }
        
        // Sort by last_time DESC
        usort($conversations, function($a, $b) {
            return strtotime($b['last_time']) - strtotime($a['last_time']);
        });
    }

    echo json_encode(['success' => true, 'data' => $conversations]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

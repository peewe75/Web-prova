<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Get ALL leads for the leads table
    // Get ALL leads and clients
    $sql = "
    SELECT id, first_name, last_name, email, status, created_at, 'lead' as type, topic as role_or_topic FROM leads
    UNION ALL
    SELECT id, first_name, last_name, email, 'Active' as status, created_at, 'client' as type, role as role_or_topic FROM users WHERE role != 'admin'
    ORDER BY created_at DESC LIMIT 100";
    $stmt = $pdo->query($sql);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'leads' => $leads]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore server: ' . $e->getMessage()]);
}
?>

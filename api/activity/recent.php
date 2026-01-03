<?php
/**
 * API: Recent Activity Log
 * Restituisce log delle attività recenti
 */

header('Content-Type: application/json');
require_once '../config.php';

// Verifica autenticazione
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    
    // Query per ottenere attività recenti
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            u.first_name,
            u.last_name
        FROM activity_log a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Aggiungi icone basate sul tipo di attività
    foreach ($activities as &$activity) {
        $activity['icon'] = getActivityIcon($activity['action_type']);
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

function getActivityIcon($actionType) {
    $icons = [
        'login' => 'login',
        'logout' => 'logout',
        'create_case' => 'folder_open',
        'update_case' => 'edit',
        'delete_case' => 'delete',
        'upload_document' => 'upload_file',
        'send_message' => 'chat',
        'create_appointment' => 'event'
    ];
    return $icons[$actionType] ?? 'info';
}

<?php
/**
 * API: Client Activity
 * Attività recenti di un cliente
 */

header('Content-Type: application/json');
require_once '../../config.php';

// Verifica autenticazione
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

try {
    // Estrai client_id dall'URL
    $uri = $_SERVER['REQUEST_URI'];
    preg_match('/\/clients\/(\d+)\/activity\.php/', $uri, $matches);
    $clientId = isset($matches[1]) ? (int)$matches[1] : null;
    
    if (!$clientId) {
        echo json_encode(['success' => false, 'message' => 'ID cliente non valido']);
        exit;
    }
    
    // Verifica permessi
    if ($_SESSION['role'] !== 'admin' && $clientId != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
        exit;
    }
    
    // Query attività
    $stmt = $pdo->prepare("
        SELECT *
        FROM activity_log
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute([$clientId]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Aggiungi icone
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
        'upload_document' => 'upload_file',
        'send_message' => 'chat',
        'request_appointment' => 'event',
        'update_profile' => 'person'
    ];
    return $icons[$actionType] ?? 'info';
}

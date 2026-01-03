<?php
/**
 * API: Mark Notifications as Read
 * Segna notifiche come lette
 */

header('Content-Type: application/json');
require_once '../config.php';

// Verifica autenticazione
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['mark_all']) && $data['mark_all']) {
        // Segna tutte come lette
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE recipient_id = ? AND is_read = 0
        ");
        $stmt->execute([$_SESSION['user_id']]);
        
        $count = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => "$count notifiche segnate come lette"
        ]);
    } elseif (isset($data['notification_id'])) {
        // Segna singola notifica
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE id = ? AND recipient_id = ?
        ");
        $stmt->execute([$data['notification_id'], $_SESSION['user_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notifica segnata come letta'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Parametri non validi']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

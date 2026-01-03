<?php
/**
 * API: Send Message
 * Invia messaggio
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
    
    // Validazione
    if (empty($data['message'])) {
        echo json_encode(['success' => false, 'message' => 'Messaggio vuoto']);
        exit;
    }
    
    $senderId = $_SESSION['user_id'];
    
    // Determina destinatario
    // Se il mittente è un client, invia all'admin
    // Se il mittente è admin, invia al client specificato o al primo admin
    if ($_SESSION['role'] === 'client') {
        // Trova un admin
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        $recipientId = $admin ? $admin['id'] : null;
    } else {
        // Admin invia a client specificato
        $recipientId = isset($data['recipient_id']) ? (int)$data['recipient_id'] : null;
    }
    
    if (!$recipientId) {
        echo json_encode(['success' => false, 'message' => 'Destinatario non trovato']);
        exit;
    }
    
    // Inserisci messaggio
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, recipient_id, message, is_read, created_at)
        VALUES (?, ?, ?, 0, NOW())
    ");
    
    $stmt->execute([
        $senderId,
        $recipientId,
        $data['message']
    ]);
    
    $messageId = $pdo->lastInsertId();
    
    // Log attività
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action_type, description, created_at)
        VALUES (?, 'send_message', 'Inviato messaggio', NOW())
    ");
    $stmt->execute([$senderId]);
    
    echo json_encode([
        'success' => true,
        'message_id' => $messageId,
        'message' => 'Messaggio inviato'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

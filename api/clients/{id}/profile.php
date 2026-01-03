<?php
/**
 * API: Update Client Profile
 * Aggiorna profilo cliente
 */

header('Content-Type: application/json');
require_once '../../config.php';

// Verifica autenticazione
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

// Solo PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

try {
    // Estrai client_id dall'URL
    $uri = $_SERVER['REQUEST_URI'];
    preg_match('/\/clients\/(\d+)\/profile\.php/', $uri, $matches);
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
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Costruisci query di aggiornamento
    $updates = [];
    $params = [];
    
    if (isset($data['first_name'])) {
        $updates[] = "first_name = ?";
        $params[] = $data['first_name'];
    }
    
    if (isset($data['last_name'])) {
        $updates[] = "last_name = ?";
        $params[] = $data['last_name'];
    }
    
    if (isset($data['password']) && !empty($data['password'])) {
        $updates[] = "password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'Nessun campo da aggiornare']);
        exit;
    }
    
    $params[] = $clientId;
    
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Recupera utente aggiornato
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE id = ?");
    $stmt->execute([$clientId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Log attività
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action_type, description, created_at)
        VALUES (?, 'update_profile', 'Profilo aggiornato', NOW())
    ");
    $stmt->execute([$clientId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Profilo aggiornato con successo',
        'user' => $user
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

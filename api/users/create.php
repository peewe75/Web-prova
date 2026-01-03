<?php
/**
 * API: Create User
 * Crea nuovo utente
 */

header('Content-Type: application/json');
require_once '../config.php';

// Verifica autenticazione admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    if (empty($data['email']) || empty($data['password']) || empty($data['first_name'])) {
        echo json_encode(['success' => false, 'message' => 'Campi obbligatori mancanti']);
        exit;
    }
    
    // Verifica email univoca
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email già registrata']);
        exit;
    }
    
    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Inserisci utente
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, password, role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $data['first_name'],
        $data['last_name'] ?? '',
        $data['email'],
        $passwordHash,
        $data['role'] ?? 'client'
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Log attività
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action_type, description, created_at)
        VALUES (?, 'create_user', ?, NOW())
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        "Creato nuovo utente: {$data['first_name']} {$data['last_name']}"
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Utente creato con successo',
        'user_id' => $userId
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

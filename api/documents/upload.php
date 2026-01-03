<?php
/**
 * API: Upload Document
 * Upload documenti per clienti
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
    // Verifica file
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'message' => 'Nessun file caricato']);
        exit;
    }
    
    $file = $_FILES['file'];
    $clientId = isset($_POST['client_id']) ? (int)$_POST['client_id'] : $_SESSION['user_id'];
    
    // Verifica permessi
    if ($_SESSION['role'] !== 'admin' && $clientId != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
        exit;
    }
    
    // Validazione tipo file
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png'
    ];
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Tipo file non supportato']);
        exit;
    }
    
    // Validazione dimensione (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File troppo grande (max 10MB)']);
        exit;
    }
    
    // Crea directory uploads se non esiste
    $uploadDir = dirname(__DIR__, 2) . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Genera nome file univoco
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Sposta file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode(['success' => false, 'message' => 'Errore durante l\'upload']);
        exit;
    }
    
    // Salva nel database
    $stmt = $pdo->prepare("
        INSERT INTO documents (client_id, filename, original_filename, file_type, file_size, uploaded_at, uploaded_by)
        VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    
    $stmt->execute([
        $clientId,
        $filename,
        $file['name'],
        $file['type'],
        $file['size'],
        $_SESSION['user_id']
    ]);
    
    $documentId = $pdo->lastInsertId();
    
    // Log attività
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action_type, description, created_at)
        VALUES (?, 'upload_document', ?, NOW())
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        "Caricato documento: {$file['name']}"
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Documento caricato con successo',
        'document_id' => $documentId,
        'filename' => $filename
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore: ' . $e->getMessage()
    ]);
}

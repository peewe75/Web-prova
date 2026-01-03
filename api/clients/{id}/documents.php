<?php
/**
 * API: Client Documents
 * Lista documenti di un cliente
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
    preg_match('/\/clients\/(\d+)\/documents\.php/', $uri, $matches);
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
    
    // Query documenti
    $stmt = $pdo->prepare("
        SELECT 
            d.*,
            u.first_name,
            u.last_name
        FROM documents d
        LEFT JOIN users u ON d.uploaded_by = u.id
        WHERE d.client_id = ?
        ORDER BY d.uploaded_at DESC
    ");
    
    $stmt->execute([$clientId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'documents' => $documents
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

<?php
/**
 * API: List Cases
 * Lista pratiche con paginazione
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
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    // Query base
    $sql = "
        SELECT 
            c.*,
            u.first_name,
            u.last_name,
            CONCAT(u.first_name, ' ', u.last_name) as client_name
        FROM cases c
        LEFT JOIN users u ON c.client_id = u.id
    ";
    
    $params = [];
    
    // Se non admin, mostra solo le proprie pratiche
    if ($_SESSION['role'] !== 'admin') {
        $sql .= " WHERE c.client_id = ?";
        $params[] = $_SESSION['user_id'];
    }
    
    $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'cases' => $cases,
        'offset' => $offset,
        'limit' => $limit
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

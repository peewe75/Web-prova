<?php
/**
 * API: Case Detail
 * GET/PUT/DELETE per singola pratica
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
    // Estrai case_id dall'URL
    $uri = $_SERVER['REQUEST_URI'];
    preg_match('/\/cases\/(\d+)\.php/', $uri, $matches);
    $caseId = isset($matches[1]) ? (int)$matches[1] : null;
    
    if (!$caseId) {
        echo json_encode(['success' => false, 'message' => 'ID pratica non valido']);
        exit;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Recupera dettagli pratica
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                u.first_name,
                u.last_name,
                CONCAT(u.first_name, ' ', u.last_name) as client_name
            FROM cases c
            LEFT JOIN users u ON c.client_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$case) {
            echo json_encode(['success' => false, 'message' => 'Pratica non trovata']);
            exit;
        }
        
        // Verifica permessi
        if ($_SESSION['role'] !== 'admin' && $case['client_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'case' => $case
        ]);
        
    } elseif ($method === 'PUT') {
        // Aggiorna pratica (solo admin)
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $updates = [];
        $params = [];
        
        if (isset($data['type'])) {
            $updates[] = "type = ?";
            $params[] = $data['type'];
        }
        
        if (isset($data['status'])) {
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }
        
        if (isset($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
        }
        
        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'Nessun campo da aggiornare']);
            exit;
        }
        
        $params[] = $caseId;
        
        $sql = "UPDATE cases SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'message' => 'Pratica aggiornata'
        ]);
        
    } elseif ($method === 'DELETE') {
        // Elimina pratica (solo admin)
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM cases WHERE id = ?");
        $stmt->execute([$caseId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Pratica eliminata'
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Metodo non supportato']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

<?php
/**
 * API: Search Cases
 * Ricerca pratiche con filtri
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
    // Parametri di ricerca
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    
    // Costruisci query
    $sql = "
        SELECT 
            c.*,
            u.first_name,
            u.last_name,
            CONCAT(u.first_name, ' ', u.last_name) as client_name
        FROM cases c
        LEFT JOIN users u ON c.client_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Filtro ricerca testuale
    if (!empty($search)) {
        $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR c.type LIKE ? OR c.description LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Filtro ruolo (se admin può vedere tutto, se client solo le sue)
    if ($_SESSION['role'] !== 'admin') {
        $sql .= " AND c.client_id = ?";
        $params[] = $_SESSION['user_id'];
    } elseif (!empty($role) && $role !== 'all') {
        $sql .= " AND u.role = ?";
        $params[] = $role;
    }
    
    // Filtro stato
    if (!empty($status) && $status !== 'all') {
        $sql .= " AND c.status = ?";
        $params[] = $status;
    }
    
    // Filtro data
    if (!empty($date) && $date !== 'all') {
        switch ($date) {
            case 'today':
                $sql .= " AND DATE(c.created_at) = CURDATE()";
                break;
            case 'month':
                $sql .= " AND MONTH(c.created_at) = MONTH(CURDATE()) AND YEAR(c.created_at) = YEAR(CURDATE())";
                break;
        }
    }
    
    $sql .= " ORDER BY c.created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'cases' => $cases,
        'count' => count($cases)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

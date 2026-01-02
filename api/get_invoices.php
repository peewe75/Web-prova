<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
    
    if (!$client_id) {
        throw new Exception("ID cliente mancante");
    }
    
    // Fetch invoices
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE client_id = ? ORDER BY date DESC");
    $stmt->execute([$client_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $invoices]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

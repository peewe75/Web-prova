<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['client_id']) || !isset($input['content'])) {
            throw new Exception("Dati mancanti");
        }
        
        if (strlen(trim($input['content'])) === 0) {
            throw new Exception("Nota vuota");
        }

        $stmt = $pdo->prepare("INSERT INTO legal_notes (client_id, content) VALUES (:uid, :content)");
        $stmt->execute([
            'uid' => $input['client_id'],
            'content' => $input['content']
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Nota salvata con successo',
            'id' => $pdo->lastInsertId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } elseif ($method === 'GET') {
        $client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
        
        if (!$client_id) {
             throw new Exception("ID cliente mancante");
        }
        
        $stmt = $pdo->prepare("SELECT * FROM legal_notes WHERE client_id = ? ORDER BY created_at DESC");
        $stmt->execute([$client_id]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $notes]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

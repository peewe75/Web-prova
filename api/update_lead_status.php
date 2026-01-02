<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['lead_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or Status']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE leads SET status = :status WHERE id = :id");
    $execute = $stmt->execute([
        'status' => $data['status'],
        'id' => $data['lead_id']
    ]);

    if ($execute) {
        echo json_encode(['success' => true, 'message' => 'Stato aggiornato']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore aggiornamento']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>

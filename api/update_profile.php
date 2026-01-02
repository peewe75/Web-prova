<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID mancante']);
    exit;
}

try {
    $userId = $data['user_id'];
    $updates = [];
    $params = ['id' => $userId];

    if (isset($data['first_name'])) {
        $updates[] = "first_name = :fname";
        $params['fname'] = $data['first_name'];
    }
    if (isset($data['last_name'])) {
        $updates[] = "last_name = :lname";
        $params['lname'] = $data['last_name'];
    }
    
    // Password Update
    if (isset($data['password']) && !empty($data['password'])) {
        $updates[] = "password_hash = :pass";
        $params['pass'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    // Address/Phone could be added if schema supports it
    // For now, only basic fields

    if (empty($updates)) {
        echo json_encode(['success' => true, 'message' => 'Nessuna modifica rilevata']);
        exit;
    }

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Profilo aggiornato con successo']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>

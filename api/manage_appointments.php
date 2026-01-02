<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // LIST APPOINTMENTS
        // ?user_id=123 (Client view) OR ?all=true (Admin view)
        
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $filter = isset($_GET['filter']) ? $_GET['filter'] : null;
        $all = isset($_GET['all']) ? $_GET['all'] : false;

        $sql = "SELECT ar.*, u.first_name, u.last_name, u.email 
                FROM appointment_requests ar 
                LEFT JOIN users u ON ar.client_id = u.id 
                WHERE 1=1";
        
        $params = [];
        if ($user_id) {
            $sql .= " AND ar.client_id = :uid";
            $params['uid'] = $user_id;
        }
        if ($filter === 'pending') {
            $sql .= " AND ar.status = 'pending'";
        }
        
        $sql .= " ORDER BY ar.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        // Create Request
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['client_id']) || !isset($input['date']) || !isset($input['type'])) {
            throw new Exception("Dati mancanti");
        }

        // date format expected: YYYY-MM-DD
        // time format expected: HH:MM
        
        // Handle combined datetime if passed
        $requested_date = $input['date'];
        $requested_time = isset($input['time']) ? $input['time'] : '09:00'; 
        
        // If 'date' contains time (T or space), split it
        if (strpos($requested_date, 'T') !== false || strpos($requested_date, ' ') !== false) {
             $dt = new DateTime($requested_date);
             $requested_date = $dt->format('Y-m-d');
             $requested_time = $dt->format('H:i');
        }

        $stmt = $pdo->prepare("INSERT INTO appointment_requests (client_id, requested_date, requested_time, type, notes, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $input['client_id'], 
            $requested_date, 
            $requested_time, 
            $input['type'], 
            $input['notes'] ?? ''
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Richiesta inviata', 'id' => $pdo->lastInsertId()]);

    } elseif ($method === 'PUT') {
        // Update Status
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id']) || !isset($input['status'])) {
             throw new Exception("ID o status mancante");
        }

        $id = $input['id'];
        $status = $input['status']; // accepted, rejected

        $stmt = $pdo->prepare("UPDATE appointment_requests SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // If accepted, add to main calendar (appointments table)
        if ($status === 'accepted') {
            // Get request details
            $req = $pdo->query("SELECT * FROM appointment_requests WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            if ($req) {
                 // Combine date time
                 $fullDate = $req['requested_date'] . ' ' . $req['requested_time'];
                 $stmtIns = $pdo->prepare("INSERT INTO appointments (user_id, date, type, status, notes) VALUES (?, ?, ?, 'Confermato', ?)");
                 $stmtIns->execute([$req['client_id'], $fullDate, $req['type'], $req['notes']]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Stato aggiornato']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

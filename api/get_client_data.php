<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['token']) || !isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $data['user_id'];

try {
    // Fetch User Details
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, role, created_at FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Fetch Appointments (Confirmed)
    $stmtApp = $pdo->prepare("SELECT id, date, type, status, notes FROM appointments WHERE user_id = :id");
    $stmtApp->execute(['id' => $user_id]);
    $confirmed_apps = $stmtApp->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Appointment Requests (Pending/Rejected)
    $stmtReq = $pdo->prepare("SELECT id, requested_date as date_part, requested_time as time_part, type, status, notes FROM appointment_requests WHERE client_id = :id");
    $stmtReq->execute(['id' => $user_id]);
    $requests = $stmtReq->fetchAll(PDO::FETCH_ASSOC);

    // Normalize Requests to match Appointments structure
    $normalized_requests = [];
    foreach ($requests as $req) {
        // combine date and time if needed, or just use date_part if legacy
        $full_date = $req['date_part'] . ' ' . $req['time_part'];
        $normalized_requests[] = [
            'id' => $req['id'],
            'date' => $full_date,
            'type' => $req['type'],
            'status' => $req['status'], // 'pending' etc
            'notes' => $req['notes']
        ];
    }

    // Merge and Sort
    $appointments = array_merge($confirmed_apps, $normalized_requests);
    usort($appointments, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']); // DESC
    });

    // Fetch Documents
    $stmtDoc = $pdo->prepare("SELECT * FROM documents WHERE user_id = :id ORDER BY upload_date DESC");
    $stmtDoc->execute(['id' => $user_id]);
    $documents = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Stats (Active vs Closed leads)
    $stmtStats = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN status != 'Chiuso' THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN status = 'Chiuso' THEN 1 ELSE 0 END) as closed_count
        FROM leads 
        WHERE user_id = :id
    ");
    $stmtStats->execute(['id' => $user_id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // Fetch Messages (New) - Corrected for Schema
    $stmtMsgs = $pdo->prepare("
        SELECT m.*, 
               CASE WHEN m.sender_id = :id THEN 'client' ELSE 'admin' END as sender_role 
        FROM messages m 
        WHERE m.sender_id = :id OR m.recipient_id = :id 
        ORDER BY m.created_at ASC
    ");
    $stmtMsgs->execute(['id' => $user_id]);
    $messages = $stmtMsgs->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'user' => $user, 'appointments' => $appointments, 'documents' => $documents, 'stats' => $stats, 'messages' => $messages]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>

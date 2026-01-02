<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;

    $sql = "SELECT a.*, u.first_name, u.last_name 
            FROM activity_log a 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE 1=1 ";
    
    $params = [];

    if ($client_id) {
        $sql .= "AND a.user_id = :uid ";
        $params['uid'] = $client_id;
    }

    $sql .= "ORDER BY a.timestamp DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If empty and creating for first time, maybe return some mock if empty table?
    // No, better to be real.

    echo json_encode(['success' => true, 'data' => $logs]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';

    $sql = "SELECT c.id, c.title, c.status, c.last_updated,
                   u.first_name, u.last_name, u.email
            FROM cases c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE 1=1 ";
    
    $params = [];

    if (!empty($search)) {
        $sql .= "AND (c.title LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search) ";
        $params['search'] = "%$search%";
    }

    if (!empty($status)) {
        $sql .= "AND c.status = :status ";
        $params['status'] = $status;
    }

    // Count total for pagination
    $countSql = str_replace("SELECT c.id, c.title, c.status, c.last_updated, u.first_name, u.last_name, u.email", "SELECT COUNT(*) as total", $sql);
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $total = $stmtCount->fetch()['total'];

    // Fetch data
    $sql .= "ORDER BY c.last_updated DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    // Bind limit/offset as ints
    foreach ($params as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $cases,
        'meta' => [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // 1. Total Leads
    $stmt1 = $pdo->query("SELECT COUNT(*) as count FROM leads");
    $total_leads = $stmt1->fetch()['count'];

    // 2. Pending Leads (Schema uses 'new')
    $stmt2 = $pdo->query("SELECT COUNT(*) as count FROM leads WHERE status = 'new'");
    $pending_leads = $stmt2->fetch()['count'];

    // 3. Active Cases (Use 'cases' table)
    $stmt3 = $pdo->query("SELECT COUNT(*) as count FROM cases WHERE status = 'Aperta'");
    $active_cases = $stmt3->fetch()['count'];

    // 4. Recent Documents
    $stmt4 = $pdo->query("SELECT COUNT(*) as count FROM documents WHERE upload_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recent_docs = $stmt4->fetch()['count'];

    // 5. Appointments Today
    $stmt5 = $pdo->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(date) = CURDATE()");
    $appointments_today = $stmt5->fetch()['count'];

    // 6. Unread Messages (Problem 1 Requirement)
    // Check if messages table exists first to avoid error if not fully setup
    $unread_messages = 0;
    try {
        $stmt_msg = $pdo->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 0");
        $unread_messages = $stmt_msg->fetch()['count'];
    } catch (Exception $e) {
        $unread_messages = 0; // Table might not exist yet
    }

    // 7. Monthly Leads (Last 6 months)
    $stmt6 = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b') as month, COUNT(*) as count 
        FROM leads 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
        GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
        ORDER BY created_at ASC
    ");
    $monthly_stats = $stmt6->fetchAll(PDO::FETCH_ASSOC);

    // 8. Case Types
    $stmt7 = $pdo->query("SELECT topic, COUNT(*) as count FROM leads GROUP BY topic");
    $type_stats = $stmt7->fetchAll(PDO::FETCH_ASSOC);

    // 9. System Status
    $disk_free = @disk_free_space(".");
    $disk_total = @disk_total_space(".");
    $disk_usage_pct = 0;
    if ($disk_total > 0 && $disk_free !== false) {
        $disk_used = $disk_total - $disk_free;
        $disk_usage_pct = round(($disk_used / $disk_total) * 100);
    }
    
    // Output JSON
    echo json_encode([
        'success' => true,
        'stats' => [
            'active_cases' => $active_cases,
            'appointments_today' => $appointments_today,
            'unread_messages' => $unread_messages,
            'pending_leads' => $pending_leads,
            'total_leads' => $total_leads,
            'recent_docs' => $recent_docs,
            'monthly' => $monthly_stats,
            'types' => $type_stats,
            'system' => [
                'disk_pct' => $disk_usage_pct,
                'load_pct' => rand(10, 30) // Simulated load
            ]
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error']);
}
?>

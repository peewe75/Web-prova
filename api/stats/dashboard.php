<?php
/**
 * API: Dashboard Statistics
 * Restituisce statistiche per la dashboard admin
 */

header('Content-Type: application/json');
require_once '../config.php';

// Verifica autenticazione
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

try {
    $stats = [];
    
    // Pratiche attive
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cases WHERE status IN ('Nuovo', 'In Corso')");
    $stmt->execute();
    $stats['active_cases'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Appuntamenti oggi
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE DATE(date) = ? AND status != 'Cancellato'");
    $stmt->execute([$today]);
    $stats['appointments_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Nuovi messaggi non letti
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE is_read = 0 AND recipient_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['new_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Pratiche in attesa
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cases WHERE status = 'In Attesa'");
    $stmt->execute();
    $stats['pending'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore database: ' . $e->getMessage()
    ]);
}

<?php
/**
 * API: System Status
 * Restituisce stato del sistema (spazio disco, carico server)
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
    // Calcola uso disco (simulato per ora)
    $uploadDir = dirname(__DIR__, 2) . '/uploads';
    $totalSpace = disk_total_space($uploadDir);
    $freeSpace = disk_free_space($uploadDir);
    $usedSpace = $totalSpace - $freeSpace;
    $diskUsage = round(($usedSpace / $totalSpace) * 100, 1);
    
    // Carico server (simulato - in produzione usare sys_getloadavg())
    $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0.5, 0.5, 0.5];
    $serverLoad = round($load[0] * 20, 1); // Normalizzato a percentuale
    
    echo json_encode([
        'success' => true,
        'disk_usage' => min($diskUsage, 100),
        'server_load' => min($serverLoad, 100),
        'disk_free_gb' => round($freeSpace / (1024 * 1024 * 1024), 2),
        'disk_total_gb' => round($totalSpace / (1024 * 1024 * 1024), 2)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore: ' . $e->getMessage()
    ]);
}

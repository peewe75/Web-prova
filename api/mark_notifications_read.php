<?php
require_once 'config.php';

header('Content-Type: application/json');

// Stub: Just returns success for now as we don't have a notifications table with 'read' status yet.
// In a full implementation, this would UPDATE notifications SET is_read = 1 WHERE user_id = ...

echo json_encode(['success' => true]);
?>

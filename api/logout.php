<?php
require_once __DIR__ . '/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  respond(405, ['success' => false, 'message' => 'Method not allowed']);
}

$token = bearer_token();
if (!$token) respond(401, ['success' => false, 'message' => 'Token mancante']);

$pdo = db();
$tokenHash = hash_token($token);
$stmt = $pdo->prepare("UPDATE sessions SET revoked_at = NOW() WHERE token_hash = ? AND revoked_at IS NULL");
$stmt->execute([$tokenHash]);

audit('LOGOUT', 'sessions', null, []);

respond(200, ['success' => true]);

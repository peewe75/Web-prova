<?php
// seed_admin.php — ESEGUI UNA SOLA VOLTA, poi cancellalo!
// 1) Carica su /api/
// 2) Apri https://api.tuodominio.it/seed_admin.php?key=CAMBIA_QUESTA_CHIAVE
// 3) Prendi le credenziali e poi elimina questo file o mettilo offline.

require_once __DIR__ . '/bootstrap.php';

$key = $_GET['key'] ?? '';
if ($key !== 'CAMBIA_QUESTA_CHIAVE') {
  respond(403, ['success' => false, 'message' => 'Forbidden']);
}

$pdo = db();
$email = 'admin@studiobcs.it';
$plain = 'BCS-Admin-2026!';
$hash = password_hash($plain, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$exists = $stmt->fetch();

if (!$exists) {
  $ins = $pdo->prepare("INSERT INTO users (id, email, password_hash, role, first_name, last_name, created_at) VALUES (UUID(), ?, ?, 'ADMIN', 'BCS', 'Admin', NOW())");
  $ins->execute([$email, $hash]);
}

respond(200, [
  'success' => true,
  'email' => $email,
  'password' => $plain,
  'note' => 'Cambia la password e cancella seed_admin.php.',
]);

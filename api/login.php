<?php
require_once __DIR__ . '/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  respond(405, ['success' => false, 'message' => 'Method not allowed']);
}

$body = json_body();
$email = strtolower(trim($body['email'] ?? ''));
$pass  = (string)($body['password'] ?? '');

if ($email === '' || $pass === '') {
  respond(400, ['success' => false, 'message' => 'Email e password obbligatorie']);
}

$pdo = db();
$stmt = $pdo->prepare("SELECT id, email, password_hash, role, first_name, last_name FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
  audit('LOGIN_FAILED', 'users', $user['id'] ?? null, ['email' => $email]);
  respond(401, ['success' => false, 'message' => 'Credenziali non valide']);
}

$token = bin2hex(random_bytes(32));
$tokenHash = hash_token($token);

$ttlDays = (int)(($config['token_ttl_days'] ?? 7));
$stmt2 = $pdo->prepare("
  INSERT INTO sessions (user_id, token_hash, created_at, expires_at)
  VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))
");
$stmt2->execute([$user['id'], $tokenHash, $ttlDays]);

audit('LOGIN_OK', 'users', $user['id'], []);

respond(200, [
  'success' => true,
  'token' => $token,
  'user' => [
    'id' => $user['id'],
    'email' => $user['email'],
    'role' => $user['role'],
    'first_name' => $user['first_name'],
    'last_name'  => $user['last_name'],
  ],
]);

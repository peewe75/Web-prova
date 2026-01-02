<?php
// utils.php — JSON helpers + auth
declare(strict_types=1);

function json_body(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

function bearer_token(): ?string {
  $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!$h) return null;
  if (preg_match('/^Bearer\s+(.+)$/i', $h, $m)) return trim($m[1]);
  return null;
}

function hash_token(string $token): string {
  // Non salvare il token in chiaro
  return hash('sha256', $token);
}

function auth_user(): ?array {
  $token = bearer_token();
  if (!$token) return null;

  $pdo = db();
  $tokenHash = hash_token($token);

  $stmt = $pdo->prepare("
    SELECT u.id, u.email, u.role, u.first_name, u.last_name
    FROM sessions s
    JOIN users u ON u.id = s.user_id
    WHERE s.token_hash = ? AND s.expires_at > NOW() AND s.revoked_at IS NULL
    LIMIT 1
  ");
  $stmt->execute([$tokenHash]);
  $u = $stmt->fetch();
  return $u ?: null;
}

function audit(string $action, string $entityType = '', ?string $entityId = null, array $meta = []): void {
  try {
    $pdo = db();
    $user = auth_user();
    $actorRole = $user['role'] ?? 'ANON';
    $actorId = $user['id'] ?? null;

    $stmt = $pdo->prepare("
      INSERT INTO audit_logs (actor_role, actor_id, action, entity_type, entity_id, metadata_json, created_at)
      VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
      $actorRole,
      $actorId,
      $action,
      $entityType,
      $entityId,
      json_encode($meta, JSON_UNESCAPED_UNICODE),
    ]);
  } catch (Throwable $e) {
    // Non bloccare la risposta se audit fallisce
  }
}

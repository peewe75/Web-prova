<?php
// bootstrap.php — include in tutti gli endpoint
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Carica config
$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Config mancante: crea config.php (da config.sample.php)']);
  exit;
}
$config = require $configPath;

// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = $config['allowed_origins'] ?? null;

if ($allowed === null) {
  // Deriva dominio base dall'origin: consente solo app./admin. del medesimo base-domain
  if ($origin) {
    $host = parse_url($origin, PHP_URL_HOST);
    if ($host) {
      $base = preg_replace('/^(app|admin)\./', '', $host);
      $allowed = [
        'https://app.' . $base,
        'https://admin.' . $base,
      ];
    } else {
      $allowed = [];
    }
  } else {
    $allowed = [];
  }
}

if ($origin && in_array($origin, $allowed, true)) {
  header("Access-Control-Allow-Origin: {$origin}");
  header('Vary: Origin');
  // Token-based auth -> credentials non necessarie
  header('Access-Control-Allow-Credentials: false');
}
header('Access-Control-Allow-Methods: GET,POST,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

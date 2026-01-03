<?php
/**
 * GitHub Webhook Auto-Deploy Script
 * 
 * Questo script riceve le notifiche da GitHub e esegue automaticamente git pull
 * quando viene fatto un push sul repository.
 */

// Configurazione
define('SECRET_TOKEN', 'CAMBIA_QUESTO_TOKEN_CON_UNO_SICURO_' . bin2hex(random_bytes(16)));
define('REPO_PATH', __DIR__);
define('BRANCH', 'main');
define('LOG_FILE', __DIR__ . '/deploy.log');

// Funzione per loggare le operazioni
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
}

// Verifica che sia una richiesta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Leggi il payload
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Verifica la firma GitHub (se configurata)
if (isset($headers['X-Hub-Signature-256'])) {
    $signature = 'sha256=' . hash_hmac('sha256', $payload, SECRET_TOKEN);
    if (!hash_equals($signature, $headers['X-Hub-Signature-256'])) {
        logMessage('ERROR: Invalid signature');
        http_response_code(403);
        die('Forbidden - Invalid signature');
    }
}

// Decodifica il payload
$data = json_decode($payload, true);

// Verifica che sia un push event
if (!isset($headers['X-GitHub-Event']) || $headers['X-GitHub-Event'] !== 'push') {
    logMessage('INFO: Not a push event, ignoring');
    http_response_code(200);
    die('OK - Not a push event');
}

// Verifica che sia il branch corretto
if (isset($data['ref']) && $data['ref'] !== 'refs/heads/' . BRANCH) {
    logMessage('INFO: Push to different branch, ignoring');
    http_response_code(200);
    die('OK - Different branch');
}

logMessage('INFO: Received push event from GitHub');

// Cambia directory al repository
chdir(REPO_PATH);

// Esegui git pull
$output = [];
$returnCode = 0;

// 1. Git fetch
exec('git fetch origin ' . BRANCH . ' 2>&1', $output, $returnCode);
if ($returnCode !== 0) {
    logMessage('ERROR: Git fetch failed - ' . implode("\n", $output));
    http_response_code(500);
    die('Git fetch failed');
}
logMessage('SUCCESS: Git fetch completed');

// 2. Git reset (per sicurezza, forza l'aggiornamento)
exec('git reset --hard origin/' . BRANCH . ' 2>&1', $output, $returnCode);
if ($returnCode !== 0) {
    logMessage('ERROR: Git reset failed - ' . implode("\n", $output));
    http_response_code(500);
    die('Git reset failed');
}
logMessage('SUCCESS: Git reset completed');

// 3. Git clean (rimuove file non tracciati)
exec('git clean -fd 2>&1', $output, $returnCode);
logMessage('INFO: Git clean completed');

// Log del commit
if (isset($data['head_commit'])) {
    $commit = $data['head_commit'];
    logMessage('DEPLOY: ' . $commit['message'] . ' by ' . $commit['author']['name']);
}

logMessage('SUCCESS: Deploy completed successfully');

// Risposta
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Deploy completed successfully',
    'timestamp' => date('Y-m-d H:i:s')
]);

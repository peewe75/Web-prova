<?php
require_once __DIR__ . '/bootstrap.php';

$user = auth_user();
if (!$user) respond(401, ['success' => false, 'message' => 'Non autenticato']);

respond(200, ['success' => true, 'user' => $user]);

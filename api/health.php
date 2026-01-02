<?php
require_once __DIR__ . '/bootstrap.php';
respond(200, ['success' => true, 'service' => 'BCS API', 'time' => date('c')]);

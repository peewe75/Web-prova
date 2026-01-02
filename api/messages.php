<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Includi configurazione database
require_once dirname(__DIR__) . '/config/db.php';

// Recupera client_id da GET
$client_id = $_GET

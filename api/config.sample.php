<?php
// Copia questo file in config.php e compila i valori.

return [
  // DB (MySQL Hostinger)
  'db' => [
    'host' => 'localhost',
    'name' => 'DB_NAME',
    'user' => 'DB_USER',
    'pass' => 'DB_PASS',
    'charset' => 'utf8mb4',
  ],

  // Security
  'token_ttl_days' => 7,

  // CORS: se vuoi forzare una lista fissa, compila questo array.
//  'allowed_origins' => [
//    'https://app.tuodominio.it',
//    'https://admin.tuodominio.it',
//  ],
];

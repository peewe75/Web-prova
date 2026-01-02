<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        content TEXT,
        status ENUM('draft', 'published', 'archived', 'suggestion') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Tabella blog_posts creata o già esistente.";
} catch (PDOException $e) {
    echo "Errore creazione tabella: " . $e->getMessage();
}
?>

<?php
require_once 'config.php';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update ENUM definition to include 'suggestion' and 'archived'
    $sql = "ALTER TABLE blog_posts MODIFY COLUMN status ENUM('draft', 'published', 'archived', 'suggestion') DEFAULT 'draft'";
    $conn->exec($sql);
    
    echo "<h1>✅ Schema Blog Aggiornato</h1>";
    echo "<p>La colonna 'status' ora supporta: draft, published, archived, suggestion.</p>";

} catch(PDOException $e) {
    echo "<h1>Errore</h1>" . $e->getMessage();
}
?>

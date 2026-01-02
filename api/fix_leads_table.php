<?php
require_once 'config.php';

try {
    // Re-create connection (using config.php variables)
    // Note: config.php creates $pdo object, so we can use that directly if it's there, 
    // but setup_full_db.php creates a new connection $conn.
    // Let's use $pdo from config.php if available, or create new.
    
    if (!isset($pdo)) {
        $host = 'localhost'; // Fallback or assume config.php sets variables
        // We assume config.php sets $host, $db_name, $username, $password OR creates $pdo
        // Looking at setup_full_db.php, it uses $host, $db_name...
        // Let's assume config.php exposes these variables or $pdo.
        // Actually, let's just use what setup_full_db.php does.
        $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } else {
        $conn = $pdo;
    }

    echo "<h3>Campagna di Aggiornamento Database</h3>";

    // 1. UPDATE LEADS TABLE
    echo "Check 1: Aggiornamento tabella 'leads'...<br>";
    try {
        // Try to add the column. If it exists, it will throw an error, which we catch.
        $sql = "ALTER TABLE leads ADD COLUMN user_id INT NULL AFTER id";
        $conn->exec($sql);
        echo "<span style='color:green'>Successo: Colonna 'user_id' aggiunta alla tabella leads.</span><br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false || $e->getCode() == '42S21') {
             echo "<span style='color:orange'>Info: La colonna 'user_id' esiste già.</span><br>";
        } else {
             echo "<span style='color:red'>Errore: " . $e->getMessage() . "</span><br>";
        }
    }

    // 2. ADD INDEX
    echo "<br>Check 2: Aggiunta Indice...<br>";
    try {
        $sql = "CREATE INDEX idx_user_id ON leads(user_id)";
        $conn->exec($sql);
        echo "<span style='color:green'>Successo: Indice creato per user_id.</span><br>";
    } catch (PDOException $e) {
         echo "<span style='color:orange'>Info: Indice probabilmente già presente o errore: " . $e->getMessage() . "</span><br>";
    }

    echo "<br><b>Operazione completata. Riprova a inviare il form.</b>";

} catch(PDOException $e) {
    echo "<span style='color:red'>Errore di connessione o critico: " . $e->getMessage() . "</span>";
}
?>

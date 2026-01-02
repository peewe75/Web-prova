<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<body style='font-family: sans-serif; padding: 2rem; line-height: 1.6;'>";
    echo "<h1>🛠️ Strumento di Riparazione Database & Admin</h1>";

    // ---------------------------------------------------------
    // 1. FIX TABELLA MESSAGGI
    // ---------------------------------------------------------
    echo "<h2>1. Riparazione Tabella Messaggi</h2>";
    try {
        // Cancelliamo la tabella vecchia per essere sicuri al 100%
        $conn->exec("DROP TABLE IF EXISTS messages");
        echo "✅ Vecchia tabella 'messages' eliminata.<br>";

        // Creiamo la tabella nuova con le colonne giuste (sender_id, recipient_id)
        $sql = "CREATE TABLE messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            recipient_id INT NOT NULL,
            content TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            attachment_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (recipient_id),
            INDEX (sender_id)
        )";
        $conn->exec($sql);
        echo "✅ Tabella 'messages' ricreata correttamente con le colonne 'sender_id' e 'recipient_id'.<br>";
        
    } catch (Exception $e) {
        echo "❌ Errore tabella: " . $e->getMessage() . "<br>";
    }

    // ---------------------------------------------------------
    // 2. CREAZIONE ADMIN
    // ---------------------------------------------------------
    echo "<h2>2. Creazione Utente Amministratore</h2>";
    
    $email = 'admin@avvocatosapone.it'; // Puoi cambiarla se vuoi
    $password_in_chiaro = 'AdminPass2025!'; // Password provvisoria
    $hash = password_hash($password_in_chiaro, PASSWORD_DEFAULT);

    // Controlla se esiste già
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        // Esiste, aggiorniamo la password e il ruolo per sicurezza
        $upd = $conn->prepare("UPDATE users SET password_hash = ?, role = 'admin', first_name = 'Amministratore', last_name = 'Sistema' WHERE email = ?");
        $upd->execute([$hash, $email]);
        echo "⚠️ L'utente <b>$email</b> esisteva già.<br>";
        echo "✅ Password resettata a: <b>$password_in_chiaro</b><br>";
        echo "✅ Ruolo impostato su 'admin'.<br>";
    } else {
        // Non esiste, creiamolo
        $ins = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES ('Amministratore', 'Sistema', ?, ?, 'admin')");
        $ins->execute([$email, $hash]);
        echo "✅ Utente Admin creato con successo!<br>";
        echo "📧 Email: <b>$email</b><br>";
        echo "🔑 Password: <b>$password_in_chiaro</b><br>";
    }

    echo "<br><hr><br><strong>🎉 Operazione Completata! Ora puoi provare a loggarti e inviare messaggi.</strong>";

} catch(PDOException $e) {
    echo "❌ Errore di Connessione Generale: " . $e->getMessage();
}
?>

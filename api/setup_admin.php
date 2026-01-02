<?php
require_once 'config.php';

$email = 'peewe75@gmail.com';
$password = 'Aartu1976!';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->fetch()) {
        // Update existing
        $sql = "UPDATE users SET password_hash = :pwd, role = 'admin' WHERE email = :email";
        $msg = "Password aggiornata per l'admin";
    } else {
        // Insert new
        $sql = "INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (:email, :pwd, 'admin', 'Super', 'Admin')";
        $msg = "Nuovo Admin creato";
    }

    $update = $pdo->prepare($sql);
    $update->execute(['email' => $email, 'pwd' => $password_hash]);

    echo "✅ Successo: $msg ($email)";

} catch (PDOException $e) {
    echo "❌ Errore Database: " . $e->getMessage();
}
?>

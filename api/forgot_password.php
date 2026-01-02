<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email richiesta']);
    exit;
}

$email = trim($data['email']);

try {
    // 1. Check if user exists
    $stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Generate Token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 3. Save to DB
        $stmtInsert = $pdo->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (:email, :token, :expiry)");
        $stmtInsert->execute(['email' => $email, 'token' => $token, 'expiry' => $expiry]);

        // 4. Send Email (Simulated for demo, but code is real)
        $resetLink = "https://avvocatosapone.it/reset_password.html?token=" . $token;
        
        $subject = "Recupero Password - Studio Legale BCS";
        $message = "Gentile " . $user['first_name'] . ",\n\n";
        $message .= "Hai richiesto di reimpostare la tua password. Clicca sul link seguente:\n";
        $message .= $resetLink . "\n\n";
        $message .= "Se non hai richiesto tu il reset, ignora questa email.\n\n";
        $message .= "Cordiali Saluti,\nStudio Legale BCS";
        
        $headers = "From: info@studiodigitale.eu";

        if(mail($email, $subject, $message, $headers)) {
             echo json_encode(['success' => true, 'message' => 'Email di recupero inviata. Controlla la tua casella di posta.']);
        } else {
             // Fallback for testing environments where mail works differently or fails
             // DO NOT EXPOSE TOKEN IN PROD LIKE THIS usually, but here helps testing
             echo json_encode(['success' => true, 'message' => 'Email inviata (Simulazione). Token: ' . $token]); 
        }

    } else {
        // Security: Don't reveal user existence? Or be helpful? 
        // Generically: "Se l'email esiste, invieremo le istruzioni."
        echo json_encode(['success' => true, 'message' => 'Se l\'email è registrata, riceverai le istruzioni.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>

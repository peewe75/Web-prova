<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Nessun dato ricevuto']);
    exit;
}

// 1. Sanitize Input
$email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
$nome = isset($data['nome']) ? trim($data['nome']) : '';
$cognome = isset($data['cognome']) ? trim($data['cognome']) : '';
$telefono = isset($data['telefono']) ? trim($data['telefono']) : '';
$message = isset($data['message']) ? trim($data['message']) : '';
$topic = isset($data['competenza']) ? trim($data['competenza']) : 'Generale';

// Validation
if (empty($email) || empty($nome)) {
    echo json_encode(['success' => false, 'message' => 'Nome ed Email obbligatori']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2. Check if user exists (Duplicate Check)
    // We use the sanitized email to find the user.
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmtUser->execute(['email' => $email]);
    $existingUser = $stmtUser->fetch();
    
    $userId = null;
    $password_plain = null;
    $is_new_user = false;

    if ($existingUser) {
        // User exists: We attach the request to THIS user. We DO NOT create a new user.
        $userId = $existingUser['id'];
    } else {
        // User does not exist: Create new User.
        $is_new_user = true;
        // Generate random password (8 chars)
        $password_plain = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#%"), 0, 8);
        $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

        $insertUser = $pdo->prepare("INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (:email, :pwd, 'client', :fname, :lname)");
        $insertUser->execute([
            'email' => $email,
            'pwd' => $password_hash,
            'fname' => $nome,
            'lname' => $cognome
        ]);
        $userId = $pdo->lastInsertId();
    }

    // 3. Save Lead (The Request)
    // We always create a new lead request, linked to the user_id.
    $sql = "INSERT INTO leads (user_id, first_name, last_name, email, phone, topic, message) VALUES (:uid, :first_name, :last_name, :email, :phone, :topic, :message)";
    $stmt = $pdo->prepare($sql);
    
    $params = [
        'uid' => $userId,
        'first_name' => $nome,
        'last_name' => $cognome,
        'email' => $email,
        'phone' => $telefono,
        'topic' => $topic,
        'message' => $message
    ];

    $stmt->execute($params);

    $pdo->commit();

    // 4. Send Email
    $info_msg = "";
    if ($is_new_user && $password_plain) {
        $to = $email;
        $subject = "Benvenuto nello Studio Legale BCS - Ecco le tue credenziali";
        
        $email_content = "Gentile " . $nome . ",\n\n";
        $email_content .= "Grazie per averci contattato. Abbiamo ricevuto la tua richiesta.\n\n";
        $email_content .= "Per monitorare la tua pratica, accedi alla tua Area Riservata con le seguenti credenziali:\n";
        $email_content .= "Email: " . $email . "\n";
        $email_content .= "Password: " . $password_plain . "\n\n";
        $email_content .= "Accedi qui: https://avvocatosapone.it/login.html\n\n"; // Replace with actual domain
        $email_content .= "Cordiali Saluti,\nStudio Legale BCS";

        $headers = "From: info@studiodigitale.eu" . "\r\n" .
                   "Reply-To: info@studiodigitale.eu" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // Try sending email
        if(mail($to, $subject, $email_content, $headers)) {
            $info_msg = "Ti abbiamo inviato una email con le credenziali di accesso.";
        } else {
            $info_msg = "Account creato! La tua password è: $password_plain (Salvala, l'invio email ha avuto un problema).";
        }
    } else {
        // Existing user
        $info_msg = "Hai già un account attivo. La tua richiesta è stata aggiunta alla tua area riservata.";
    }

    echo json_encode(['success' => true, 'message' => 'Richiesta inviata correttamente.', 'info' => $info_msg]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Errore Database: ' . $e->getMessage()]);
}
?>

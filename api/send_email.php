<?php
/**
 * Send Email API
 * Invia i dati del form di contatto via email a info@studiodigitale.eu
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verifica che sia una richiesta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Leggi i dati JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validazione dati
if (empty($data['nome']) || empty($data['email']) || empty($data['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Campi obbligatori mancanti']);
    exit;
}

// Sanitizza i dati
$nome = htmlspecialchars(trim($data['nome']));
$cognome = htmlspecialchars(trim($data['cognome'] ?? ''));
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$competenza = htmlspecialchars(trim($data['competenza'] ?? 'Non specificata'));
$message = htmlspecialchars(trim($data['message']));

// Verifica email valida
if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email non valida']);
    exit;
}

// Destinatario
$to = 'info@studiodigitale.eu';

// Oggetto email
$subject = "Nuova richiesta da $nome $cognome - $competenza";

// Corpo email in HTML
$htmlMessage = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #13ec80 0%, #0a8a4d 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #0a8a4d; }
        .value { margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #13ec80; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📧 Nuova Richiesta di Contatto</h2>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>Nome e Cognome:</div>
                <div class='value'>$nome $cognome</div>
            </div>
            <div class='field'>
                <div class='label'>Email:</div>
                <div class='value'><a href='mailto:$email'>$email</a></div>
            </div>
            <div class='field'>
                <div class='label'>Area di Interesse:</div>
                <div class='value'>$competenza</div>
            </div>
            <div class='field'>
                <div class='label'>Messaggio:</div>
                <div class='value'>" . nl2br($message) . "</div>
            </div>
            <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
            <p style='font-size: 12px; color: #666;'>
                Questa email è stata inviata dal form di contatto del sito Studio Legale BCS.<br>
                Data: " . date('d/m/Y H:i:s') . "
            </p>
        </div>
    </div>
</body>
</html>
";

// Headers email
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: Studio Legale BCS <noreply@studiodigitale.eu>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion()
];

// Invia email
$success = mail($to, $subject, $htmlMessage, implode("\r\n", $headers));

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Email inviata con successo'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Errore durante l\'invio dell\'email'
    ]);
}

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Load configuration (API keys)
require_once 'config.php';

// CONFIGURATION
$API_KEY = GEMINI_API_KEY;
$TEMP_DIR = '../images/temp/';

// Ensure temp directory exists
if (!file_exists($TEMP_DIR)) {
    mkdir($TEMP_DIR, 0777, true);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['title'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

$title = $input['title'];
$summary = isset($input['summary']) ? $input['summary'] : '';

// Construct Prompt - IMPORTANT: No text/words in generated images
$prompt = "Create a high quality, professional, abstract or symbolic blog cover image visually representing the concept of: " . $title . ". ";
if ($summary) {
    $prompt .= "Visual context inspiration: " . $summary . ". ";
}
$prompt .= "Style requirements: Modern, corporate legal aesthetics, primary color palette using neon green (#4fffac) and dark green (#1a3326), professional, minimalistic, abstract geometric shapes or symbolic imagery, high resolution, 16:9 aspect ratio. CRITICAL: DO NOT include ANY text, words, letters, numbers, watermarks, or writing of any kind in the image. The image must be purely visual with no textual elements whatsoever. Focus on abstract shapes, patterns, icons, or symbolic representations only.";

// Call Imagen 3 (via Gemini API check correctness for image gen endpoint)
// Note: As of late 2024/2025, getting image gen via standard Gemini API might vary. 
// Standard endpoint for Imagen: https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-fast-generate-001:predict
// Fallback to what user likely has access to. Assuming standard Imagen access with this key.

$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-fast-generate-001:predict?key=" . $API_KEY;

$data = [
    "instances" => [
        [
            "prompt" => $prompt
        ]
    ],
    "parameters" => [
        "sampleCount" => 1,
        "aspectRatio" => "16:9" // generated images are usually square by default if not specified, but imagen 3 supports aspect ratio
    ]
];

// If using Gemini 2.5 flash or similar for text, we can't generate images. 
// We must assume the user means "Image Generation Model". 
// Let's try the standard Imagen endpoint.

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['success' => false, 'message' => 'Curl error: ' . curl_error($ch)]);
    exit;
}
curl_close($ch);

$responseData = json_decode($response, true);

// Check for errors in response
if ($httpCode !== 200 || isset($responseData['error'])) {
    // Fallback: Try a different model or endpoint if possible, but for now report error
    // Note: User said "Gemini 2.5", usually text model. If they want image they need Imagen.
    // Let's assume the key works for Imagen.

    $errorMsg = isset($responseData['error']['message']) ? $responseData['error']['message'] : 'Unknown API Error';
    echo json_encode(['success' => false, 'message' => 'API Error: ' . $errorMsg, 'debug' => $responseData]);
    exit;
}

// Extract Image
// Imagen response usually: predictions[0].bytesBase64Encoded or similar
if (isset($responseData['predictions'][0]['bytesBase64Encoded'])) {
    $base64Image = $responseData['predictions'][0]['bytesBase64Encoded'];
    $imageData = base64_decode($base64Image);

    $filename = 'gen_' . time() . '_' . uniqid() . '.png';
    $filepath = $TEMP_DIR . $filename;

    if (file_put_contents($filepath, $imageData)) {
        echo json_encode([
            'success' => true,
            'image_url' => 'images/temp/' . $filename, // Relative path for frontend
            'temp_filename' => $filename
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save image']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid API Response format', 'debug' => $responseData]);
}
?>
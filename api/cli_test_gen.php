<?php
// List available models
$API_KEY = 'AIzaSyBHsjGTPoeg5qRZYlCbRQ1EQq438kzhsnw';
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $API_KEY;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['models'])) {
    echo "Available Models:\n";
    $output = "Available Models:\n";
    foreach ($data['models'] as $model) {
        if (strpos($model['name'], 'image') !== false || strpos($model['name'], 'gemini') !== false) {
            $output .= $model['name'] . "\n";
        }
    }
    file_put_contents('models.txt', $output);
    echo "Models saved to models.txt";
} else {
    echo "Failed to list models:\n";
    print_r($data);
}
?>

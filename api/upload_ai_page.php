<?php
// Increase limits
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$targetDir = '../news_pages/';

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Check for upload errors
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['zip_file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

// Check for PHP upload errors
if ($_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];

    $errorCode = $_FILES['zip_file']['error'];
    $errorMsg = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : 'Unknown upload error';

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $errorMsg, 'error_code' => $errorCode]);
    exit;
}

$file = $_FILES['zip_file'];
$filename = pathinfo($file['name'], PATHINFO_FILENAME);
// Sanitize filename
$safeName = preg_replace('/[^a-zA-Z0-9]/', '', $filename);
$timestamp = time();
$folderName = $timestamp . '_' . $safeName;
$extractPath = $targetDir . $folderName . '/';

// Verify it is a zip or html
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'zip' && $ext !== 'html' && $ext !== 'htm') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File must be a ZIP archive or HTML file']);
    exit;
}

$entryFile = '';
$publicUrl = '';

if (!mkdir($extractPath, 0777, true)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create directory']);
    exit;
}

if ($ext === 'zip') {
    // Check if ZipArchive is available
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'ZipArchive extension not available']);
        exit;
    }

    $zip = new ZipArchive;
    if ($zip->open($file['tmp_name']) === TRUE) {
        $zip->extractTo($extractPath);
        $zip->close();

        // Find entry point
        if (file_exists($extractPath . 'index.html')) {
            $entryFile = 'index.html';
        } else {
            $files = glob($extractPath . '*.html');
            if (count($files) > 0) {
                $entryFile = basename($files[0]);
            }
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to open ZIP file']);
        exit;
    }
} else {
    // Handling HTML file
    $entryFile = $file['name'];
    if (!move_uploaded_file($file['tmp_name'], $extractPath . $entryFile)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded HTML file']);
        exit;
    }
}

if ($entryFile) {
    $publicUrl = 'news_pages/' . $folderName . '/' . $entryFile;
    echo json_encode([
        'success' => true,
        'url' => $publicUrl,
        'message' => 'Page uploaded successfully'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'url' => 'news_pages/' . $folderName . '/',
        'warning' => 'No HTML file found',
        'message' => 'Uploaded but no HTML found'
    ]);
}
?>
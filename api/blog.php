<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];
$dataFile = '../data/posts.json';
$uploadDir = '../images/blog/';

// Increase upload limits
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('memory_limit', '256M');

// Ensure upload directory exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle OPTIONS request for CORS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// GET: Fetch posts
if ($method === 'GET') {
    if (!file_exists($dataFile)) {
        echo json_encode([]);
        exit();
    }

    $jsonData = file_get_contents($dataFile);
    $posts = json_decode($jsonData, true) ?? [];

    // Sort by date descending (newest first)
    usort($posts, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // Filter by category if requested
    if (isset($_GET['category']) && $_GET['category'] !== 'all') {
        $categories = array_map('trim', explode(',', strtolower($_GET['category'])));

        // FAILSAFE: Alias 'gioco' to 'gaming' to handle caching/old frontend requests
        if (in_array('gioco', $categories)) {
            $categories[] = 'gaming';
        }

        $posts = array_filter($posts, function ($post) use ($categories) {
            // Case insensitive check against multiple categories
            return in_array(strtolower(trim($post['category'])), $categories);
        });
        // Re-index array
        $posts = array_values($posts);
    }

    // Filter by ID if requested
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $foundPost = null;
        foreach ($posts as $post) {
            if ((string) $post['id'] === (string) $id) {
                $foundPost = $post;
                break;
            }
        }
        echo json_encode($foundPost);
        exit();
    }

    // Limit results if requested
    if (isset($_GET['limit'])) {
        $limit = intval($_GET['limit']);
        $posts = array_slice($posts, 0, $limit);
    }

    echo json_encode($posts);
    exit();
}

// POST: Add or Update post (Admin only - TODO: Add Auth check)
if ($method === 'POST') {
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode([]));
    }

    $input = $_POST; // Use $_POST for form-data (needed for file upload)

    // If receiving raw JSON instead of form-data
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true);
    }

    if (!$input) {
        http_response_code(400);
        echo json_encode(["message" => "No data provided"]);
        exit();
    }

    $currentData = json_decode(file_get_contents($dataFile), true) ?? [];

    // Handle Image Upload
    $imagePath = isset($input['existing_image']) ? $input['existing_image'] : '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $name = basename($_FILES['image']['name']);
        // Sanitize filename and add timestamp to prevent overwrite
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $newFilename = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($name, PATHINFO_FILENAME)) . '.' . $extension;

        if (move_uploaded_file($tmpName, $uploadDir . $newFilename)) {
            $imagePath = 'images/blog/' . $newFilename;
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to move uploaded file."]);
            exit();
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Upload failed with error
        $uploadErrorParams = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $errCode = $_FILES['image']['error'];
        $errMsg = isset($uploadErrorParams[$errCode]) ? $uploadErrorParams[$errCode] : 'Unknown upload error';

        http_response_code(400);
        echo json_encode(["message" => "Image upload failed: " . $errMsg]);
        exit();
    } elseif (isset($input['generated_image']) && !empty($input['generated_image'])) {
        // Handle Generated Image
        $tempFile = '../images/temp/' . basename($input['generated_image']);
        if (file_exists($tempFile)) {
            $newFilename = 'generated_' . time() . '_' . uniqid() . '.png';
            if (rename($tempFile, $uploadDir . $newFilename)) {
                $imagePath = 'images/blog/' . $newFilename;
            }
        }
    }

    $postId = isset($input['id']) ? $input['id'] : null;

    $newPost = [
        "id" => $postId ? $postId : uniqid(),
        "title" => $input['title'] ?? 'Untitled',
        "summary" => $input['summary'] ?? '',
        "content" => $input['content'] ?? '',
        "category" => $input['category'] ?? 'News',
        "author" => $input['author'] ?? 'Redazione',
        "date" => $input['date'] ?? date('Y-m-d'),
        "image" => $imagePath,
        "custom_url" => $input['custom_url'] ?? ''
    ];

    if ($postId) {
        // Update existing
        foreach ($currentData as $key => $post) {
            if ((string) $post['id'] === (string) $postId) {
                $currentData[$key] = $newPost;
                break;
            }
        }
    } else {
        // Add new
        array_unshift($currentData, $newPost);
    }

    if (file_put_contents($dataFile, json_encode($currentData, JSON_PRETTY_PRINT))) {
        echo json_encode(["message" => "Post saved successfully", "post" => $newPost]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to save data"]);
    }
    exit();
}

// DELETE: Remove post
if ($method === 'DELETE') {
    if (!file_exists($dataFile)) {
        http_response_code(404);
        echo json_encode(["message" => "No posts found"]);
        exit();
    }

    // Get ID from URL or body
    $input = json_decode(file_get_contents('php://input'), true);
    $idToDelete = $_GET['id'] ?? $input['id'] ?? null;

    if (!$idToDelete) {
        http_response_code(400);
        echo json_encode(["message" => "No ID provided"]);
        exit();
    }

    $jsonData = file_get_contents($dataFile);
    $posts = json_decode($jsonData, true) ?? [];

    $initialCount = count($posts);
    $posts = array_filter($posts, function ($post) use ($idToDelete) {
        return (string) $post['id'] !== (string) $idToDelete;
    });

    if (count($posts) === $initialCount) {
        http_response_code(404);
        echo json_encode(["message" => "Post not found"]);
        exit();
    }

    // Re-index array
    $posts = array_values($posts);

    if (file_put_contents($dataFile, json_encode($posts, JSON_PRETTY_PRINT))) {
        echo json_encode(["message" => "Post deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to save changes"]);
    }
    exit();
}

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// File to store feedback
$feedbackFile = '../data/feedbacks.json';

// Initialize file if not exists
if (!file_exists($feedbackFile)) {
    file_put_contents($feedbackFile, json_encode([]));
}

// Get POST data
$data = json_decode(file_get_contents("php://input"));

if (
    !isset($data->post_id) || 
    !isset($data->vote) || 
    !in_array($data->vote, ['up', 'down'])
) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input."]);
    exit;
}

$postId = $data->post_id;
$vote = $data->vote;

// Read existing data
$feedbacks = json_decode(file_get_contents($feedbackFile), true);

// Find or create entry for this post
$foundIndex = -1;
foreach ($feedbacks as $index => $item) {
    if ($item['post_id'] === $postId) {
        $foundIndex = $index;
        break;
    }
}

if ($foundIndex === -1) {
    // New entry
    $entry = [
        'post_id' => $postId,
        'upvotes' => 0,
        'downvotes' => 0
    ];
    $feedbacks[] = $entry;
    $foundIndex = count($feedbacks) - 1;
}

// Update vots
if ($vote === 'up') {
    $feedbacks[$foundIndex]['upvotes']++;
} else {
    $feedbacks[$foundIndex]['downvotes']++;
}

// Save back to file
if (file_put_contents($feedbackFile, json_encode($feedbacks, JSON_PRETTY_PRINT))) {
    http_response_code(200);
    echo json_encode([
        "message" => "Feedback received.",
        "stats" => [
            "upvotes" => $feedbacks[$foundIndex]['upvotes'],
            "downvotes" => $feedbacks[$foundIndex]['downvotes']
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Unable to save feedback."]);
}


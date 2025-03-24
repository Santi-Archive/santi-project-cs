<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

try {
    if (!isset($data['votes'])) {
        throw new Exception("No vote data received");
    }

    // Process representatives array if it exists
    if (isset($data['votes']['Representative']) && is_array($data['votes']['Representative'])) {
        $_SESSION['selectedVotes'] = $data['votes'];
    } else {
        // Handle regular votes
        $_SESSION['selectedVotes'] = $data['votes'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Votes stored successfully'
    ]);
} catch (Exception $e) {
    error_log("Error storing votes: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
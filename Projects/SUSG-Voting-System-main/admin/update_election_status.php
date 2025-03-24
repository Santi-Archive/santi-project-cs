<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['election_id']) && isset($_POST['status'])) {
    $election_id = $_POST['election_id'];
    $status = $_POST['status'];
    
    // Verify the status is valid
    if (!in_array($status, ['Scheduled', 'Ongoing', 'Completed'])) {
        header('HTTP/1.1 400 Bad Request');
        exit('Invalid status');
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE elections 
            SET status = ?, updated_at = NOW() 
            WHERE election_id = ?
        ");
        $result = $stmt->execute([$status, $election_id]);
        
        if ($result) {
            header('HTTP/1.1 200 OK');
            echo json_encode(['success' => true]);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

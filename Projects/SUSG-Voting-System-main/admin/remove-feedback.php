<?php
require_once '../connect.php';

if (isset($_GET['id'])) {
    $feedbackId = $_GET['id'];

    // Delete feedback from the database
    $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE feedback_id = :id");
    $stmt->execute(['id' => $feedbackId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>
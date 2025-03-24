<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $studentId = $data['studentId'];

    // Delete student from the database
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    if ($stmt->execute([$studentId])) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request!"]);
}
?>
<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['studentFormId'], $_POST['studentId'], $_POST['studentName'], $_POST['college'], $_POST['hasVoted'], $_POST['password'])) {
        $studentFormId = $_POST['studentFormId'];
        $studentId = $_POST['studentId'];
        $studentName = $_POST['studentName'];
        $college = $_POST['college'];
        $hasVoted = $_POST['hasVoted'];
        $password = $_POST['password'];

        // Update student in the database
        $stmt = $pdo->prepare("
            UPDATE students 
            SET student_id = ?, student_name = ?, college_id = ?, has_voted = ?, password = ?
            WHERE student_id = ?
        ");
        if ($stmt->execute([$studentId, $studentName, $college, $hasVoted, $password, $studentFormId])) {
            // Redirect back to the voters page
            header('Location: admin-voters.php');
            exit();
        } else {
            echo "<script>alert('Failed to update student. Please try again.'); window.history.back();</script>";
            exit(); // Prevent form submission
        }
    } else {
        echo "<script>alert('Missing required fields.'); window.history.back();</script>";
        exit(); // Prevent form submission
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request method!"]);
}
?>
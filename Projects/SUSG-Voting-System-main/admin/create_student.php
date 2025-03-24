<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['studentId'], $_POST['studentName'], $_POST['college'], $_POST['hasVoted'], $_POST['password'])) {
        die("Missing required fields");
    }

    $studentId = $_POST['studentId'];
    $studentName = $_POST['studentName'];
    $college = $_POST['college'];
    $hasVoted = $_POST['hasVoted'];
    $password = $_POST['password'];

    // Get the current election ID
    $stmt = $pdo->query("SELECT election_id FROM elections WHERE is_current = 1 LIMIT 1");
    $currentElection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentElection) {
        die("No current election found. Please set a current election first.");
    }

    // Insert new student into the database
    $stmt = $pdo->prepare("
        INSERT INTO students (student_id, student_name, college_id, has_voted, password, election_id) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    try {
        if ($stmt->execute([$studentId, $studentName, $college, $hasVoted, $password, $currentElection['election_id']])) {
            header('Location: admin-voters.php');
            exit();
        } else {
            throw new Exception('Failed to create student');
        }
    } catch (PDOException $e) {
        // Check for duplicate entry
        if ($e->getCode() == 23000) {
            echo "<script>alert('Student ID already exists!'); window.history.back();</script>";
        } else {
            echo "<script>alert('Database error: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request method!"]);
}
?>
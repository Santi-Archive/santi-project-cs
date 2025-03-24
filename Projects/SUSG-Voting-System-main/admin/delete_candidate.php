<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $candidateId = $data['candidateId'];

    // Fetch the candidate's image path
    $stmt = $pdo->prepare("SELECT candidate_image FROM candidates WHERE candidate_id = ?");
    $stmt->execute([$candidateId]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($candidate) {
        $candidateImage = $candidate['candidate_image'];

        // Delete candidate from the database
        $stmt = $pdo->prepare("DELETE FROM candidates WHERE candidate_id = ?");
        if ($stmt->execute([$candidateId])) {
            // Delete the candidate's image file
            if ($candidateImage && file_exists('../' . $candidateImage)) {
                unlink('../' . $candidateImage);
            }
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Candidate not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request!"]);
}
?>
<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = $_POST['studentID'] ?? '';
    $fullName = $_POST['fullName'] ?? '';
    $college = $_POST['college'] ?? '';
    $position = $_POST['position'] ?? '';
    $candidateParty = $_POST['candidateParty'] ?? '';
    $uploadedFiles = $_FILES['uploadedFiles'] ?? null;

    try {
        // Insert candidate details into the database
        $stmt = $pdo->prepare("
            INSERT INTO candidates (candidate_name, college_id, position_id, candidate_party, qualified, remarks, candidate_image)
            VALUES (:candidate_name, :college_id, :position_id, :candidate_party, 0, NULL, NULL)
        ");
        $stmt->execute([
            ':candidate_name' => $fullName,
            ':college_id' => $college,
            ':position_id' => $position,
            ':candidate_party' => $candidateParty
        ]);

        // Get the inserted candidate ID
        $candidateID = $pdo->lastInsertId();

        // Handle uploaded files
        if ($uploadedFiles) {
            foreach ($uploadedFiles['tmp_name'] as $key => $tmpName) {
                $fileName = $uploadedFiles['name'][$key];
                $filePath = 'uploads/' . $fileName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $fileStmt = $pdo->prepare("
                        INSERT INTO candidate_documents (candidate_id, document_path)
                        VALUES (:candidate_id, :document_path)
                    ");
                    $fileStmt->execute([
                        ':candidate_id' => $candidateID,
                        ':document_path' => $filePath
                    ]);
                }
            }
        }

        echo json_encode(['success' => true, 'message' => 'Candidate data saved successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to save candidate data. Error: ' . $e->getMessage()]);
    }
}
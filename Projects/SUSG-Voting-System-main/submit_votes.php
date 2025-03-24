<?php
// Disable error display in output
error_reporting(0);
ini_set('display_errors', 0);

require_once 'connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("User not logged in");
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception("Invalid JSON data received");
    }

    $user_id = $_SESSION['user']['student_id'];

    // Get current election ID
    $electionStmt = $pdo->prepare("SELECT election_id FROM elections WHERE is_current = 1 AND status = 'Ongoing' LIMIT 1");
    $electionStmt->execute();
    $election_id = $electionStmt->fetchColumn();

    if (!$election_id) {
        throw new Exception("No active election found");
    }

    // Get voter's college_id
    $voterStmt = $pdo->prepare("SELECT college_id FROM students WHERE student_id = ?");
    $voterStmt->execute([$user_id]);
    $voter_college_id = $voterStmt->fetchColumn();

    if (!$voter_college_id) {
        throw new Exception("Could not determine voter's college");
    }

    $pdo->beginTransaction();

    // Process votes
    foreach ($data as $position => $candidate) {
        $position_id_stmt = $pdo->prepare("SELECT position_id FROM positions WHERE position_name = :position");
        $position_id_stmt->bindParam(':position', $position);
        $position_id_stmt->execute();
        $position_id = $position_id_stmt->fetchColumn();

        if (!$position_id) {
            throw new Exception("Invalid position: " . htmlspecialchars($position));
        }

        // Handle representatives differently
        if ($position === 'Representative') {
            // Check if it's an empty array (abstain case) or has actual votes
            if (empty($candidate) || (is_array($candidate) && count($candidate) === 0)) {
                // Handle abstain for representatives - now with college_id
                $abstainStmt = $pdo->prepare("
                    SELECT candidate_id FROM candidates 
                    WHERE position_id = :position_id 
                    AND election_id = :election_id 
                    AND candidate_name = 'Abstain'
                    AND college_id = :college_id
                ");
                $abstainStmt->execute([
                    ':position_id' => $position_id,
                    ':election_id' => $election_id,
                    ':college_id' => $voter_college_id
                ]);
                $abstain_id = $abstainStmt->fetchColumn();

                if (!$abstain_id) {
                    // Create abstain candidate for representatives with voter's college_id
                    $createAbstainStmt = $pdo->prepare("
                        INSERT INTO candidates (
                            candidate_name, college_id, position_id, 
                            qualified, election_id, party_id
                        ) VALUES (
                            'Abstain', :college_id, :position_id, 
                            0, :election_id, NULL
                        )
                    ");
                    $createAbstainStmt->execute([
                        ':college_id' => $voter_college_id,
                        ':position_id' => $position_id,
                        ':election_id' => $election_id
                    ]);
                    $abstain_id = $pdo->lastInsertId();
                }

                // Insert abstain vote for representatives
                $stmt = $pdo->prepare("
                    INSERT INTO votes (
                        student_id, position_id, candidate_id, 
                        election_id, vote_timestamp
                    ) VALUES (
                        :student_id, :position_id, :candidate_id, 
                        :election_id, NOW()
                    )
                ");
                
                $stmt->execute([
                    ':student_id' => $user_id,
                    ':position_id' => $position_id,
                    ':candidate_id' => $abstain_id,
                    ':election_id' => $election_id
                ]);
            } else {
                // Insert vote for each representative
                foreach ($candidate as $rep) {
                    // Verify candidate exists and belongs to current election
                    $checkCandidateStmt = $pdo->prepare("
                        SELECT candidate_id FROM candidates 
                        WHERE candidate_id = :candidate_id 
                        AND election_id = :election_id
                    ");
                    $checkCandidateStmt->execute([
                        ':candidate_id' => $rep['candidate_id'],
                        ':election_id' => $election_id
                    ]);
                    $candidate_id = $checkCandidateStmt->fetchColumn();

                    if (!$candidate_id) {
                        throw new Exception("Invalid representative selection");
                    }

                    // Insert representative vote
                    $stmt = $pdo->prepare("
                        INSERT INTO votes (
                            student_id, position_id, candidate_id, 
                            election_id, vote_timestamp
                        ) VALUES (
                            :student_id, :position_id, :candidate_id, 
                            :election_id, NOW()
                        )
                    ");
                    
                    $stmt->execute([
                        ':student_id' => $user_id,
                        ':position_id' => $position_id,
                        ':candidate_id' => $candidate_id,
                        ':election_id' => $election_id
                    ]);
                }
            }
        } else {
            // Handle abstain vote
            if ($candidate['candidate_id'] === 0) {
                // Find abstain candidate or create one if it doesn't exist
                $abstainStmt = $pdo->prepare("
                    SELECT candidate_id FROM candidates 
                    WHERE position_id = :position_id 
                    AND election_id = :election_id 
                    AND candidate_name = 'Abstain'
                ");
                $abstainStmt->execute([
                    ':position_id' => $position_id,
                    ':election_id' => $election_id
                ]);
                $abstain_id = $abstainStmt->fetchColumn();

                if (!$abstain_id) {
                    // Create abstain candidate
                    $createAbstainStmt = $pdo->prepare("
                        INSERT INTO candidates (
                            candidate_name, college_id, position_id, 
                            qualified, election_id, party_id
                        ) VALUES (
                            'Abstain', 0, :position_id, 
                            0, :election_id, NULL
                        )
                    ");
                    $createAbstainStmt->execute([
                        ':position_id' => $position_id,
                        ':election_id' => $election_id
                    ]);
                    $abstain_id = $pdo->lastInsertId();
                }
                
                $candidate_id = $abstain_id;
            } else {
                // Verify candidate exists and belongs to current election
                $checkCandidateStmt = $pdo->prepare("
                    SELECT candidate_id FROM candidates 
                    WHERE candidate_id = :candidate_id 
                    AND election_id = :election_id
                ");
                $checkCandidateStmt->execute([
                    ':candidate_id' => $candidate['candidate_id'],
                    ':election_id' => $election_id
                ]);
                $candidate_id = $checkCandidateStmt->fetchColumn();

                if (!$candidate_id) {
                    throw new Exception("Invalid candidate selection");
                }
            }

            // Insert vote
            $stmt = $pdo->prepare("
                INSERT INTO votes (
                    student_id, position_id, candidate_id, 
                    election_id, vote_timestamp
                ) VALUES (
                    :student_id, :position_id, :candidate_id, 
                    :election_id, NOW()
                )
            ");
            
            $stmt->execute([
                ':student_id' => $user_id,
                ':position_id' => $position_id,
                ':candidate_id' => $candidate_id,
                ':election_id' => $election_id
            ]);
        }
    }

    // Update has_voted status
    $updateStmt = $pdo->prepare("UPDATE students SET has_voted = 1 WHERE student_id = :student_id");
    $updateStmt->execute([':student_id' => $user_id]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Vote submission failed for student $user_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
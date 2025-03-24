<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

if (isset($_GET['position_id'])) {
    // Get student's college_id from session
    $student_college_id = $_SESSION['user']['college_id'];
    
    // First get the current election ID
    $electionStmt = $pdo->query("SELECT election_id FROM elections WHERE is_current = 1 AND status = 'Ongoing' LIMIT 1");
    $current_election = $electionStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_election) {
        echo json_encode([]);
        exit;
    }
    
    // Modify query to filter by current election and college for representatives
    $stmt = $pdo->prepare("
        SELECT c.*, 
               colleges.college_name, 
               positions.position_name,
               parties.party_name
        FROM candidates c
        LEFT JOIN colleges ON c.college_id = colleges.college_id 
        LEFT JOIN positions ON c.position_id = positions.position_id
        LEFT JOIN parties ON c.party_id = parties.party_id
        WHERE c.position_id = ? 
        AND c.qualified = 1
        AND c.election_id = ?
        AND (
            positions.position_name != 'Representative' 
            OR 
            (positions.position_name = 'Representative' AND c.college_id = ?)
        )
    ");
    
    $stmt->execute([
        $_GET['position_id'], 
        $current_election['election_id'],
        $student_college_id
    ]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($candidates);
}
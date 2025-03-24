<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start logging
error_log("Starting bulk upload process");

$file = null;

try {
    // Get current election ID
    $stmt = $pdo->query("SELECT election_id FROM elections WHERE is_current = 1 LIMIT 1");
    $currentElection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentElection) {
        throw new Exception("No active election found.");
    }

    if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("No file uploaded or upload error: " . $_FILES['csvFile']['error']);
    }

    // Log file details
    error_log("File uploaded: " . $_FILES['csvFile']['name']);
    error_log("File size: " . $_FILES['csvFile']['size']);
    error_log("File type: " . $_FILES['csvFile']['type']);

    // Read CSV file
    $file = fopen($_FILES['csvFile']['tmp_name'], 'r');
    if (!$file) {
        throw new Exception("Error opening file: " . error_get_last()['message']);
    }

    // Read headers
    $headers = fgetcsv($file);
    if (!$headers) {
        throw new Exception("Could not read CSV headers");
    }

    // Log headers for debugging
    error_log("CSV Headers found: " . implode(", ", $headers));

    // Clean headers (remove BOM if present and trim)
    $headers = array_map(function($header) {
        $header = trim($header);
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header);
    }, $headers);

    $expectedHeaders = ['student_id', 'student_name', 'college_id', 'password'];
    
    // Log cleaned headers and expected headers
    error_log("Cleaned headers: " . implode(", ", $headers));
    error_log("Expected headers: " . implode(", ", $expectedHeaders));

    // Compare headers
    $diff = array_diff($headers, $expectedHeaders);
    if (!empty($diff)) {
        throw new Exception("Invalid CSV format. Headers mismatch. Found: " . implode(", ", $headers));
    }

    $pdo->beginTransaction();
    $successCount = 0;
    $errorCount = 0;
    $errors = [];

    $rowNumber = 1; // For error reporting

    while (($data = fgetcsv($file)) !== FALSE) {
        $rowNumber++;
        error_log("Processing row $rowNumber: " . implode(", ", $data));

        // Skip empty rows
        if (empty($data[0])) {
            error_log("Skipping empty row $rowNumber");
            continue;
        }

        $student_id = trim($data[0]);
        $student_name = trim($data[1]);
        $college_id = (int)trim($data[2]);
        $password = trim($data[3]);

        // Validate data
        if (empty($student_id) || empty($student_name) || empty($college_id) || empty($password)) {
            $errors[] = "Row $rowNumber: Missing required fields";
            $errorCount++;
            continue;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO students (student_id, student_name, college_id, password, election_id, has_voted) 
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            
            if ($stmt->execute([$student_id, $student_name, $college_id, $password, $currentElection['election_id']])) {
                $successCount++;
                error_log("Successfully inserted student: $student_id");
            } else {
                $errors[] = "Row $rowNumber: Failed to insert student $student_id";
                $errorCount++;
                error_log("Failed to insert student: $student_id");
            }
        } catch (PDOException $e) {
            $errors[] = "Row $rowNumber: Database error for student $student_id: " . $e->getMessage();
            $errorCount++;
            error_log("Database error: " . $e->getMessage());
        }
    }

    if ($successCount > 0) {
        $pdo->commit();
        $_SESSION['success'] = "Successfully added {$successCount} voters." . ($errorCount > 0 ? " Failed: {$errorCount}" : "");
        if (!empty($errors)) {
            $_SESSION['upload_errors'] = $errors;
        }
        error_log("Upload completed: $successCount successful, $errorCount failed");
    } else {
        throw new Exception("No valid records were found to insert. Please check your CSV file format.");
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "Error: " . $e->getMessage();
    error_log("Exception caught: " . $e->getMessage());
} finally {
    if ($file !== null && is_resource($file)) {
        fclose($file);
    }
}

header('Location: admin-voters.php');
exit();

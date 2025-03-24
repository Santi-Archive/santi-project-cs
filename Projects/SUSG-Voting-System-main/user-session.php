<?php
require_once 'connect.php';

session_start();

// Store the previous page URL in session for proper redirection after logout
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['current_page'])) {
    if (strpos($_GET['current_page'], 'admin') !== false) {
        $_SESSION['logout_type'] = 'comelec';
    } else {
        $_SESSION['logout_type'] = 'voter';
    }
}

// Only redirect if accessing this page directly (not through a login form)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_SESSION['is_comelec_logged_in'])) {
        header('Location: Admin/admin-home.php');
        exit();
    } elseif (isset($_SESSION['user'])) {
        header('Location: homepage.php');
        exit();
    }
}

$errors = [];

// Handle Comelec login
if (isset($_POST['signin_comelec'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Debugging: Log the received username and password
    error_log("Received Username: $username");
    error_log("Received Password: $password");

    if (empty($username)) {
        $errors['username'] = 'Username cannot be empty';
    }

    if (empty($password)) {
        $errors['password'] = 'Password cannot be empty';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: loginascomelec.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM comelec WHERE comelec_name = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // Debugging: Log the fetched user data
    if ($user) {
        error_log("Comelec user found: " . print_r($user, true));
    } else {
        error_log("Comelec user not found");
    }

    // Compare plain text passwords
    if ($user && $password === $user['password']) {
        $_SESSION['comelec_name'] = $user['comelec_name'];
        $_SESSION['is_comelec_logged_in'] = true; // Add session variable to track Comelec login

        header('Location: Admin/admin-home.php');
        exit();
    } else {
        $errors['login'] = 'Invalid Username or Password';
        $_SESSION['errors'] = $errors;
        header('Location: loginascomelec.php');
        exit();
    }
}

// Handle Voter login
if (isset($_POST['signin'])) {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Debugging: Log the received student_id and password
    error_log("Received Student ID: $student_id");
    error_log("Received Password: $password");

    if (empty($student_id)) {
        $errors['student_id'] = 'Student ID cannot be empty';
    }

    if (empty($password)) {
        $errors['password'] = 'Password cannot be empty';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: loginasvoter.php');
        exit();
    }

    // First, get the current election
    $election_stmt = $pdo->prepare("SELECT election_id FROM elections WHERE is_current = 1 LIMIT 1");
    $election_stmt->execute();
    $current_election = $election_stmt->fetch();

    if (!$current_election) {
        $errors['login'] = 'No active election found';
        $_SESSION['errors'] = $errors;
        header('Location: loginasvoter.php');
        exit();
    }

    // Modified query to include election verification
    $stmt = $pdo->prepare("
        SELECT students.*, colleges.college_name 
        FROM students 
        LEFT JOIN colleges ON students.college_id = colleges.college_id 
        WHERE student_id = :student_id AND election_id = :election_id
    ");
    $stmt->execute([
        'student_id' => $student_id,
        'election_id' => $current_election['election_id']
    ]);
    $user = $stmt->fetch();

    // Debugging: Log the fetched user data
    if ($user) {
        error_log("User found: " . print_r($user, true));
    } else {
        error_log("User not found");
    }

    // Compare plain text passwords
    if ($user && $password === $user['password']) {
        $_SESSION['user'] = [
            'student_id' => $user['student_id'],
            'student_name' => $user['student_name'],
            'college_name' => $user['college_name'],
            'has_voted' => $user['has_voted'],
            'election_id' => $user['election_id']  // Add election_id to session
        ];

        header('Location: homepage.php');
        exit();
    } else {
        $errors['login'] = 'Invalid student ID or password';
        $_SESSION['errors'] = $errors;
        header('Location: loginasvoter.php');
        exit();
    }
}
?>
<?php
session_start();

if (isset($_GET['type'])) {
    switch ($_GET['type']) {
        case 'voter':
            // Only unset voter session
            if (isset($_SESSION['user'])) {
                unset($_SESSION['user']);
            }
            header("Location: start.php");
            break;
            
        case 'comelec':
            // Only unset comelec session
            if (isset($_SESSION['is_comelec_logged_in'])) {
                unset($_SESSION['is_comelec_logged_in']);
                unset($_SESSION['comelec_name']);
            }
            header("Location: start.php");
            break;
            
        default:
            // Full logout
            session_unset();
            session_destroy();
            header("Location: start.php");
    }
} else {
    // Default to full logout if no type specified
    session_unset();
    session_destroy();
    header("Location: start.php");
}
exit();
?>

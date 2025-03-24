<?php
session_start();

// Only check for voter login status
if (isset($_SESSION['user'])) {
    header('Location: homepage.php');
    exit();
}

if (isset($_SESSION['errors'])) {
  $errors = $_SESSION['errors'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Login Page</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        /* General body and layout styling */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Container for all page content */
        .page-container {
            margin-top: 60px;
            margin-bottom: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-grow: 1;
        }

        /* Header styling */
        #header {
            width: 100%;
        }

        /* Main content area styling */
        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 20px;
        }

        .login-page-box {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 320px;
            position: relative;
            box-sizing: border-box; /* Ensure padding is included in the width */
        }

        .login-page-logo {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
        }

        .login-page-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
        }

        .login-page-input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .login-page-input-group label {
            font-weight: 500;
            color: #333;
        }

        .login-page-input-group input {
            margin-top: 5px;
            width: 100%; /* Adjust width to take full container width */
            padding: 10px;
            font-size: 14px;
            border: 2px solid #ccc;
            border-radius: 5px;
            transition: border 0.3s ease;
            box-sizing: border-box; /* Ensure padding is included in the width */
        }

        .login-page-input-group input:focus {
            border-color: #c41f1f;
            outline: none;
        }

        .login-page-btn {
            margin-top: 15px;
            background-color: #c41f1f;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%; /* Adjust width to take full container width */
            font-size: 16px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-page-btn:hover {
            background-color: #b01919;
        }

        .login-page-error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Footer styling */
        #footer {
            width: 100%;
            padding: 10px 0;
            background-color: #f5f5f5;
            text-align: center;
            font-size: 14px;
            color: #333;
        }

        @media (max-width: 768px) {
            .login-page-box {
                width: 90%;
                margin: 0 auto;
            }
        }
    </style>
    <script src="script/mainload.js" type="module" defer></script>
</head>
<body>
    
    <!-- Header Section -->
    <?php include 'header.php'; ?>
  
    <!-- Page Content Container -->
    <div class="page-container">

        <!-- Main Content Section -->
        <main class="main">
            <div class="login-page-box">
                <img src="asset/sulogo.png" alt="University Logo" class="login-page-logo">
                <h2 class="login-page-title">Login as Voter</h2>
                
                <form id="loginForm" method="POST" action="user-session.php">
                    <div class="login-page-input-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" required>
                        <?php
                        if (isset($errors['student_id'])) {
                            echo '<div class="error"><p>' . $errors['student_id'] . '</p></div>';
                        }
                        ?>
                    </div>
                    <div class="login-page-input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <?php
                        if (isset($errors['password'])) {
                            echo '<div class="error"><p>' . $errors['password'] . '</p></div>';
                        }
                        ?>
                    </div>
                    <button type="submit" name="signin" class="login-page-btn">Login</button>
                    <?php
                    if (isset($errors['login'])) {
                        echo '<p class="login-page-error-message">' . $errors['login'] . '</p>';
                    }
                    ?>
                </form>
            </div>
        </main>
        
    </div>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>
<?php
if (isset($_SESSION['errors'])) {
  unset($_SESSION['errors']);
}
?>
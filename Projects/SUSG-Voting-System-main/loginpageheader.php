<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Header</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        /* Reset and body styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        /* Scoped header styles */
        .login-page-header {
            display: flex;
            align-items: center;
            background-color: #c41f1f; 
            padding: 10px 20px;
            padding-left: 250px;
        }

        .login-page-header-logo {
            width: 120px;
            height: 120px;
        }

        .login-page-header-title {
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin-left: 15px;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .login-page-header {
                padding-left: 20px;
                justify-content: flex-start;
            }

            .login-page-header-logo {
                width: 80px;
                height: auto;
            }

            .login-page-header-title {
                font-size: 18px;
            }
        }

        .main {
            padding-top: 150px;
            height: auto;
        }

    </style>
</head>

<body>

    <!-- Header Content -->
    <div class="login-page-header">
        <img src="asset/susglogo.png" alt="SUSG Logo" class="login-page-header-logo">
        <span class="login-page-header-title">SUSG Election System</span>
    </div>

</body>
</html>
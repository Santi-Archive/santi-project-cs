<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Start Page</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        /* General body and layout styling */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
            background-color: #ffffff;
        }

        /* Header container for consistent padding */
        .page-container {
            padding-top: 50px;
            margin-top: 50px;
            margin-bottom: 100px;
        }

        .start-container {
            text-align: center;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
            max-width: 600px;
        }

        .title {
            font-size: 24px;
            color: #333;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .button-group {
            display: flex;
            gap: 20px;
            flex-direction: row;
            justify-content: center;
        }

        .start-btn {
            background-color: #c41f1f;
            color: white;
            padding: 15px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            text-decoration: none; /* Removes underline */
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 200px;
            font-weight: 600;
            text-align: center;
        }

        .start-btn:hover {
            background-color: #b01919;
        }

        .start-btn:focus {
            outline: none;
        }
    </style>
    <script src="script/mainload.js" type="module" defer></script>
</head>

<body>
    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <div class="page-container">
        <div class="start-container">
            <h2 class="title">Choose Your Login</h2>
            <div class="button-group">
                <a href="loginasvoter.php" class="start-btn">Login as Voter</a>
                <a href="loginascomelec.php" class="start-btn">Login as Comelec</a>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>
    
</body>
</html>
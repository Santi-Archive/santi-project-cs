<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - How to Vote</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        .main {
            padding-top: 150px;
            height: auto;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .how-to-vote-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .how-to-vote-header h2 {
            color: #c41f1f;
            font-size: 36px;
        }

        .step-container {
            background-color: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .step-container:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .step-number {
            background-color: #c41f1f;
            color: white;
            font-size: 24px;
            font-weight: bold;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 20px;
            flex-shrink: 0;
            text-align: center;
        }

        .step-content {
            flex-grow: 1;
        }

        .step-content h3 {
            color: #333;
            font-size: 22px;
            margin-bottom: 10px;
        }

        .step-content p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
        }
    </style>
    <script src="script/mainload.js" type="module" defer></script>
</head>
<body>

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="container">
            <div class="how-to-vote-header">
                <h2>How to Vote</h2>
            </div>
    
            <!-- Step 1 -->
            <div class="step-container">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Register as a Voter</h3>
                    <p>Ensure that you are registered to vote. Visit the Voter Registration page, fill in the necessary details, and submit your registration before the deadline.</p>
                </div>
            </div>
    
            <!-- Step 2 -->
            <div class="step-container">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Receive Your Voting Credentials</h3>
                    <p>Once registered, you will receive your voting credentials via email. Make sure to keep them secure as they are necessary for accessing the voting system.</p>
                </div>
            </div>
    
            <!-- Step 3 -->
            <div class="step-container">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Log In to the Voting Portal</h3>
                    <p>On the day of the election, log in to the SUSG Election System using your credentials. The system will guide you through the secure authentication process.</p>
                </div>
            </div>
    
            <!-- Step 4 -->
            <div class="step-container">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>Select Your Candidates</h3>
                    <p>Review the list of candidates and select your preferred choices. Ensure to cast your vote before the voting period ends.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>
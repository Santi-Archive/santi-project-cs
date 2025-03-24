<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Guidelines</title>
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

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h2 {
            color: #c41f1f;
            font-size: 36px;
        }

        .guidelines-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .guidelines-section h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 5px;
        }

        .guidelines-section h3::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #c41f1f;
        }

        .guidelines-section p {
            color: #555;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .guidelines-section ul {
            list-style-type: disc;
            padding-left: 20px;
        }

        .guidelines-section ul li {
            margin-bottom: 10px;
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
            <div class="page-header">
                <h2>Election Guidelines</h2>
            </div>
    
            <div class="guidelines-section">
                <h3>1. Eligibility Criteria</h3>
                <p>All registered students of SUSG are eligible to vote in the elections, provided they have completed their voter registration by the specified deadline.</p>
    
                <h3>2. Voter Registration</h3>
                <p>Voter registration is mandatory. To be able to vote, a student must be enrolled in Silliman University taking a two-semester class in their course.</p>
    
                <h3>3. Voting Process</h3>
                <ul>
                    <li>Log in using your student credentials.</li>
                    <li>Review the list of candidates and their manifestos.</li>
                    <li>Cast your vote by selecting your preferred candidate.</li>
                </ul>
    
                <h3>4. Important Dates</h3>
                <p>Keep an eye on the official SUSG announcements for key election dates, including the registration deadline, voting period, and result announcements.</p>
    
                <h3>5. Code of Conduct</h3>
                <p>All candidates and voters are expected to uphold the integrity of the election process. Any form of coercion, bribery, or tampering with the voting system will result in disqualification.</p>
    
                <h3>6. Contact Us</h3>
                <p>If you have any questions or need assistance, please contact the Election Commission.</p>
            </div>
        </div>
        
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>
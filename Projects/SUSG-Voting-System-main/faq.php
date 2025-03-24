<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Frequently Asked Questions</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
            background-color: #f5f5f5; /* Light gray background for better contrast */
        }

        main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            width: 100%;
            padding: 50px 20px;
            box-sizing: border-box;
        }

        .faq-container {
            margin-top: 50px;
            max-width: 900px;
            width: 100%;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .faq-title {
            font-size: 36px;
            font-weight: 600;
            color: #c41f1f; /* Maroon */
            text-align: center;
            margin-bottom: 30px;
        }

        .faq-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 15px;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .question-item {
            color: #333;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            padding: 10px 0;
        }

        .question-item::before {
            content: '+';
            position: absolute;
            right: 0;
            font-size: 24px;
            color: #c41f1f; /* Maroon */
            transition: transform 0.3s ease;
        }

        .question-item.active::before {
            content: '-';
            transform: rotate(45deg);
        }

        .answer-item {
            display: none;
            font-size: 16px;
            color: #555;
            line-height: 1.8;
            margin-top: 10px;
        }

        .faq-item.open .answer-item {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="faq-container">
            <h2 class="faq-title">Frequently Asked Questions</h2>
            <div class="faq-item">
                <div class="question-item">What is the SUSG Election System?</div>
                <div class="answer-item">
                    <p>The SUSG Election System is an online platform designed to manage and facilitate the election process for the Silliman University Student Government.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="question-item">Who can access the SUSG Election System?</div>
                <div class="answer-item">
                    <p>Only registered students of Silliman University are allowed access to the SUSG Election System. Each student must log in using their university credentials to cast their vote.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="question-item">How do I log in to the SUSG Election System?</div>
                <div class="answer-item">
                    <p>You can log in using your university-issued email and password. If you have trouble logging in, ensure you are using the correct credentials or contact support.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="question-item">Can I vote more than once?</div>
                <div class="answer-item">
                    <p>No, each student can vote only once per election. Once a vote is submitted, it cannot be changed or cast again to maintain fairness and integrity.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="question-item">Are my votes confidential?</div>
                <div class="answer-item">
                    <p>Yes, your votes are strictly confidential. The SUSG Election System uses secure protocols to ensure that your choices are private and only the final results are made public.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="question-item">How are my votes counted in the SUSG Election System?</div>
                <div class="answer-item">
                    <p>The votes are automatically counted by the system after submission. The system ensures accuracy and transparency by using secure algorithms to tally votes and present the results without any manual interference.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

    <script>
        document.querySelectorAll('.question-item').forEach(item => {
            item.addEventListener('click', () => {
                const parent = item.parentElement;
                parent.classList.toggle('open');
            });
        });
    </script>
</body>
</html>
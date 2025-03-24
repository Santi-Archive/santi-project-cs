<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - FAQ</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
            background-color: #f5f5f5;
        }

        .main {
            padding-top: 150px;
            height: auto;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .faq-header h2 {
            color: #c41f1f;
            font-size: 36px;
        }

        .faq-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .faq-item {
            margin-bottom: 20px;
        }

        .faq-item h3 {
            color: #333;
            font-size: 20px;
            cursor: pointer;
            position: relative;
            padding-bottom: 5px;
        }

        .faq-item h3::before {
            content: '+';
            position: absolute;
            right: 0;
            top: 0;
            font-size: 24px;
            color: #c41f1f;
        }

        .faq-item.open h3::before {
            content: '-';
        }

        .faq-item p {
            display: none;
            color: #555;
            font-size: 16px;
            line-height: 1.8;
            margin-top: 10px;
        }

        .faq-item.open p {
            display: block;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // FAQ Toggle Script
            document.querySelectorAll('.faq-item h3').forEach(item => {
                item.addEventListener('click', () => {
                    const parent = item.parentElement;
                    parent.classList.toggle('open');
                });
            });
        });
    </script>
</head>
<body>

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="container">
            <div class="faq-header">
                <h2>Frequently Asked Questions</h2>
            </div>
    
            <div class="faq-section">
                <div class="faq-item">
                    <h3>What is the SUSG Election System?</h3>
                    <p>The SUSG Election System is a platform designed to facilitate transparent, secure, and efficient student union elections, allowing students to cast their votes electronically.</p>
                </div>
                <div class="faq-item">
                    <h3>How do I register to vote?</h3>
                    <p>To register, visit the Voter Registration page, fill out the required information, and submit your details. Make sure to register before the deadline to be eligible to vote.</p>
                </div>
                <div class="faq-item">
                    <h3>Is my vote anonymous?</h3>
                    <p>Yes, your vote is completely anonymous. The system uses encryption and secure data management practices to ensure that all votes are confidential and cannot be traced back to individual voters.</p>
                </div>
                <div class="faq-item">
                    <h3>Can I change my vote once it's submitted?</h3>
                    <p>Unfortunately, once you have submitted your vote, it cannot be changed. Please make sure to review your choices carefully before finalizing your vote.</p>
                </div>
                <div class="faq-item">
                    <h3>Who can I contact for support?</h3>
                    <p>If you encounter any issues or have questions, please reach out to our support team via the Help & Support section of our website or by emailing us at susg@su.edu.ph.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>
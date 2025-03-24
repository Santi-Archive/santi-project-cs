<?php
if (headers_sent($file, $line)) {
    die("Headers already sent in $file on line $line.");
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Candidate Registration</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <script src="script/mainload.js" type="module" defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #f8f9fa;
        }

        main {
            max-width: 800px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px 40px;
        }

        h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #b82323;
            text-align: center;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .progress-step {
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .progress-step span {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #d9534f;
            color: #fff;
            font-size: 12px;
            line-height: 20px;
            text-align: center;
            margin-right: 10px;
        }

        .progress-step.active span {
            background-color: #b82323;
        }

        .progress-step .line {
            flex-grow: 1;
            height: 2px;
            background-color: #d9534f;
            margin: 0 10px;
        }

        .progress-step.active .line {
            background-color: #b82323;
        }

        .content {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }

        .content h2 {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
        }

        .content p {
            margin-bottom: 20px;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            color: #888;
        }

        .step span {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 30px;
            height: 30px;
            background: #ddd;
            border-radius: 50%;
            font-weight: bold;
            color: #555;
        }

        .step.active span {
            background: #c41f1f;
            color: white;
        }

        .step.active {
            color: #000;
        }

        .step:not(:last-child)::after {
            content: '';
            flex-grow: 1;
            height: 2px;
            background: #ddd;
        }

        .step.active + .step::after {
            background: #c41f1f;
        }

        .content-section {
            display: none;
        }

        .content-section:not(.hidden) {
            display: block;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:disabled {
            background: #ddd;
            cursor: not-allowed;
        }

        #backButton {
            background: #f44336;
            color: white;
        }

        #nextButton {
            background: #4CAF50;
            color: white;
        }

        section {
            margin-bottom: 20px;
        }

        section h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }

        ul {
            margin-left: 20px;
        }

        ul li {
            margin-bottom: 5px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        ul {
            margin: 10px 0 20px 20px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const steps = document.querySelectorAll('.step');
            const sections = document.querySelectorAll('.content-section');
            const backButton = document.getElementById('backButton');
            const nextButton = document.getElementById('nextButton');
            let currentStep = 0;

            function updateSteps() {
                steps.forEach((step, index) => {
                    step.classList.toggle('active', index <= currentStep);
                    sections[index].classList.toggle('hidden', index !== currentStep);
                });

                backButton.disabled = currentStep === 0;
                nextButton.disabled = currentStep === steps.length - 1;
            }

            backButton.addEventListener('click', () => {
                if (currentStep > 0) {
                    currentStep--;
                    updateSteps();
                }
            });

            nextButton.addEventListener('click', () => {
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    updateSteps();
                }
            });

            updateSteps();
        });
    </script>
</head>
<body>

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Main Section -->
    <main>
         <!-- Main Content: Election Monitoring Section -->
    <div class="main">
        <div class="container">
            <div class="monitoring-header">
                <h2>Election Monitoring</h2>
            </div>

            <div class="monitoring-content">
                <p>The SUSG Election Monitoring team ensures the integrity and transparency of the election process. This page outlines the various measures and steps taken to monitor elections effectively.</p>
                
                <h3>Overview of Election Monitoring</h3>
                <p>Election monitoring at SUSG is conducted through a combination of technology and human oversight. This approach ensures that every vote is accounted for and the process remains free from tampering or irregularities.</p>

                <h3>Key Monitoring Measures:</h3>
                <ul>
                    <li><strong>Real-Time Surveillance:</strong> Election venues are equipped with CCTV cameras for continuous monitoring.</li>
                    <li><strong>Digital Voting System:</strong> The system logs all voting activities, providing an audit trail for verification.</li>
                    <li><strong>Independent Observers:</strong> Third-party observers are present to ensure a fair and unbiased process.</li>
                    <li><strong>Security Checks:</strong> Voters and candidates are subject to identity verification before participation.</li>
                </ul>

                <div class="monitoring-steps">
                    <h3>Step-by-Step Monitoring Process</h3>

                    <!-- Step 1 -->
                    <div class="monitoring-step">
                        <h4>Step 1: Pre-Election Preparation</h4>
                        <p>Before the election begins, the system undergoes thorough testing, and security measures are enforced.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="monitoring-step">
                        <h4>Step 2: Real-Time Monitoring</h4>
                        <p>During voting, real-time data is captured and analyzed to detect any suspicious activities.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="monitoring-step">
                        <h4>Step 3: Post-Election Audit</h4>
                        <p>After the election, a comprehensive audit is conducted to verify the accuracy of the results.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>
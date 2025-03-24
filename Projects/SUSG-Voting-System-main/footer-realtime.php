<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Real Time Results</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <script src="script/mainload.js" type="module" defer></script>
</head>
    <style>
        .main {
            padding-top: 50px; /* Reduced gap between header and body */
            height: auto;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .results-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .results-header h2 {
            color: #c41f1f;
            font-size: 36px;
        }

        .results-content {
            line-height: 1.8;
            color: #333;
            font-size: 16px;
        }

        .results-content h3 {
            color: #c41f1f;
            font-size: 22px;
            margin-top: 20px;
        }

        .results-content ul {
            list-style: disc;
            margin-left: 20px;
        }

        .results-content ul li {
            margin-bottom: 10px;
        }

        .animation-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .result-step {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 12px;
            width: 45%;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .result-step:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .result-step h4 {
            margin: 0;
            color: #333;
        }

        .result-step p {
            margin: 10px 0 0;
            color: #555;
        }

        @media (max-width: 768px) {
            .result-step {
                width: 100%;
            }
        }
    </style>
<body>

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Main Section -->
    <main>
        <!-- Main Content: Real-Time Results Section -->
    <div class="main">
        <div class="container">
            <div class="results-header">
                <h2>Real-Time Election Results</h2>
            </div>

            <div class="results-content">
                <p>The SUSG Election System provides real-time election results to ensure transparency and maintain the confidence of all stakeholders. The system updates results in real-time as votes are tallied, providing an up-to-date snapshot of the election status.</p>

                <h3>How Real-Time Results Work:</h3>
                <ul>
                    <li><strong>Automated Vote Counting:</strong> Votes are automatically counted as they are submitted, with results instantly updated on the system.</li>
                    <li><strong>Data Encryption:</strong> All vote data is securely encrypted to prevent tampering and unauthorized access.</li>
                    <li><strong>Live Dashboard:</strong> The election dashboard provides visual analytics, such as bar charts and pie charts, to display the results in an easy-to-understand format.</li>
                    <li><strong>Instant Notifications:</strong> Candidates and voters receive instant notifications of significant updates and milestones.</li>
                </ul>

                <div class="animation-container">
                    <!-- Step 1 -->
                    <div class="result-step">
                        <h4>Step 1: Vote Submission</h4>
                        <p>Voters submit their votes via the digital voting platform, which records each vote securely.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="result-step">
                        <h4>Step 2: Real-Time Counting</h4>
                        <p>The system counts votes as they come in, updating the total count in real-time.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="result-step">
                        <h4>Step 3: Live Dashboard</h4>
                        <p>Results are displayed on a live dashboard accessible to all stakeholders.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="result-step">
                        <h4>Step 4: Secure Data Storage</h4>
                        <p>All vote data is stored securely, with backups in place to prevent data loss.</p>
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
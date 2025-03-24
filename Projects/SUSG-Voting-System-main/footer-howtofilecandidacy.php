<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - How to File Candidacy</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <script src="script/mainload.js" type="module" defer></script>
    <style>
        .main {
            padding-top: 150px;
            height: auto;
        }

        /* How to File Candidacy Section styles */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .how-to-file-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .how-to-file-header h2 {
            color: #c41f1f;
            font-size: 36px;
        }

        .step-container {
            background-color: white;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .step-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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

        @media (max-width: 768px) {
            .step-container {
                flex-direction: column;
                align-items: flex-start;
            }

            .step-number {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="container">
            <div class="how-to-file-header">
                <h2>How to File Candidacy</h2>
            </div>

            <!-- Step 1 -->
            <div class="step-container">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Certificate of Candidacy</h3>
                    <p>A candidate must file a certificate of candidacy as provided in Annex A, which must be duly signed and executed by the candidate.</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-container">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Dean/Principal's Certification</h3>
                    <p>
                        A candidate must submit a certification duly signed by the Dean of the College or any Head of the School where they are currently enrolled indicating the following:
                        <br><br>
                        i. That they are of good moral standing. In case the Dean or any head of the school refuses to issue such, without prejudice to the other succeeding requirements, a good moral certificate may also be requested from the Barangay where the candidate resides;
                        <br><br>
                        ii. That they are bona fide students of Silliman University and not members of the faculty, staff, or administration thereof;
                        <br><br>
                        iii. That they have a cumulative QPA of at least 2.50 for the President and the Vice President; and 2.25 for the representatives of the Student Assembly;
                        <br><br>
                        iv. In the case of junior and senior high school, grades in the form of percentage shall be converted to numerical rating as provided for in the Silliman University Student Manual (page 32). Both grades in the percentage form and numerical rating must be shown and indicated.
                        <br><br>
                        v. That they have a minimum load of at least 12 units.
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-container">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Certificate of Residency</h3>
                    <p>
                        Presidential and Vice Presidential candidates must submit a certificate of residency proving that they have spent at least one (1) school year in Silliman University, duly signed by the registrar or by the Dean of the College or Head of the School where they are currently enrolled. 
                        <br><br>
                        Candidates for Representatives of the Student Assembly must present a certificate of residency proving that they have spent at least one (1) semester in the College/School they are representing, duly signed by the Dean or Head of the School.
                    </p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step-container">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>URL Masterlist</h3>
                    <p>Candidates must present their official social media account names and Uniform Resource Locator (URL) in Facebook, Instagram, and YouTube in an Excel file.</p>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="step-container">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h3>Online Submission of the Requirements</h3>
                    <p>
                        Parties must compile all of the requirements specified in the immediately preceding section of the candidates running for their party, and upload the files in a Google Drive folder designated by COMELEC.
                        <br><br>
                        Submissions shall commence strictly starting from [DATE], [DAY] until [DATE], [DAY] from [TIME] to [TIME].
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>
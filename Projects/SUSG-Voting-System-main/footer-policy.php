<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Policy</title>
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

        .policy-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .policy-section h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 5px;
        }

        .policy-section h3::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #c41f1f;
        }

        .policy-section p {
            color: #555;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .policy-section ul {
            list-style-type: disc;
            padding-left: 20px;
        }

        .policy-section ul li {
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
                <h2>Data Privacy Policy</h2>
            </div>
    
            <div class="policy-section">
                <h3>1. Introduction</h3>
                <p>We value your privacy and are committed to protecting your personal information. This policy outlines how we collect, use, and protect your data in connection with the SUSG Election System.</p>
    
                <h3>2. Information We Collect</h3>
                <p>We collect information that you provide directly, such as your name, student ID, and contact details, as well as data generated during the voting process.</p>
    
                <h3>3. How We Use Your Information</h3>
                <ul>
                    <li>To ensure the integrity and security of the election process.</li>
                    <li>To verify voter eligibility and prevent fraudulent activities.</li>
                    <li>To communicate important election updates and results.</li>
                </ul>
    
                <h3>4. Data Security</h3>
                <p>We implement robust security measures to protect your data from unauthorized access, alteration, or disclosure. This includes encryption, access controls, and regular security audits.</p>
    
                <h3>5. Data Retention</h3>
                <p>Your personal information will be retained only for as long as necessary to fulfill the purposes outlined in this policy. Once the election is concluded, data will be anonymized or securely deleted.</p>
    
                <h3>6. Your Rights</h3>
                <p>You have the right to access, correct, or delete your personal information. If you have any concerns regarding your data, please contact us. </p>
    
                <h3>7. Changes to This Policy</h3>
                <p>We may update this privacy policy from time to time. Any changes will be posted on this page with a revised effective date.</p>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>
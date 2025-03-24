<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Footer</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        .footer {
            padding: 40px 0;
            position: relative; 
        }

        .footer-container {
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            flex-wrap: wrap;
            padding: 0 50px;
        }

        .footer-section {
            flex: 1;
            margin: 10px;
            text-align: left;
        }

        .footer-section h3 {
            color: black;
            font-size: 18px;
            margin-bottom: 20px;
            display: inline-block;
            padding-bottom: 5px;
            position: relative;
        }

        .footer-section h3::before {
            content: '';
            position: absolute;
            left: 0;
            bottom: -5px;
            background-color: #c41f1f;
            height: 2px;
            width: 50px;
            transition: width 0.3s ease;
        }

        .footer-section h3:hover::before {
            width: 100%;
        }

        .footer-section ul {
            list-style-type: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-section ul li a {
            color: black;
            text-decoration: none;
            font-size: 14px;
        }

        .footer-section ul li a:hover {
            color: #c41f1f;
        }

        .footer-logo {
            margin-left: 100px;
            width: 140px;
            height: 140px;
            margin-top: 25px;
        }

        .footer-section p {
            font-size: 14px;
            color: black;
        }

        .footer-social-icons a img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }

        .footer-social-icons a img:hover {
            filter: brightness(0.8);
        }

        .footer-copyright {
            text-align: center;
            width: 100%;
            padding-top: 20px;
            font-size: 16px;
            color: black;
        }

        @media (max-width: 768px) {
            .footer-container {
                flex-direction: column;
                align-items: center;
            }

            .footer-section {
                width: 100%;
                margin-bottom: 20px;
                text-align: center;
            }

            .footer-logo {
                margin: 0 auto;
                width: 100px;
                height: auto;
            }
        }

        @media (max-width: 480px) {
            .footer-section h3 {
                font-size: 16px;
            }

            .footer-section ul li a {
                font-size: 12px;
            }

            .footer-social-icons a img {
                width: 25px;
                height: 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <img src="asset/supnglogo.png" alt="Election Logo" class="footer-logo">
            </div>
            <div class="footer-section">
                <h3>About Election</h3>
                <ul>
                    <li><a href="footer-guidelines.php" target="_blank">Election Guidelines</a></li>
                    <li><a href="footer-candidateregistration.php" target="_blank">Candidate Registration</a></li>
                    <li><a href="#" target="_blank">Comelec Registration</a></li>
                    <li><a href="footer-policy.php" target="_blank">Data Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Help & Support</h3>
                <ul>
                    <li><a href="footer-faq.php" target="_blank">FAQs</a></li>
                    <li><a href="footer-voting.php" target="_blank">How to Vote</a></li>
                    <li><a href="footer-howtofilecandidacy.php" target="_blank">How to File Candidacy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Election Services</h3>
                <ul>
                    <li><a href="footer-electionmonitoring.php" target="_blank">Election Monitoring</a></li>
                    <li><a href="footer-realtime.php" target="_blank">Real-Time Results</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="footer-social-icons">
                    <a href="https://www.facebook.com/TheSUSG" target="_blank"><img src="asset/facebook.png" alt="Facebook"></a>
                    <a href="https://www.instagram.com/officialsusg/" target="_blank"><img src="asset/instagramdark.png" alt="Instagram"></a>
                    <a href="https://x.com/silliman_u" target="_blank"><img src="asset/twitter.png" alt="Twitter"></a>
                </div>
            </div>
        </div>
    </footer>
    <div class="footer-copyright">
        © 2024 Election Commission, All Rights Reserved.
    </div>
</body>
</html>
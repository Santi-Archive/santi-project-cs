<?php
if (headers_sent($file, $line)) {
    die("Headers already sent in $file on line $line.");
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch and sanitize input data
    $fullName = $_POST['fullName'];
    $collegeId = $_POST['college'];
    $positionId = $_POST['position'];
    $candidateParty = $_POST['candidateParty'];

    // Validate position_id exists in the positions table
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM positions WHERE position_id = ?");
    $stmt->execute([$positionId]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid position selected.']);
        exit();
    }

    // Insert candidate into the candidates table
    $stmt = $pdo->prepare("
        INSERT INTO candidates (candidate_name, college_id, position_id, candidate_party, qualified, remarks, candidate_image)
        VALUES (:candidate_name, :college_id, :position_id, :candidate_party, 0, NULL, NULL)
    ");

    try {
        $stmt->execute([
            ':candidate_name' => $fullName,
            ':college_id' => $collegeId,
            ':position_id' => $positionId,
            ':candidate_party' => $candidateParty
        ]);
        echo json_encode(['success' => true, 'message' => 'Candidate saved successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to save candidate data. Error: ' . $e->getMessage()]);
    }
    exit();
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

        #saveButton {
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

        .success-message {
            display: none;
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const steps = document.querySelectorAll('.step');
            const sections = document.querySelectorAll('.content-section');
            const backButton = document.getElementById('backButton');
            const saveButton = document.getElementById('saveButton');
            const nextButton = document.getElementById('nextButton');
            let currentStep = 0;

            function updateSteps() {
                steps.forEach((step, index) => {
                    step.classList.toggle('active', index <= currentStep);
                    sections[index].classList.toggle('hidden', index !== currentStep);
                });

                backButton.disabled = currentStep === 0;
                nextButton.style.display = currentStep === steps.length - 1 ? 'none' : 'inline-block';
                saveButton.style.display = currentStep === steps.length - 1 ? 'inline-block' : 'none';
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

            saveButton.addEventListener('click', async () => {
                const form = document.getElementById('candidateForm');
                const formData = new FormData(form);

                try {
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        form.reset();
                        currentStep = 0;
                        updateSteps();
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('An error occurred while saving candidate data. Please try again.');
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
        <div class="success-message">Candidate registered successfully!</div>
        <!-- Original content goes here -->
        <div class="container">
                <h1 class="title">SUSG Candidate Registration Policy</h1>

                        <!-- Steps -->
                <div class="steps">
                    <div class="step">
                        <span>1</span>
                        Instructions
                    </div>
                    <div class="step">
                        <span>2</span>
                        Sign up
                    </div>
                    <div class="step">
                        <span>3</span>
                        Upload Documents
                    </div>
                </div>

                <h2>Article II: Eligibility of Candidates and Certification of Candidacy</h2>
        
                <!-- Section 1 -->
                <section>
                    <h3>Section 1: Elective SUSG Officials</h3>
                    <p>The officials to be elected are the President, Vice President, and Representatives from the various colleges and schools of the University:</p>
                </section>
        
                <!-- Section 2 -->
                <section>
                    <h3>Section 2: Appointment of Representatives</h3>
                    <p>The following guidelines for the number of representatives per college or school shall be followed:</p>
                    <ul>
                        <li>Six (6) representatives from the College of Arts and Sciences</li>
                        <li>Two (2) representatives from the College of Agriculture</li>
                        <li>Five (5) representatives from the College of Business Administration</li>
                        <li>Two (2) representatives from the College of Computer Studies</li>
                        <li>Two (2) representatives from the College of Education</li>
                        <li>Five (5) representatives from the College of Engineering and Design</li>
                        <li>Two (2) representatives from the College of Law</li>
                        <li>Two (2) representatives from the College of Mass Communication</li>
                        <li>Two (2) representatives from the College of Nursing</li>
                        <li>Two (2) representatives from the College of Performing and Visual Arts</li>
                        <li>Two (2) representatives from the Divinity School</li>
                        <li>Two (2) representatives from the Graduate School</li>
                        <li>Three (3) representatives from the Institute of Clinical Laboratory Sciences</li>
                        <li>Two (2) representatives from the Institute of Environmental and Marine Sciences</li>
                        <li>Two (2) representatives from the Institute of Rehabilitative Sciences</li>
                        <li>Five (5) representatives from the Junior High School</li>
                        <li>Two (2) representatives from the School of Medicine</li>
                        <li>Two (2) representatives from the School of Public Affairs and Governance</li>
                        <li>Seven (7) representatives from Senior High School</li>
                    </ul>
                </section>
        
                <!-- Section 3 -->
                <section>
                    <h3>Section 3: Qualifications of SUSG Officials</h3>
                    <p>The following are the respective qualifications:</p>
                    <h4>A. The President</h4>
                    <ul>
                        <li>Residency of at least one (1) school year in Silliman University immediately preceding their election.</li>
                        <li>Bona fide student of Silliman University and not a member of the faculty, staff, or administration.</li>
                        <li>Cumulative QPA of at least 2.50.</li>
                        <li>Minimum load of at least 12 units.</li>
                    </ul>
                    <h4>B. The Vice President</h4>
                    <ul>
                        <li>Residency of at least one (1) school year in Silliman University immediately preceding their election.</li>
                        <li>Bona fide student of Silliman University and not a member of the faculty, staff, or administration.</li>
                        <li>Cumulative QPA of at least 2.50.</li>
                        <li>Minimum load of at least 12 units.</li>
                    </ul>
                    <h4>C. The Representatives</h4>
                    <ul>
                        <li>Residency of at least one (1) semester in the College or School they are representing immediately preceding their election.</li>
                        <li>Bona fide student of Silliman University and not a member of the faculty, staff, or administration.</li>
                        <li>Cumulative QPA of at least 2.25.</li>
                        <li>Minimum load of at least 12 units.</li>
                    </ul>
                </section>
        
                <!-- Section 4 -->
                <section>
                    <h3>Section 4: Requirements for Candidacy</h3>
                    <ul>
                        <li><strong>Certificate of Candidacy:</strong> Duly signed and executed by the candidate as provided in Annex A.</li>
                        <li><strong>Dean/Principal's Certification:</strong> Certification signed by the Dean or Head of School indicating good moral standing, bona fide student status, QPA, and minimum load.</li>
                        <li><strong>Certificate of Residency:</strong> Proof of residency signed by the Registrar or the Dean/Head of School.</li>
                        <li><strong>URL Masterlist:</strong> Official social media account names and URLs in an Excel file.</li>
                    </ul>
                </section>
        
                <!-- Section 5 -->
                <section>
                    <h3>Section 5: Online Submission of Requirements</h3>
                    <ul>
                        <li>Parties must compile all requirements and upload files to a Google Drive folder designated by COMELEC.</li>
                        <li>Each Party must upload files by position and college/school for Representatives.</li>
                        <li>Files must be in PDF form unless specified otherwise.</li>
                        <li>Independent candidates must follow the same guidelines.</li>
                    </ul>
                </section>
            </div>

            <div class="content-section">
                <h1>SUSG Candidate Registration</h1>
                <h2>Instructions</h2>
                <p>Rules for Filing of Candidacy:</p>
                <ul>
                    <li>Ensure that you meet the eligibility criteria before proceeding.</li>
                    <li>All information provided must be accurate and truthful.</li>
                    <li>Upload the required documents in the appropriate section.</li>
                </ul>
            </div>

        <div class="content-section">
            <h1>Fill Up Candidate Information</h1>
            <form id="candidateForm">
                <!-- <label for="suID">Student ID: </label>
                <input type="text" id="studentID" name="studentID" placeholder="Enter your Student ID" required pattern="[0-9\-]+" title="Please enter only numbers and dashes."> -->

                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
                
                <label for="college">College/Department:</label>
                <select id="college" name="college" required>
                    <option value="">Select your college</option>
                    <option value="1">CCS</option>
                    <option value="2">AGRI</option>
                    <option value="3">CAS</option>
                    <option value="4">CBA</option>
                    <option value="5">EDUC</option>
                    <option value="6">CED</option>
                    <option value="7">LAW</option>
                    <option value="8">CMC</option>
                    <option value="9">CON</option>
                    <option value="10">COPVA</option>
                    <option value="11">ICLS</option>
                    <option value="12">IEMS</option>
                    <option value="13">IRS</option>
                    <option value="14">JHS</option>
                    <option value="15-school">MEDICAL SCHOOL</option>
                    <option value="16">SPAG</option>
                    <option value="17">SHS</option>
                </select>

                <label for="position">Candidate's Position:</label>
                <select id="position" name="position" required>
                    <option value="">Select position</option>
                    <option value="1">President (Speaker)</option>
                    <option value="2-president">Vice President (Speaker Pro Tempore)</option>
                    <option value="3">Representative</option>
                </select>

                <label for="candidateParty">Candidate Party:</label>
                <input type="text" id="candidateParty" name="candidateParty" placeholder="Enter your Candidate Party" required>
            </form>
        </div>
        
        <!-- Other content sections -->
        <div class="content-section hidden">
                <h1>Upload Documents</h1>
                <h2>Submit Your Requirements</h2>
                <p>Upload the following documents in PDF format:</p>
                <ul>
                    <li>Certificate of Candidacy</li>
                    <li>Dean's Certification</li>
                    <li>Certificate of Residency</li>
                </ul>
                <input type="file" name="documents[]" multiple>
            </div>


            <!-- Navigation Buttons -->
            <div class="navigation-buttons">
            <button id="backButton">Back</button>
            <button id="nextButton">Next</button>
            <button id="saveButton">Save</button>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>
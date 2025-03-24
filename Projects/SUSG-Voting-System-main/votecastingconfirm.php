<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

$user = $_SESSION['user'];

// Redirect if user has already voted
if ($user['has_voted']) {
    header('Location: homepage.php');
    exit();
}

// Retrieve selected votes from session
$selectedVotes = isset($_SESSION['selectedVotes']) ? $_SESSION['selectedVotes'] : [];

require_once 'connect.php';

// Fetch positions from the database
$positions_stmt = $pdo->query("SELECT * FROM positions");
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Add college abbreviations mapping
$collegeAbbreviations = [
    'College of Computer Studies' => 'CCS',
    'College of Agriculture' => 'AGRI',
    'College of Arts and Science' => 'CAS',
    'College of Business Administration' => 'CBA',
    'College of Education' => 'COE',
    'College of Engineering and Design' => 'CED',
    'Law School' => 'LAW',
    'College of Mass Communication' => 'CMC',
    'College of Nursing' => 'CON',
    'College of Performing and Visual Arts' => 'COPVA',
    'Institute of Clinical Laboratory Sciences' => 'ICLS',
    'Institute of Environmental and Marine Sciences' => 'IEMS',
    'Institute of Rehabilitative Sciences' => 'IRS',
    'Junior High School' => 'JHS',
    'Medical School' => 'MED',
    'School of Public Affairs and Governance' => 'SPAG',
    'Senior High School' => 'SHS'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Confirmation Page</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
        .abstain-icon {
            background: #FEF3C7;
            width: 64px;
            height: 64px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .abstain-icon i {
            font-size: 32px;
            color: #D97706;
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>

<body class="bg-gray-50">

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Vote Confirmation Header -->
            <div class="bg-white rounded-xl shadow-xl p-8 mb-8 border-l-4 border-red-600">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Review Your Votes</h1>
                <div class="text-gray-600">
                    Please review your selections carefully before submitting your final vote.
                </div>
            </div>

            <!-- Vote Summary Box -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                    <h2 class="text-xl font-semibold text-red-800">Your Selected Candidates</h2>
                </div>
                
                <div class="p-6 grid gap-6">
                    <?php foreach ($positions as $position): ?>
                        <?php if (isset($selectedVotes[$position['position_name']])): ?>
                            <?php if ($position['position_name'] === 'Representative'): ?>
                                <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-user-tie text-red-600 mr-2"></i>
                                        <?php
                                        $userCollege = $_SESSION['user']['college_name'];
                                        $collegeAbbr = isset($collegeAbbreviations[$userCollege]) ? $collegeAbbreviations[$userCollege] : $userCollege;
                                        echo $collegeAbbr . ' Representative';
                                        ?>
                                    </h3>
                                    
                                    <?php if (empty($selectedVotes[$position['position_name']])): ?>
                                        <!-- Show abstain card -->
                                        <div class="flex items-center bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                            <div class="abstain-icon">
                                                <i class="fas fa-ban"></i>
                                            </div>
                                            <div class="ml-4">
                                                <h4 class="text-lg font-medium text-yellow-800">Abstain</h4>
                                                <p class="text-sm text-yellow-600">You chose to abstain for this position</p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Show multiple representatives -->
                                        <div class="grid gap-4">
                                            <?php foreach ($selectedVotes[$position['position_name']] as $representative): ?>
                                                <div class="flex items-center bg-white rounded-lg p-4 border border-gray-200">
                                                    <img class="w-16 h-16 rounded-lg object-cover shadow-sm" 
                                                         src="<?php echo htmlspecialchars($representative['candidate_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($representative['candidate_name']); ?>">
                                                    <div class="ml-4">
                                                        <h4 class="text-lg font-medium text-gray-800">
                                                            <?php echo htmlspecialchars($representative['candidate_name']); ?>
                                                        </h4>
                                                        <div class="flex flex-row items-center space-x-3 mt-1">
                                                            <!-- College Name -->
                                                            <div class="flex-1">
                                                                <div class="flex items-center p-2 bg-red-100 rounded-lg">
                                                                    <i class="fas fa-university text-red-800 text-lg mr-2"></i>
                                                                    <span class="text-red-800 text-base font-medium truncate">
                                                                        <?php echo htmlspecialchars($representative['college_name']); ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Party Name -->
                                                            <?php if (!empty($representative['party_name'])): ?>
                                                                <div class="flex-1">
                                                                    <div class="flex items-center p-2 <?php 
                                                                        $partyName = $representative['party_name'];
                                                                        if ($partyName === 'CAUSE') {
                                                                            echo 'bg-green-100';
                                                                        } elseif ($partyName === 'SURE') {
                                                                            echo 'bg-blue-100';
                                                                        } else {
                                                                            echo 'bg-red-100';
                                                                        }
                                                                    ?> rounded-lg">
                                                                        <i class="fas fa-users text-<?php 
                                                                            if ($partyName === 'CAUSE') {
                                                                                echo 'green';
                                                                            } elseif ($partyName === 'SURE') {
                                                                                echo 'blue';
                                                                            } else {
                                                                                echo 'red';
                                                                            }
                                                                        ?>-800 text-lg mr-2"></i>
                                                                        <span class="text-<?php 
                                                                            if ($partyName === 'CAUSE') {
                                                                                echo 'green';
                                                                            } elseif ($partyName === 'SURE') {
                                                                                echo 'blue';
                                                                            } else {
                                                                                echo 'red';
                                                                            }
                                                                        ?>-800 text-base font-medium truncate">
                                                                            <?php echo htmlspecialchars($representative['party_name']); ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-user-tie text-red-600 mr-2"></i>
                                        <?php echo htmlspecialchars($position['position_name']); ?>
                                    </h3>
                                    
                                    <?php if ($selectedVotes[$position['position_name']]['candidate_id'] == 0): ?>
                                        <!-- Abstain Card -->
                                        <div class="flex items-center bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                            <div class="abstain-icon">
                                                <i class="fas fa-ban"></i>
                                            </div>
                                            <div class="ml-4">
                                                <h4 class="text-lg font-medium text-yellow-800">Abstain</h4>
                                                <p class="text-sm text-yellow-600">You chose to abstain for this position</p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Candidate Card -->
                                        <div class="flex items-center bg-white rounded-lg p-4 border border-gray-200">
                                            <img class="w-16 h-16 rounded-lg object-cover shadow-sm" 
                                                 src="<?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?>">
                                            <div class="ml-4">
                                                <h4 class="text-lg font-medium text-gray-800">
                                                    <?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?>
                                                </h4>
                                                <div class="flex flex-row items-center space-x-3 mt-1">
                                                    <!-- College Name -->
                                                    <div class="flex-1">
                                                        <div class="flex items-center p-2 bg-red-100 rounded-lg">
                                                            <i class="fas fa-university text-red-800 text-lg mr-2"></i>
                                                            <span class="text-red-800 text-base font-medium truncate">
                                                                <?php echo htmlspecialchars($selectedVotes[$position['position_name']]['college_name']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Party Name -->
                                                    <?php if (!empty($selectedVotes[$position['position_name']]['party_name'])): ?>
                                                        <div class="flex-1">
                                                            <div class="flex items-center p-2 <?php 
                                                                $partyName = $selectedVotes[$position['position_name']]['party_name'];
                                                                if ($partyName === 'CAUSE') {
                                                                    echo 'bg-green-100';
                                                                } elseif ($partyName === 'SURE') {
                                                                    echo 'bg-blue-100';
                                                                } else {
                                                                    echo 'bg-red-100';
                                                                }
                                                            ?> rounded-lg">
                                                                <i class="fas fa-users text-<?php 
                                                                    if ($partyName === 'CAUSE') {
                                                                        echo 'green';
                                                                    } elseif ($partyName === 'SURE') {
                                                                        echo 'blue';
                                                                    } else {
                                                                        echo 'red';
                                                                    }
                                                                ?>-800 text-lg mr-2"></i>
                                                                <span class="text-<?php 
                                                                    if ($partyName === 'CAUSE') {
                                                                        echo 'green';
                                                                    } elseif ($partyName === 'SURE') {
                                                                        echo 'blue';
                                                                    } else {
                                                                        echo 'red';
                                                                    }
                                                                ?>-800 text-base font-medium truncate">
                                                                    <?php echo htmlspecialchars($selectedVotes[$position['position_name']]['party_name']); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between mt-8 gap-4">
                <button onclick="returnToVoting()" 
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg flex items-center justify-center transition duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Return to Voting
                </button>
                <button onclick="submitVotes()" 
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg flex items-center justify-center transition duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-check-circle mr-2"></i>
                    Submit Final Vote
                </button>
            </div>

            <!-- Success Message -->
            <div id="successMessage" class="hidden mt-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-2"></i>
                    <p>Your votes have been successfully submitted!</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Success Modal with Redirect -->
    <div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 text-center">
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check-circle text-4xl text-green-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Vote Submitted Successfully!</h2>
                <p class="text-gray-600">Thank you for participating in the SUSG Election.</p>
            </div>
            <div class="text-center mb-6">
                <p class="text-gray-600">Redirecting to homepage in <span id="redirectTimer" class="font-bold text-red-600">5</span> seconds...</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div id="redirectProgress" class="bg-red-600 h-2 rounded-full transition-all duration-1000" style="width: 0%"></div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <button onclick="window.location.href='review_votes.php?from=home'" 
                        class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                    <i class="fas fa-eye mr-2"></i>
                    Review Votes
                </button>
                <button onclick="redirectNow()" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                    <i class="fas fa-home mr-2"></i>
                    Go to Homepage
                </button>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>
    <script>
        function returnToVoting() {
            window.location.href = "votecasting.php";
        }

        function submitVotes() {
            if (confirm("Are you sure you want to submit your final votes? This action cannot be undone.")) {
                const submitButton = document.querySelector('button:last-child');
                const returnButton = document.querySelector('button:first-child');
                submitButton.disabled = true;
                returnButton.disabled = true;
                
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

                // Get the votes directly from the session
                const votes = <?php echo json_encode($_SESSION['selectedVotes'] ?? []); ?>;

                // Log the data being sent (for debugging)
                console.log('Submitting votes:', votes);

                fetch("submit_votes.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(votes)
                })
                .then(response => response.text())
                .then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Server response:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                })
                .then(data => {
                    if (data.success) {
                        showSuccessAndRedirect();
                    } else {
                        throw new Error(data.message || "Failed to submit votes");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Error submitting votes: " + error.message);
                    submitButton.disabled = false;
                    returnButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Submit Final Vote';
                });
            }
        }

        function showSuccessAndRedirect() {
            const modal = document.getElementById('successModal');
            const timerDisplay = document.getElementById('redirectTimer');
            const progressBar = document.getElementById('redirectProgress');
            let timeLeft = 5;
            
            modal.classList.remove('hidden');
            
            // Start countdown
            const countdown = setInterval(() => {
                timeLeft--;
                timerDisplay.textContent = timeLeft;
                progressBar.style.width = `${(5 - timeLeft) * 20}%`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    redirectNow();
                }
            }, 1000);
        }

        function redirectNow() {
            window.location.href = 'homepage.php';
        }
    </script>
</body>
</html>
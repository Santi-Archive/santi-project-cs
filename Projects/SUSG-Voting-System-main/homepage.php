<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

// Include database connection
require_once 'connect.php';

// Update the user query to include college information
$stmt = $pdo->prepare("
    SELECT students.*, colleges.college_name 
    FROM students 
    LEFT JOIN colleges ON students.college_id = colleges.college_id 
    WHERE students.student_id = :student_id
");
$stmt->execute(['student_id' => $_SESSION['user']['student_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update session with complete user data including college name
$_SESSION['user'] = $user;

// Check if the user has voted
$hasVoted = $user['has_voted'];

// Update session data with voting status
$_SESSION['user']['has_voted'] = $hasVoted;
$user['has_voted'] = $hasVoted;

// Add this after the existing database queries
$electionStmt = $pdo->query("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $electionStmt->fetch(PDO::FETCH_ASSOC);

// Pass election data to JavaScript
$electionData = $currentElection ? [
    'start_datetime' => $currentElection['start_datetime'],
    'end_datetime' => $currentElection['end_datetime'],
    'status' => $currentElection['status']
] : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Homepage</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

    <!-- Placeholder for Header -->
    <?php include 'header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="max-w-6xl mx-auto mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <div class="max-w-6xl mx-auto bg-white rounded-xl p-8
            border border-gray-200
            shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] 
            hover:shadow-[rgba(17,_17,_26,_0.1)_0px_4px_20px,_rgba(239,68,68,_0.15)_0px_4px_12px]
            transition-all duration-300">
            <div class="text-center mb-8">
                <div class="inline-block mb-4">
                    <span class="<?php echo $user['has_voted'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> text-lg font-semibold px-4 py-2 rounded-full">
                        Voting Status: <?php echo $user['has_voted'] ? 'Voted' : 'Not Voted'; ?>
                    </span>
                </div>
                <!-- Increased width from max-w-lg to max-w-2xl -->
                <div class="bg-gray-50 rounded-lg p-6 max-w-2xl mx-auto shadow-sm border border-gray-200">
                    <h1 class="text-3xl font-bold text-black-600 mb-4"><?php echo htmlspecialchars($user['student_name']); ?></h1>
                    <div class="flex flex-col space-y-2">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <p class="text-lg font-medium text-gray-700"><?php echo htmlspecialchars($user['student_id']); ?></p>
                        </div>
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <p class="text-lg font-medium text-gray-700"><?php echo htmlspecialchars($user['college_name']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update the action cards styling -->
            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-xl p-6 
                    border border-gray-200 
                    shadow-[0_2px_8px_rgba(0,0,0,0.06)]
                    hover:shadow-[0_4px_12px_rgba(0,0,0,0.08)]
                    transform hover:scale-102 transition-all duration-300">
                    <div class="mb-4">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Cast Your Vote</h3>
                        <p id="voteStatusText" class="text-gray-600">Make your voice heard in the SUSG Elections</p>
                    </div>
                    <button 
                        id="voteButton"
                        onclick="handleVoteAction()"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        Vote Now
                    </button>
                </div>

                <div class="bg-white rounded-xl p-6 
                    border border-gray-200 
                    shadow-[0_2px_8px_rgba(0,0,0,0.06)]
                    hover:shadow-[0_4px_12px_rgba(0,0,0,0.08)]
                    transform hover:scale-102 transition-all duration-300">
                    <div class="mb-4">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Live Results</h3>
                        <p class="text-gray-600">View real-time election results</p>
                    </div>
                    <button 
                        onclick="navigateTo('liveresult.php')" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        View Results
                    </button>
                </div>
            </div>

            <!-- Add modals for different election states -->
            <div id="electionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen px-4"></div>
                    <div class="bg-white rounded-lg p-8 max-w-sm w-full"></div>
                        <p id="modalMessage" class="text-xl font-semibold mb-4"></p>
                        <button onclick="closeModal()" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Placeholder for Footer -->
    <?php include 'footer.php'; ?>

    <script>
        // Preserve the original navigateTo function for the View Results button
        function navigateTo(page) {
            window.location.href = page;
        }

        // Election data from PHP
        const electionData = <?php echo json_encode($electionData); ?>;
        const hasVoted = <?php echo $user['has_voted'] ? 'true' : 'false'; ?>;
        
        function updateVoteButton() {
            const voteButton = document.getElementById('voteButton');
            const voteStatusText = document.getElementById('voteStatusText');
            const now = new Date().getTime();
            
            if (!electionData) {
                setButtonState('No election scheduled', true, 'bg-gray-500');
                return;
            }

            const startTime = new Date(electionData.start_datetime).getTime();
            const endTime = new Date(electionData.end_datetime).getTime();

            if (now < startTime) {
                setButtonState('Election has not started yet', true, 'bg-gray-500');
            } else if (now > endTime) {
                setButtonState('Election has ended', true, 'bg-gray-500');
            } else if (hasVoted) {
                setButtonState('Already Voted', true, 'bg-gray-500');
            } else {
                setButtonState('Vote Now', false, 'bg-red-600');
                voteStatusText.textContent = 'Election is ongoing - Cast your vote now!';
            }
        }

        function setButtonState(text, disabled, colorClass) {
            const button = document.getElementById('voteButton');
            button.textContent = text;
            button.disabled = disabled;
            button.className = `w-full ${colorClass} text-white font-bold py-3 px-6 rounded-lg transition duration-300 ${disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-700'}`;
        }

        function handleVoteAction() {
            if (!electionData) {
                showModal('No election is currently scheduled.');
                return;
            }

            const now = new Date().getTime();
            const startTime = new Date(electionData.start_datetime).getTime();
            const endTime = new Date(electionData.end_datetime).getTime();

            if (now < startTime) {
                showModal('Election has not started yet.');
            } else if (now > endTime) {
                showModal('Election has ended.');
            } else if (hasVoted) {
                showModal('You have already voted.');
            } else {
                window.location.href = 'votecasting.php';
            }
        }

        function showModal(message) {
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('electionModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('electionModal').classList.add('hidden');
        }

        // Initialize button state and update every minute
        updateVoteButton();
        setInterval(updateVoteButton, 60000);
    </script>
</body>
</html>
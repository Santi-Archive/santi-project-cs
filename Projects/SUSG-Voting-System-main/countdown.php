<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

// Database connection
require_once 'connect.php';

// Get fresh user data from database to ensure current voting status
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$_SESSION['user']['student_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update session with fresh user data
$_SESSION['user'] = $user;

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

// Fetch the current election's start and end times
$stmt = $pdo->query("SELECT start_datetime, end_datetime FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

// Set default values if no current election exists
$startDatetime = $currentElection ? $currentElection['start_datetime'] : null;
$endDatetime = $currentElection ? $currentElection['end_datetime'] : null;

// Pass user voting status to JavaScript
$userStatus = [
    'has_voted' => (bool)$user['has_voted'],
    'student_id' => $user['student_id']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Countdown</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes blink {
            50% { opacity: 0; }
        }
        .blink { animation: blink 1s step-start infinite; }
    </style>
    <script>
        // Add user status to window object
        window.userStatus = <?php echo json_encode($userStatus); ?>;
        
        document.addEventListener('DOMContentLoaded', function () {
            const startDatetime = "<?php echo $startDatetime; ?>";
            const endDatetime = "<?php echo $endDatetime; ?>";

            const daysEl = document.getElementById('days');
            const hoursEl = document.getElementById('hours');
            const minutesEl = document.getElementById('minutes');
            const secondsEl = document.getElementById('seconds');
            const messageEl = document.getElementById('countdown-message');
            const voteBtn = document.getElementById('vote-btn');
            const reviewBtn = document.getElementById('review-btn');

            let targetDate = null;

            // Determine which countdown to use (start or end)
            const now = new Date().getTime();
            const startTime = new Date(startDatetime).getTime();
            const endTime = new Date(endDatetime).getTime();

            if (startDatetime && now < startTime) {
                targetDate = startTime;
                messageEl.textContent = "ELECTION STARTS IN";
                messageEl.className = "bg-red-500 px-4 py-1 rounded-full";
                disableVoting('not-started');
            } else if (endDatetime && now <= endTime) {
                targetDate = endTime;
                messageEl.textContent = "ELECTION TIME REMAINING";
                messageEl.className = "bg-green-500 px-4 py-1 rounded-full";
                enableVoting();
            } else {
                messageEl.textContent = "ELECTION ENDED";
                messageEl.className = "bg-gray-500 px-4 py-1 rounded-full";
                // Immediately disable both buttons when election has ended
                [voteBtn, reviewBtn].forEach(btn => {
                    btn.classList.add('disabled', 'opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    btn.disabled = true;
                    btn.onclick = function(e) {
                        e.preventDefault();
                        showEndedPopup();
                    };
                });
                daysEl.textContent = "00";
                hoursEl.textContent = "00";
                minutesEl.textContent = "00";
                secondsEl.textContent = "00";
                return;
            }

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = targetDate - now;
                const voteBtn = document.getElementById('vote-btn');
                const reviewBtn = document.getElementById('review-btn');
                const messageEl = document.getElementById('countdown-message');

                // Check election timing status
                const isBeforeStart = startDatetime && now < new Date(startDatetime).getTime();
                const isAfterEnd = endDatetime && now > new Date(endDatetime).getTime();
                const isDuringElection = !isBeforeStart && !isAfterEnd;

                if (isBeforeStart) {
                    // Before election starts
                    messageEl.textContent = "ELECTION STARTS IN";
                    messageEl.className = "bg-blue-500 px-4 py-1 rounded-full";
                    disableVoting('not-started');
                } else if (isDuringElection) {
                    // During election
                    messageEl.textContent = "ELECTION TIME REMAINING";
                    messageEl.className = "bg-green-500 px-4 py-1 rounded-full";
                    enableVoting();
                } else {
                    // After election ends
                    messageEl.textContent = "ELECTION ENDED";
                    messageEl.className = "bg-gray-500 px-4 py-1 rounded-full";
                    disableVoting('ended');
                }

                // Update countdown display
                if (distance > 0) {
                    // Calculate time components
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Update the countdown elements
                    daysEl.textContent = days < 10 ? `0${days}` : days;
                    hoursEl.textContent = hours < 10 ? `0${hours}` : hours;
                    minutesEl.textContent = minutes < 10 ? `0${minutes}` : minutes;
                    secondsEl.textContent = seconds < 10 ? `0${seconds}` : seconds;

                    // Enable voting buttons
                    voteBtn.classList.remove("disabled");
                    reviewBtn.classList.remove("disabled");
                    voteBtn.disabled = false;
                    reviewBtn.disabled = false;
                } else {
                    // Countdown finished
                    clearInterval(countdownInterval);
                    messageEl.textContent = "VOTES ARE CLOSED";

                    // Set all countdown values to zero
                    daysEl.textContent = "00";
                    hoursEl.textContent = "00";
                    minutesEl.textContent = "00";
                    secondsEl.textContent = "00";

                    // Disable voting buttons
                    voteBtn.classList.add("disabled");
                    reviewBtn.classList.add("disabled");
                    voteBtn.disabled = true;
                    reviewBtn.disabled = true;
                }

                // Also update button states in case they changed
                updateButtonStates();
            }

            function disableVoting(state) {
                const voteBtn = document.getElementById('vote-btn');
                const reviewBtn = document.getElementById('review-btn');

                // Disable and gray out both buttons in 'not-started' and 'ended' states
                if (state === 'not-started' || state === 'ended') {
                    // Add disabled classes to both buttons
                    [voteBtn, reviewBtn].forEach(btn => {
                        btn.classList.add('disabled', 'opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                        btn.disabled = true;
                    });
                    
                    // Set appropriate click handlers based on state
                    const message = state === 'not-started' ? showNotStartedPopup : showEndedPopup;
                    voteBtn.onclick = function(e) {
                        e.preventDefault();
                        message();
                    };
                    reviewBtn.onclick = function(e) {
                        e.preventDefault();
                        message();
                    };
                    return;
                }

                // Default state handling for during election
                // ...rest of the function for normal voting period...
            }

            function enableVoting() {
                const voteBtn = document.getElementById('vote-btn');
                const reviewBtn = document.getElementById('review-btn');

                // Only enable vote button if user hasn't voted
                if (!<?php echo $user['has_voted'] ? 'true' : 'false' ?>) {
                    voteBtn.classList.remove('disabled', 'opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    voteBtn.removeAttribute('title');
                    voteBtn.onclick = null;
                } else {
                    voteBtn.classList.add('disabled', 'opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }

                // Enable review button only if user has voted
                if (<?php echo $user['has_voted'] ? 'true' : 'false' ?>) {
                    reviewBtn.classList.remove('disabled', 'opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                } else {
                    reviewBtn.classList.add('disabled', 'opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            }

            // Start the interval to update the countdown every second
            const countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown(); // Call immediately to set initial values

            // Update button states based on user voting status
            function updateButtonStates() {
                const voteBtn = document.getElementById('vote-btn');
                const reviewBtn = document.getElementById('review-btn');
                
                if (window.userStatus.has_voted) {
                    // User has voted - disable vote button, enable review button
                    voteBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    voteBtn.disabled = true;
                    voteBtn.title = 'You have already voted';
                    
                    reviewBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    reviewBtn.disabled = false;
                } else {
                    // User hasn't voted - enable vote button, disable review button
                    voteBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    voteBtn.disabled = false;
                    voteBtn.removeAttribute('title');
                    
                    reviewBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    reviewBtn.disabled = true;
                    reviewBtn.title = 'You have not voted yet';
                }
            }

            // Initial button state update
            updateButtonStates();
        });
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Countdown Container -->
        <div class="max-w-4xl mx-auto my-8">
            <!-- Enhanced Countdown Box -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-xl shadow-2xl p-8 mb-8">
                <h2 class="text-4xl font-bold mb-8 text-white text-center tracking-wide">Election Countdown</h2>
                
                <!-- Countdown Status -->
                <div class="countdown-status text-white text-xl font-semibold text-center mb-4">
                    <span id="countdown-message" class="bg-blue-500 px-4 py-1 rounded-full">
                        ELECTION COUNTDOWN
                    </span>
                </div>

                <!-- Countdown Timer Boxes -->
                <div class="countdown-box flex justify-center gap-8">
                    <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <span id="days" class="text-7xl font-bold text-red-700 block mb-3">00</span>
                        <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Days</span>
                    </div>
                    <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <span id="hours" class="text-7xl font-bold text-red-700 block mb-3">00</span>
                        <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Hours</span>
                    </div>
                    <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <span id="minutes" class="text-7xl font-bold text-red-700 block mb-3">00</span>
                        <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Minutes</span>
                    </div>
                    <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <span id="seconds" class="text-7xl font-bold text-red-700 block mb-3">00</span>
                        <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Seconds</span>
                    </div>
                </div>

                <!-- Election Times -->
                <?php if ($startDatetime && $endDatetime): ?>
                <div class="mt-6 text-center text-white">
                    <div class="text-sm">
                        Start: <span class="font-semibold"><?php echo (new DateTime($startDatetime))->format('F j, Y - g:i A'); ?></span>
                    </div>
                    <div class="text-sm">
                        End: <span class="font-semibold"><?php echo (new DateTime($endDatetime))->format('F j, Y - g:i A'); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4">
                <a href="votecasting.php" id="vote-btn" 
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-lg transition duration-300 ease-in-out transform hover:-translate-y-1 disabled:bg-gray-400 disabled:cursor-not-allowed"
                    title="<?php 
                        if ($user['has_voted']) echo 'You have already voted';
                    ?>">
                    VOTE NOW
                </a>
                <button id="review-btn"
                    onclick="checkVotingStatus2(event, <?php echo $user['has_voted'] ? 'true' : 'false'; ?>)"
                    class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-4 px-8 rounded-lg transition duration-300 ease-in-out transform hover:-translate-y-1 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    REVIEW VOTES
                </button>
            </div>
        </div>
    </main>

    <!-- Modal/Popup for Vote Status -->
    <div id="vote-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg p-8 max-w-sm w-full">
                <p class="text-xl font-semibold mb-4">You have already voted.</p>
                <button onclick="closeAllPopups()" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Modal/Popup for Review Status -->
    <div id="review-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg p-8 max-w-sm w-full">
                <p class="text-xl font-semibold mb-4">You have not voted yet.</p>
                <button onclick="closeAllPopups()" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Add new modals for different election states -->
    <div id="not-started-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg p-8 max-w-sm w-full">
                <p class="text-xl font-semibold mb-4">Election hasn't started yet.</p>
                <button onclick="closeAllPopups()" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <div id="ended-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg p-8 max-w-sm w-full">
                <p class="text-xl font-semibold mb-4">Election has ended.</p>
                <button onclick="closeAllPopups()" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Update popup handling for Tailwind
        function closeAllPopups() {
            document.getElementById('vote-popup').classList.add('hidden');
            document.getElementById('review-popup').classList.add('hidden');
            document.getElementById('not-started-popup').classList.add('hidden');
            document.getElementById('ended-popup').classList.add('hidden');
        }

        // Also close popups when clicking outside the modal
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed')) {
                closeAllPopups();
            }
        });

        function showVotePopup() {
            document.getElementById('vote-popup').classList.remove('hidden');
        }

        function showReviewPopup() {
            document.getElementById('review-popup').classList.remove('hidden');
        }

        function showNotStartedPopup() {
            document.getElementById('not-started-popup').classList.remove('hidden');
        }

        function showEndedPopup() {
            document.getElementById('ended-popup').classList.remove('hidden');
        }

        // Update voting status checks
        window.checkVotingStatus = function(event, hasVoted) {
            if (hasVoted) {
                showVotePopup();
            } else {
                window.location.href = 'votecasting.php';
            }
        };

        // Simplified voting status checks
        function checkVotingStatus2(event, hasVoted) {
            event.preventDefault();
            if (!hasVoted) {
                showReviewPopup();
            } else {
                window.location.href = 'review_votes.php';
            }
        }
    </script>
</body>
</html>
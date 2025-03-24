<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
} else {
    $user = null;
}

// Add database connection if not already included
if (!isset($pdo)) {
    require_once 'connect.php';
}

// Add election status check
$electionStmt = $pdo->query("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $electionStmt->fetch(PDO::FETCH_ASSOC);

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
    <title>SUSG Election System - Header</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Heroicons (for icons) -->
    <script src="https://unpkg.com/@heroicons/v2/24/outline/esm/index.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body, html {
            font-family: 'Poppins', sans-serif;
        }
        
        .header-menu {
            display: none;
            animation: slideIn 0.3s ease-out;
        }
        
        .header-menu.active {
            display: flex;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        .menu-item-hover {
            position: relative;
            transition: all 0.3s ease;
            overflow-x: hidden; /* Add this line */
        }

        .menu-item-hover:hover {
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.1));
            padding-left: 4px; /* Replace transform with padding change */
        }

        .menu-item-hover::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 2px;
            width: 0;
            background: white;
            transition: width 0.3s ease;
        }

        .menu-item-hover:hover::after {
            width: 100%;
        }

        .header-menu [class*="bg-"].shadow-lg {
            transition: all 0.3s ease;
        }

        .header-menu [class*="bg-"].shadow-lg:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="flex items-center bg-[#c41f1f] px-4 md:px-20 py-2">
        <img src="asset/susglogo.png" alt="Logo" class="w-20 md:w-32">
        <span class="text-white text-lg md:text-2xl font-bold ml-4">SUSG Election System</span>
        <?php if ($user): ?>
        <div class="ml-auto">
            <button class="w-10 h-10 flex flex-col justify-center items-center rounded-lg hover:bg-red-700 transition-colors duration-200" id="header-menu-toggle">
                <span class="w-6 h-0.5 bg-white rounded-full transition-all duration-200"></span>
                <span class="w-6 h-0.5 bg-white rounded-full my-1.5 transition-all duration-200"></span>
                <span class="w-6 h-0.5 bg-white rounded-full transition-all duration-200"></span>
            </button>
        </div>
        <?php endif; ?>
    </header>

    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" id="header-overlay"></div>

    <!-- Update Popup Messages -->
    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="vote-popup">
        <p class="text-gray-800 mb-4">You have already voted.</p>
        <button onclick="closeHeaderPopup('vote-popup')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="election-not-started">
        <p class="text-gray-800 mb-4">The election has not started yet.</p>
        <button onclick="closeHeaderPopup('election-not-started')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="election-ended">
        <p class="text-gray-800 mb-4">The election has ended.</p>
        <button onclick="closeHeaderPopup('election-ended')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="no-election">
        <p class="text-gray-800 mb-4">No election is currently scheduled.</p>
        <button onclick="closeHeaderPopup('no-election')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="not-voted-popup">
        <p class="text-gray-800 mb-4">You haven't cast your vote yet.</p>
        <button onclick="closeHeaderPopup('not-voted-popup')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <!-- Dropdown Menu -->
    <?php if ($user): ?>
    <nav class="header-menu fixed top-0 right-0 w-72 h-full bg-gradient-to-b from-[#811111] to-[#621111] z-50 flex flex-col" id="header-side-menu">
        <!-- Enhanced Menu Header -->
        <div class="p-6 bg-[#811111]/50 backdrop-blur-sm">
            <div class="flex items-center justify-between mb-6">
                <img src="asset/susglogo.png" alt="SUSG Logo" class="w-12 h-12 rounded-lg">
                <button onclick="closeMenu()" class="text-white/80 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <?php if ($user): ?>
                <div class="text-white">
                    <h3 class="text-xl font-bold truncate"><?php echo htmlspecialchars($user['student_name']); ?></h3>
                    <div class="mt-2 space-y-1">
                        <p class="text-sm text-white/90 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?php echo htmlspecialchars($user['student_id']); ?>
                        </p>
                        <p class="text-sm text-white/90 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <?php echo htmlspecialchars($user['college_name']); ?>
                        </p>
                    </div>
                    <div class="mt-4">
                        <div class="<?php echo $user['has_voted'] ? 'bg-green-600' : 'bg-red-600'; ?> 
                                 text-white text-sm font-semibold py-2 px-4 rounded-md 
                                 flex items-center justify-center space-x-2 
                                 shadow-lg shadow-black/10">
                            <?php if ($user['has_voted']): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            <?php endif; ?>
                            <span><?php echo $user['has_voted'] ? 'Vote Recorded' : 'Not Voted Yet'; ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Navigation -->
        <div class="flex-1 overflow-y-auto py-2">
            <ul class="space-y-1">
                <li class="menu-item-hover">
                    <a href="homepage.php" class="flex items-center space-x-3 px-6 py-3 text-white/90 hover:text-white hover:bg-white/5 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Home</span>
                    </a>
                </li>
                <li class="menu-item-hover">
                    <a href="javascript:void(0);" onclick="handleVoteClickHeader()" class="flex items-center space-x-3 px-6 py-3 text-white/90 hover:text-white hover:bg-white/5 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Vote</span>
                    </a>
                </li>
                <!-- Replace the conditional Review Votes section with this -->
                <li class="menu-item-hover">
                    <a href="javascript:void(0);" onclick="handleReviewVotesClick()" class="flex items-center space-x-3 px-6 py-3 text-white/90 hover:text-white hover:bg-white/5 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Review Votes</span>
                    </a>
                </li>
                <li class="menu-item-hover">
                    <a href="liveresult.php" class="flex items-center space-x-3 px-6 py-3 text-white/90 hover:text-white hover:bg-white/5 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Live Results</span>
                    </a>
                </li>
                <li class="menu-item-hover">
                    <a href="countdown.php" class="flex items-center space-x-3 px-6 py-3 text-white/90 hover:text-white hover:bg-white/5 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Countdown</span>
                    </a>
                </li>
                <li class="menu-item-hover">
                    <a href="feedback.php" class="flex items-center space-x-3 px-6 py-3 text-white/90 hover:text-white hover:bg-white/5 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <span>Leave a Feedback</span>
                    </a>
                </li>
                <li class="menu-item-hover mt-4">
                    <a href="logout.php?type=voter" class="flex items-center space-x-3 px-6 py-3 text-red-300 hover:text-red-200 hover:bg-white/5 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Menu Footer -->
        <div class="p-4 bg-[#811111]/30 backdrop-blur-sm">
            <div class="text-white/60 text-xs text-center">
                <p>SUSG Election System</p>
                <p class="mt-1">© <?php echo date('Y'); ?> All rights reserved</p>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- JavaScript for toggling menu and popup message -->
    <script>
        // Global variables for election data
        const headerElectionData = <?php echo json_encode($electionData); ?>;
        const headerHasVoted = <?php echo isset($user['has_voted']) ? ($user['has_voted'] ? 'true' : 'false') : 'false'; ?>;

        function handleVoteClickHeader() {
            const now = new Date().getTime();
            
            if (!headerElectionData) {
                showHeaderPopup('no-election');
                return;
            }

            const startTime = new Date(headerElectionData.start_datetime).getTime();
            const endTime = new Date(headerElectionData.end_datetime).getTime();

            if (headerHasVoted) {
                showHeaderPopup('vote-popup');
            } else if (now < startTime) {
                showHeaderPopup('election-not-started');
            } else if (now > endTime) {
                showHeaderPopup('election-ended');
            } else {
                window.location.href = 'votecasting.php';
            }
        }

        // Add this new function to handle Review Votes click
        function handleReviewVotesClick() {
            if (!headerHasVoted) {
                showHeaderPopup('not-voted-popup');
            } else {
                window.location.href = 'review_votes.php?from=home';
            }
        }

        function showHeaderPopup(popupId) {
            const popup = document.getElementById(popupId);
            const overlay = document.getElementById('header-overlay');
            if (popup && overlay) {
                popup.classList.remove('hidden');
                overlay.classList.remove('hidden');
                sideMenu.classList.add('hidden'); // Close the menu when showing popup
            }
        }

        function closeHeaderPopup(popupId) {
            const popup = document.getElementById(popupId);
            const overlay = document.getElementById('header-overlay');
            if (popup && overlay) {
                popup.classList.add('hidden');
                overlay.classList.add('hidden');
            }
        }

        function closeMenu() {
            const sideMenu = document.getElementById('header-side-menu');
            const overlay = document.getElementById('header-overlay');
            sideMenu.classList.add('hidden');
            sideMenu.classList.remove('active');
            overlay.classList.add('hidden');
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('header-menu-toggle');
            const sideMenu = document.getElementById('header-side-menu');
            const overlay = document.getElementById('header-overlay');

            menuToggle.addEventListener('click', function() {
                sideMenu.classList.toggle('hidden');
                sideMenu.classList.toggle('active');
                overlay.classList.toggle('hidden');
            });

            // Close everything when clicking overlay
            overlay.addEventListener('click', function() {
                sideMenu.classList.add('hidden');
                sideMenu.classList.remove('active');
                overlay.classList.add('hidden');
                document.querySelectorAll('.popup').forEach(popup => {
                    popup.classList.add('hidden');
                });
            });

            // Update popup close buttons
            document.querySelectorAll('.popup button').forEach(button => {
                button.onclick = function() {
                    const popupId = this.closest('.popup').id;
                    closeHeaderPopup(popupId);
                };
            });
        });
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Get current election with full details
$stmt = $pdo->query("
    SELECT * FROM elections 
    WHERE is_current = 1 
    LIMIT 1
");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentElection) {
    die("Please set a current election first before viewing results.");
}

// Store datetime values for JavaScript
$startDatetime = $currentElection['start_datetime'];
$endDatetime = $currentElection['end_datetime'];

// Fetch positions from the database
$positionsStmt = $pdo->query("SELECT * FROM positions");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch colleges for the dropdown
$collegesStmt = $pdo->query("SELECT * FROM colleges WHERE college_name != 'Abstain' ORDER BY college_name");
$colleges = $collegesStmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get live results for current election
function getLiveResults($pdo, $positionId, $electionId) {
    $stmt = $pdo->prepare("
        SELECT 
            c.candidate_id,
            c.candidate_name,
            c.candidate_image,
            c.college_id,
            co.college_name,
            pa.party_name,
            COUNT(v.vote_id) as vote_count,
            (SELECT COUNT(*) FROM votes 
             WHERE position_id = :position_id 
             AND election_id = :election_id) as total_position_votes
        FROM candidates c
        LEFT JOIN colleges co ON c.college_id = co.college_id
        LEFT JOIN parties pa ON c.party_id = pa.party_id
        LEFT JOIN votes v ON c.candidate_id = v.candidate_id 
            AND v.election_id = :election_id
        WHERE c.position_id = :position_id 
        AND c.election_id = :election_id
        GROUP BY 
            c.candidate_id, 
            c.candidate_name, 
            c.candidate_image, 
            c.college_id,
            co.college_name,
            pa.party_name
        ORDER BY vote_count DESC
    ");
    
    $stmt->execute([
        ':position_id' => $positionId,
        ':election_id' => $electionId
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Create API endpoint for fetching results
if (isset($_GET['fetch_results'])) {
    header('Content-Type: application/json');
    $positionId = $_GET['position_id'] ?? null;
    $electionId = $_GET['election_id'] ?? null;
    
    if ($positionId && $electionId) {
        $results = getLiveResults($pdo, $positionId, $electionId);
        echo json_encode($results);
    } else {
        echo json_encode(['error' => 'Missing parameters']);
    }
    exit;
}

// Start output buffering if needed
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Live Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
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
        .vote-counter {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 0.25rem 0.75rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            min-width: 80px;
        }
        .position-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .position-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background: #f3f4f6;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
            transition: width 0.5s ease-out;
        }
        .results-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .candidate-card {
            border-left: 4px solid #ef4444;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 1rem;
        }
        .progress-container {
            margin-top: 0.5rem;
            width: 100%;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const resultsContainer = document.querySelector(".results");
            const currentPositionElement = document.querySelector(".current-position");
            const countdownBox = document.querySelector('.countdown-box');
            const collegeFilter = document.getElementById('collegeFilter');
            const collegeSelect = document.getElementById('collegeSelect');
            let currentPositionId = null;

            function fetchResults(positionId, positionName) {
                currentPositionId = positionId;
                const collegeId = collegeSelect.value;
                let url = `fetch_results.php?position_id=${positionId}&election_id=<?php echo $currentElection['election_id']; ?>`;
                
                // Add college_id to URL if filtering and position is Representative
                if (collegeId && positionName.includes('Representative')) {
                    url += `&college_id=${collegeId}`;
                }

                // Show/hide college filter based on position
                collegeFilter.style.display = positionName.includes('Representative') ? 'block' : 'none';

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Fetched data:', data); // Debug log
                        updateResults(data, positionName);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultsContainer.innerHTML = '<div class="text-red-600">Error loading results</div>';
                    });
            }

            function updateResults(candidates, positionName) {
                resultsContainer.innerHTML = "";
                currentPositionElement.textContent = positionName;

                if (!candidates || candidates.length === 0) {
                    resultsContainer.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-users-slash text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-600">No candidates found for this position</p>
                        </div>`;
                    return;
                }

                const resultsGrid = document.createElement('div');
                resultsGrid.className = 'results-grid';

                candidates.forEach((candidate, index) => {
                    const resultElement = document.createElement("div");
                    resultElement.className = "candidate-card bg-white rounded-xl shadow-md p-6";
                    
                    const voteCount = parseInt(candidate.vote_count) || 0;
                    const percentage = candidate.percentage || 0;
                    const isLeading = index === 0 && voteCount > 0;

                    let candidateContent = `
                        <div class="relative ${isLeading ? 'pb-4' : ''}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 flex-grow">
                                    ${candidate.candidate_name === 'Abstain' ? `
                                        <div class="abstain-icon">
                                            <i class="fas fa-ban"></i>
                                        </div>
                                    ` : `
                                        <div class="relative">
                                            <img class="h-16 w-16 rounded-lg object-cover border-2 border-red-600" 
                                                 src="../${candidate.candidate_image}" 
                                                 alt="${candidate.candidate_name}">
                                            ${isLeading ? '<div class="absolute -top-2 -right-2 bg-yellow-400 text-white p-1.5 rounded-full shadow-lg transform -translate-y-1/4 translate-x-1/4"><i class="fas fa-crown text-sm"></i></div>' : ''}
                                        </div>
                                    `}
                                    <div class="flex-grow">
                                        <h3 class="text-lg font-bold ${candidate.candidate_name === 'Abstain' ? 'text-yellow-800' : 'text-gray-800'}">${candidate.candidate_name}</h3>
                                        ${candidate.candidate_name !== 'Abstain' ? `
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                    ${candidate.college_name || 'N/A'}
                                                </span>
                                                <span class="px-2 py-1 ${
                                                    candidate.party_name === 'CAUSE' ? 'bg-green-100 text-green-800' :
                                                    candidate.party_name === 'SURE' ? 'bg-blue-100 text-blue-800' :
                                                    'bg-red-100 text-red-800'
                                                } text-xs font-medium rounded-full">
                                                    ${candidate.party_name || 'Independent'}
                                                </span>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="vote-counter">
                                    <span class="text-xl font-bold text-white">${voteCount}</span>
                                    <span class="text-xs text-white opacity-90">votes</span>
                                </div>
                            </div>
                            <div class="progress-container">    
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${percentage}%"></div>
                                </div>
                                <div class="text-right text-sm text-gray-500 mt-1">
                                    ${percentage}%
                                </div>
                            </div>
                        </div>
                    `;

                    resultElement.innerHTML = candidateContent;
                    resultsGrid.appendChild(resultElement);
                });

                resultsContainer.appendChild(resultsGrid);
            }

            // Initialize with first position
            fetchResults(<?php echo $positions[0]['position_id']; ?>, "<?php echo $positions[0]['position_name']; ?>");

            // Countdown timer functionality
            <?php if ($startDatetime && $endDatetime): ?>
            const startDatetime = new Date("<?php echo $startDatetime; ?>").getTime();
            const endDatetime = new Date("<?php echo $endDatetime; ?>").getTime();
            <?php else: ?>
            const startDatetimeVar = null;
            const endDatetimeVar = null;
            <?php endif; ?>

            function updateCountdown() {
                if (!startDatetime || !endDatetime) {
                    countdownBox.innerHTML = '<div class="text-center text-white text-3xl font-bold py-12">No election scheduled.</div>';
                    return;
                }

                const now = new Date().getTime();
                const status = document.querySelector('.countdown-status span');
                
                // Determine which countdown to show
                let distance;
                if (now < startDatetime) {
                    // Count down to election start
                    distance = startDatetime - now;
                    status.textContent = 'ELECTION STARTS IN';
                    status.className = 'bg-blue-500 px-4 py-1 rounded-full';
                } else if (now <= endDatetime) {
                    // Count down to election end
                    distance = endDatetime - now;
                    status.textContent = 'ELECTION TIME REMAINING';
                    status.className = 'bg-green-500 px-4 py-1 rounded-full';
                } else {
                    // Election has ended
                    status.textContent = 'ELECTION ENDED';
                    status.className = 'bg-gray-500 px-4 py-1 rounded-full';
                    document.querySelector('.days').textContent = '00';
                    document.querySelector('.hours').textContent = '00';
                    document.querySelector('.minutes').textContent = '00';
                    document.querySelector('.seconds').textContent = '00';
                    return;
                }

                // Calculate time components
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Update the display with padded numbers
                document.querySelector('.days').textContent = String(days).padStart(2, '0');
                document.querySelector('.hours').textContent = String(hours).padStart(2, '0');
                document.querySelector('.minutes').textContent = String(minutes).padStart(2, '0');
                document.querySelector('.seconds').textContent = String(seconds).padStart(2, '0');
            }

            const countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown();

            // Position button event listeners
            document.querySelectorAll('.position-button').forEach(button => {
                button.addEventListener('click', function() {
                    const positionId = this.getAttribute('data-position-id');
                    const positionName = this.textContent;
                    
                    // Remove active class from all buttons
                    document.querySelectorAll('.position-button').forEach(btn => {
                        btn.classList.remove('bg-red-700', 'ring-2', 'ring-red-600');
                        btn.classList.add('bg-red-600');
                    });
                    
                    // Add active class to clicked button
                    this.classList.remove('bg-red-600');
                    this.classList.add('bg-red-700', 'ring-2', 'ring-red-600');
                    
                    fetchResults(positionId, positionName);
                });
            });

            // Add college filter change event
            collegeSelect.addEventListener('change', function() {
                if (currentPositionId) {
                    fetchResults(currentPositionId, document.querySelector('.current-position').textContent);
                }
            });

            // Add auto-refresh functionality
            setInterval(() => {
                if (currentPositionId) {
                    fetchResults(currentPositionId, document.querySelector('.current-position').textContent);
                }
            }, 30000); // Refresh every 30 seconds
        });
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Live Election Results</h1>
                <div class="text-sm text-gray-600">
                    Current Election: <span class="font-semibold text-red-600"><?php echo htmlspecialchars($currentElection['election_name']); ?></span>
                </div>
            </div>

            <!-- Countdown Section -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-xl shadow-2xl p-8 mb-8">
                <h2 class="text-4xl font-bold mb-8 text-white text-center tracking-wide">Election Countdown</h2>
                <?php if ($currentElection): ?>
                    <div class="countdown-status text-white text-xl font-semibold text-center mb-4">
                        <?php 
                        $now = new DateTime();
                        $start = new DateTime($currentElection['start_datetime']);
                        $end = new DateTime($currentElection['end_datetime']);
                        
                        if ($now < $start): ?>
                            <span class="bg-blue-500 px-4 py-1 rounded-full">Election Starts In</span>
                        <?php elseif ($now <= $end): ?>
                            <span class="bg-green-500 px-4 py-1 rounded-full">Election Time Remaining</span>
                        <?php else: ?>
                            <span class="bg-gray-500 px-4 py-1 rounded-full">Election Ended</span>
                        <?php endif; ?>
                    </div>
                    <div class="countdown-box flex justify-center gap-8">
                        <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span class="days text-7xl font-bold text-red-700 block mb-3">00</span>
                            <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Days</span>
                        </div>
                        <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span class="hours text-7xl font-bold text-red-700 block mb-3">00</span>
                            <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Hours</span>
                        </div>
                        <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span class="minutes text-7xl font-bold text-red-700 block mb-3">00</span>
                            <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Minutes</span>
                        </div>
                        <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span class="seconds text-7xl font-bold text-red-700 block mb-3">00</span>
                            <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Seconds</span>
                        </div>
                    </div>
                    <div class="mt-6 text-center text-white">
                        <div class="text-sm">
                            Start: <span class="font-semibold"><?php echo (new DateTime($currentElection['start_datetime']))->format('F j, Y - g:i A'); ?></span>
                        </div>
                        <div class="text-sm">
                            End: <span class="font-semibold"><?php echo (new DateTime($currentElection['end_datetime']))->format('F j, Y - g:i A'); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center text-white text-3xl font-bold py-12">No election scheduled.</div>
                <?php endif; ?>
            </div>

            <!-- Results Section -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                    <i class="fas fa-chart-bar text-red-600 mr-2"></i>
                    Position Results
                </h2>
                
                <!-- Position Buttons -->
                <div class="flex flex-wrap gap-2 mb-8">
                    <?php foreach ($positions as $position): ?>
                        <button class="position-button transition-all duration-300 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transform hover:scale-105 flex items-center"
                                data-position-id="<?php echo htmlspecialchars($position['position_id']); ?>">
                            <i class="fas fa-user-tie mr-2"></i>
                            <?php echo htmlspecialchars($position['position_name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- College Filter -->
                <div id="collegeFilter" class="hidden mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter by College:</label>
                    <select id="collegeSelect" class="form-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 transition">
                        <option value="">All Colleges</option>
                        <?php foreach ($colleges as $college): ?>
                            <option value="<?php echo htmlspecialchars($college['college_id']); ?>">
                                <?php echo htmlspecialchars($college['college_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Current Position Display -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="current-position text-xl font-semibold text-red-600"></h3>
                    <div class="text-sm text-gray-500">
                        Auto-refreshing every 30 seconds
                        <i class="fas fa-sync-alt ml-2 animate-spin"></i>
                    </div>
                </div>

                <!-- Results Container -->
                <div class="results space-y-4">
                    <!-- Results will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </main>
</body>
</html>
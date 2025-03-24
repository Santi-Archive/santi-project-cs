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

require_once 'connect.php';

// Get current election
$stmt = $pdo->query("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    header('Location: homepage.php');
    exit();
}

// Check if user has already voted
if ($user['has_voted']) {
    header('Location: homepage.php');
    exit();
}

// Pass election times to JavaScript
$electionTimes = [
    'start' => $election['start_datetime'],
    'end' => $election['end_datetime']
];

// Fetch candidates from the database
function fetchCandidates($pdo, $position_id) {
    $stmt = $pdo->prepare("
        SELECT candidates.*, colleges.college_name, positions.position_name, candidates.candidate_image, candidates.candidate_party 
        FROM candidates 
        LEFT JOIN colleges ON candidates.college_id = colleges.college_id 
        LEFT JOIN positions ON candidates.position_id = positions.position_id 
        WHERE candidates.position_id = :position_id AND candidates.qualified = 1
    ");
    $stmt->execute(['position_id' => $position_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch positions from the database
$positions_stmt = $pdo->query("SELECT * FROM positions");
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get student's college and max representatives allowed
$collegeStmt = $pdo->prepare("
    SELECT c.college_id, c.max_representatives 
    FROM colleges c 
    JOIN students s ON c.college_id = s.college_id 
    WHERE s.student_id = ?
");
$collegeStmt->execute([$_SESSION['user']['student_id']]);
$collegeInfo = $collegeStmt->fetch(PDO::FETCH_ASSOC);

// Modify the candidates query to filter representatives by college
$candidatesStmt = $pdo->prepare("
    SELECT c.*, col.college_name, p.party_name, pos.position_name 
    FROM candidates c
    JOIN colleges col ON c.college_id = col.college_id
    LEFT JOIN parties p ON c.party_id = p.party_id
    JOIN positions pos ON c.position_id = pos.position_id
    WHERE c.election_id = ? AND c.qualified = 1
    AND (pos.position_name != 'Representative' OR 
        (pos.position_name = 'Representative' AND c.college_id = ?))
    ORDER BY pos.position_id, c.candidate_name
");
$candidatesStmt->execute([$election['election_id'], $collegeInfo['college_id']]);
$candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);

// Group candidates by position
$candidatesByPosition = [];
foreach ($candidates as $candidate) {
    $candidatesByPosition[$candidate['position_name']][] = $candidate;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Vote Casting</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="script/load.js" type="module" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script>
        // Check if current time is within election period
        const electionTimes = <?php echo json_encode($electionTimes); ?>;
        const now = new Date();
        const startTime = new Date(electionTimes.start);
        const endTime = new Date(electionTimes.end);

        if (now < startTime || now > endTime) {
            window.location.href = 'homepage.php';
        }
    </script>
</head>

<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <main class="h-full p-8 mb-16">
        <div class="max-w-7xl mx-auto">
            <!-- Vote Casting Header -->
            <div class="bg-white rounded-xl shadow-xl p-8 mb-8 border-l-4 border-red-600">
                <h1 id="mainTitle" class="text-3xl font-bold text-gray-800 mb-4"></h1>
                <div class="text-gray-600">
                    Please select your candidate for each position carefully.
                </div>
            </div>

            <!-- Voting Area -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div id="candidatesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Candidates will be loaded here dynamically -->
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center space-x-4">
                <button class="nav-btn back-btn bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg flex items-center transition duration-300 transform hover:-translate-y-1" onclick="goBack()">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
                <button class="nav-btn abstain-btn bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg flex items-center transition duration-300 transform hover:-translate-y-1" onclick="abstainVote()">
                    <i class="fas fa-ban mr-2"></i> Abstain
                </button>
                <button class="nav-btn next-btn bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg flex items-center transition duration-300 transform hover:-translate-y-1" onclick="goNext()">
                    Next <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>

            <!-- Vote Summary -->
            <div id="summaryContainer" class="hidden bg-white rounded-xl shadow-xl p-8 mt-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Summary of Your Votes</h2>
                <ul id="summaryList" class="space-y-4">
                    <!-- Summary items will be loaded here dynamically -->
                </ul>
                <div class="mt-8 flex justify-center">
                    <button onclick="submitVotes()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:-translate-y-1 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> Submit Votes
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        const positions = <?php echo json_encode($positions); ?>;
        const maxRepresentatives = <?php echo $collegeInfo['max_representatives']; ?>;
        let currentPositionIndex = 0;
        const selectedVotes = {};
        let hasAbstained = false;
        let isAbstainingForRepresentatives = false;

        function fetchCandidates(position_id) {
            return fetch(`fetch_candidates.php?position_id=${position_id}`)
                .then(response => response.json())
                .then(candidates => {
                    console.log(candidates); // Log fetched candidates
                    return candidates;
                });
        }

        // Add this college abbreviations mapping right after the script tag starts
        const collegeAbbreviations = {
            'College of Computer Studies': 'CCS',
            'College of Agriculture': 'AGRI',
            'College of Arts and Science': 'CAS',
            'College of Business Administration': 'CBA',
            'College of Education': 'COE',
            'College of Engineering and Design': 'CED',
            'Law School': 'LAW',
            'College of Mass Communication': 'CMC',
            'College of Nursing': 'CON',
            'College of Performing and Visual Arts': 'COPVA',
            'Institute of Clinical Laboratory Sciences': 'ICLS',
            'Institute of Environmental and Marine Sciences': 'IEMS',
            'Institute of Rehabilitative Sciences': 'IRS',
            'Junior High School': 'JHS',
            'Medical School': 'MED',
            'School of Public Affairs and Governance': 'SPAG',
            'Senior High School': 'SHS'
        };

        // Modify the displayPosition function to handle multiple selections for representatives
        function displayPosition() {
            const position = positions[currentPositionIndex];
            if (position.position_name === 'Representative') {
                // Get user's college from PHP session
                const userCollege = <?php echo json_encode($_SESSION['user']['college_name']); ?>;
                const collegeAbbr = collegeAbbreviations[userCollege] || userCollege;
                document.getElementById("mainTitle").textContent = `${collegeAbbr} Representative`;
            } else {
                document.getElementById("mainTitle").textContent = position.position_name;
            }

            // Add max representatives indicator for Representative position
            const titleContainer = document.getElementById("mainTitle").parentElement;
            const existingIndicator = document.querySelector('.max-reps-indicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }

            if (position.position_name === 'Representative') {
                const indicator = document.createElement('div');
                indicator.className = 'max-reps-indicator mt-2 text-gray-600';
                const selectedCount = selectedVotes['Representative']?.length || 0;
                indicator.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span>Select up to ${maxRepresentatives} representatives</span>
                        <span class="font-semibold text-red-600">${selectedCount}/${maxRepresentatives} selected</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-red-600 h-2 rounded-full transition-all duration-300" 
                             style="width: ${(selectedCount / maxRepresentatives) * 100}%"></div>
                    </div>
                `;
                titleContainer.appendChild(indicator);
            }

            fetchCandidates(position.position_id).then(candidates => {
                const candidatesContainer = document.getElementById("candidatesContainer");
                candidatesContainer.innerHTML = "";

                // Check if there are no candidates
                if (!candidates || candidates.length === 0) {
                    candidatesContainer.innerHTML = `
                        <div class="col-span-3 text-center p-8 bg-yellow-50 rounded-xl border-2 border-yellow-200">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-5xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-yellow-800 mb-2">No Candidates Available</h3>
                            <p class="text-yellow-700 mb-4">There are no candidates for this position. 
                            <br> Press Abstain to Continue <br></p>
                        </div>`;
                    return;
                }

                candidates.forEach((candidate, index) => {
                    const candidateCard = document.createElement("div");
                    candidateCard.classList.add(
                        "candidate-card",
                        "bg-white",
                        "rounded-xl",
                        "shadow-lg",
                        "overflow-hidden",
                        "transition-all",
                        "duration-300",
                        "hover:shadow-2xl",
                        "transform",
                        "hover:-translate-y-2",
                        "cursor-pointer",
                        "relative"
                    );
                    candidateCard.dataset.index = index;

                    candidateCard.innerHTML = `
                        <div class="relative aspect-w-4 aspect-h-3">
                            <img class="w-full h-64 object-cover object-center" src="${candidate.candidate_image}" alt="${candidate.candidate_name}">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            <div class="absolute top-0 right-0 m-2">
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded-full shadow-md">
                                    ${candidate.college_name}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">${candidate.candidate_name}</h3>
                            <p class="text-gray-600">${candidate.party_name}</p>
                        </div>
                        <div class="selected-overlay hidden absolute inset-0 bg-red-600/20 rounded-xl">
                            <div class="absolute bottom-4 right-4 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <i class="fas fa-check text-lg"></i>
                            </div>
                        </div>
                    `;

                    // Modify the selection handling for representatives
                    if (position.position_name === 'Representative') {
                        candidateCard.addEventListener("click", () => {
                            const currentSelections = selectedVotes['Representative'] || [];
                            const isSelected = candidateCard.querySelector('.selected-overlay').classList.contains('hidden');
                            
                            if (isSelected && currentSelections.length >= maxRepresentatives) {
                                alert(`You can only select up to ${maxRepresentatives} representatives.`);
                                return;
                            }

                            const overlay = candidateCard.querySelector('.selected-overlay');
                            if (isSelected) {
                                // Add selection
                                overlay.classList.remove('hidden');
                                candidateCard.classList.add('ring-4', 'ring-red-600', 'ring-opacity-50');
                                selectedVotes['Representative'] = [...currentSelections, candidate];
                            } else {
                                // Remove selection
                                overlay.classList.add('hidden');
                                candidateCard.classList.remove('ring-4', 'ring-red-600', 'ring-opacity-50');
                                selectedVotes['Representative'] = currentSelections.filter(c => c.candidate_id !== candidate.candidate_id);
                            }
                            
                            // Update the indicator
                            const indicator = document.querySelector('.max-reps-indicator');
                            const selectedCount = selectedVotes['Representative'].length;
                            indicator.querySelector('span:last-child').textContent = `${selectedCount}/${maxRepresentatives} selected`;
                            indicator.querySelector('.bg-red-600').style.width = `${(selectedCount / maxRepresentatives) * 100}%`;
                        });
                    } else {
                        candidateCard.addEventListener("click", () => selectCandidate(candidateCard, candidate, position.position_name));
                    }
                    candidatesContainer.appendChild(candidateCard);

                    // Show selection if previously selected
                    if (selectedVotes[position.position_name] && 
                        selectedVotes[position.position_name].candidate_id === candidate.candidate_id) {
                        const overlay = candidateCard.querySelector('.selected-overlay');
                        overlay.classList.remove('hidden');
                        candidateCard.classList.add('ring-4', 'ring-red-600', 'ring-opacity-50');
                    }
                });
            });
        }

        function selectCandidate(candidateCard, candidate, positionName) {
            const candidatesContainer = document.getElementById("candidatesContainer");
            const allCards = candidatesContainer.querySelectorAll('.candidate-card');
            const overlay = candidateCard.querySelector('.selected-overlay');
            
            // Check if this candidate is already selected
            const isSelected = !overlay.classList.contains('hidden');
            
            // Remove selection from all cards first
            allCards.forEach(card => {
                card.querySelector('.selected-overlay').classList.add('hidden');
                card.classList.remove('ring-4', 'ring-red-600', 'ring-opacity-50');
            });

            if (!isSelected) {
                // Select the new candidate if it wasn't previously selected
                overlay.classList.remove('hidden');
                candidateCard.classList.add('ring-4', 'ring-red-600', 'ring-opacity-50');
                selectedVotes[positionName] = candidate;
            } else {
                // If it was already selected, deselect it
                selectedVotes[positionName] = null;
            }
            
            updateSelectionState();
        }

        // Modify the abstainVote function to handle representatives
        function abstainVote() {
            const position = positions[currentPositionIndex].position_name;
            const candidatesContainer = document.getElementById("candidatesContainer");
            const allCards = candidatesContainer.querySelectorAll('.candidate-card');
            
            // Position-specific confirmation messages
            const confirmMessages = {
                'President': 'Are you sure you want to abstain from voting for any presidential candidates?',
                'Vice President': 'Are you sure you want to abstain from voting for any vice-presidential candidates?',
                'Representative': 'Are you sure you want to abstain from voting for any representatives?'
            };
            
            const confirmMessage = confirmMessages[position] || `Are you sure you want to abstain from voting for ${position}?`;
            
            if (confirm(confirmMessage)) {
                allCards.forEach(card => {
                    card.querySelector('.selected-overlay').classList.add('hidden');
                    card.classList.remove('ring-4', 'ring-red-600', 'ring-opacity-50');
                });
                
                if (position === 'Representative') {
                    hasAbstained = true;
                    selectedVotes[position] = [];
                } else {
                    selectedVotes[position] = { 
                        candidate_id: 0,
                        candidate_name: 'Abstain',
                        college_name: 'Abstain',
                        candidate_image: 'abstain-icon'
                    };
                }
                goNext();
            }
        }

        function updateSelectionState() {
            const position = positions[currentPositionIndex].position_name;
            const candidatesContainer = document.getElementById("candidatesContainer");
            const selectedCard = candidatesContainer.querySelector(".candidate-card.selected");

            if (selectedVotes[position] === "Abstain") {
                document.querySelector(".abstain-btn").classList.add("selected");
                if (selectedCard) {
                    selectedCard.classList.remove("selected");
                }
            } else {
                document.querySelector(".abstain-btn").classList.remove("selected");
            }
        }

        // Modify the goNext function to enforce vote selection
        function goNext() {
            const position = positions[currentPositionIndex].position_name;
            
            if (position === 'Representative' && !hasAbstained) {
                const selectedReps = selectedVotes[position]?.length || 0;
                if (selectedReps === 0 ) {
                    alert('Please select at least one representative');
                    return;
                }
            } else if (!selectedVotes[position]) {
                alert("Please select a candidate to proceed.");
                return;
            }

            if (currentPositionIndex < positions.length - 1) {
                currentPositionIndex++;
                displayPosition();
            } else {
                // Check if all positions have votes
                const hasAllVotes = positions.every(pos => {
                    if (pos.position_name === 'Representative') {
                        return selectedVotes[pos.position_name]?.length >= 0;
                    }
                    return selectedVotes[pos.position_name] !== undefined && selectedVotes[pos.position_name] !== null;
                });

                if (!hasAllVotes) {
                    alert("Please ensure you have selected a candidate or abstained for all positions.");
                    return;
                }

                // Proceed with vote submission
                fetch("store_votes.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        votes: selectedVotes,
                        isConfirmation: true
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "votecastingconfirm.php";
                    } else {
                        alert("Failed to store votes: " + (data.message || "Unknown error"));
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while storing votes.");
                });
            }
        }

        function goBack() {
            if (currentPositionIndex > 0) {
                // Reset the current position's selections if it's Representatives
                const currentPosition = positions[currentPositionIndex].position_name;
                if (currentPosition === 'Representative') {
                    selectedVotes['Representative'] = [];
                    const indicator = document.querySelector('.max-reps-indicator');
                    if (indicator) {
                        indicator.querySelector('span:last-child').textContent = `0/${maxRepresentatives} selected`;
                        indicator.querySelector('.bg-red-600').style.width = '0%';
                    }
                }
                
                // Go back to previous position
                currentPositionIndex--;
                displayPosition();
                
                // Clear any selections on the previous page
                const allCards = document.querySelectorAll('.candidate-card');
                allCards.forEach(card => {
                    card.querySelector('.selected-overlay').classList.add('hidden');
                    card.classList.remove('ring-4', 'ring-red-600', 'ring-opacity-50');
                });
                
                // Show previous selections if they exist
                const prevPosition = positions[currentPositionIndex].position_name;
                if (selectedVotes[prevPosition]) {
                    if (Array.isArray(selectedVotes[prevPosition])) {
                        // Handle representative selections
                        selectedVotes[prevPosition].forEach(candidate => {
                            const candidateCard = Array.from(allCards).find(
                                card => card.querySelector('h3').textContent.trim() === candidate.candidate_name
                            );
                            if (candidateCard) {
                                candidateCard.querySelector('.selected-overlay').classList.remove('hidden');
                                candidateCard.classList.add('ring-4', 'ring-red-600', 'ring-opacity-50');
                            }
                        });
                    } else {
                        // Handle single candidate selection
                        const candidateCard = Array.from(allCards).find(
                            card => card.querySelector('h3').textContent.trim() === selectedVotes[prevPosition].candidate_name
                        );
                        if (candidateCard) {
                            candidateCard.querySelector('.selected-overlay').classList.remove('hidden');
                            candidateCard.classList.add('ring-4', 'ring-red-600', 'ring-opacity-50');
                        }
                    }
                }
            }
        }

        function showSummary() {
            console.log("Showing summary"); // Debugging log
            document.querySelector(".vote-casting-container").style.display = "none";
            const summaryContainer = document.getElementById("summaryContainer");
            summaryContainer.style.display = "flex";

            const summaryList = document.getElementById("summaryList");
            summaryList.innerHTML = "";

            for (const [position, candidate] of Object.entries(selectedVotes)) {
                const summaryItem = document.createElement("li");
                summaryItem.classList.add(
                    "flex",
                    "justify-between",
                    "items-center",
                    "p-4",
                    "bg-gray-50",
                    "rounded-lg",
                    "border",
                    "border-gray-200"
                );

                summaryItem.innerHTML = `
                    <div>
                        <h4 class="font-semibold text-gray-800">${position}</h4>
                        <p class="text-gray-600">${candidate === "Abstain" ? "Abstain" : candidate.candidate_name}</p>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">
                        ${candidate === "Abstain" ? "Abstained" : "Selected"}
                    </span>
                `;

                summaryList.appendChild(summaryItem);
            }

            console.log("Summary displayed:", summaryList.innerHTML); // Debugging log
        }

        // Modify submitVotes function to add final validation
        function submitVotes() {
            // Check if all positions have votes
            const hasAllVotes = positions.every(pos => {
                if (pos.position_name === 'Representative') {
                    return selectedVotes[pos.position_name]?.length >= 0;
                }
                return selectedVotes[pos.position_name] !== undefined && selectedVotes[pos.position_name] !== null;
            });

            if (!hasAllVotes) {
                alert("Please ensure you have selected a candidate or abstained for all positions before submitting.");
                return;
            }

            if (confirm("Are you sure you want to submit your final votes? This action cannot be undone.")) {
                fetch("submit_votes.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(selectedVotes)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Votes submitted successfully!");
                        window.location.href = "homepage.php";
                    } else {
                        alert("Failed to submit votes. Please try again. Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert("An error occurred: " + error.message);
                });
            } else {
                // User canceled the confirmation
                alert("Submission canceled. You can review or change your votes.");
            }
        }

        // Initialize the first position display
        displayPosition();
    </script>    
</body>
</html>
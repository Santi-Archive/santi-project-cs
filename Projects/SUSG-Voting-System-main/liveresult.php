<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

require_once 'connect.php';

// Get current election
$stmt = $pdo->query("
    SELECT * FROM elections 
    WHERE is_current = 1 
    LIMIT 1
");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentElection) {
    die("No active election found.");
}

// Add election status check
$now = new DateTime();
$startDate = new DateTime($currentElection['start_datetime']);
$endDate = new DateTime($currentElection['end_datetime']);

$electionStatus = '';
$statusClass = '';
if ($now < $startDate) {
    $electionStatus = 'Not Started';
    $statusClass = 'bg-yellow-500';
} elseif ($now > $endDate) {
    $electionStatus = 'Ended';
    $statusClass = 'bg-gray-500';
} else {
    $electionStatus = 'Ongoing';
    $statusClass = 'bg-green-500';
}

// Get student's college
$collegeStmt = $pdo->prepare("
    SELECT c.college_id, c.college_name 
    FROM colleges c 
    JOIN students s ON c.college_id = s.college_id 
    WHERE s.student_id = ?
");
$collegeStmt->execute([$_SESSION['user']['student_id']]);
$userCollege = $collegeStmt->fetch(PDO::FETCH_ASSOC);

// Fetch positions
$positionsStmt = $pdo->query("SELECT * FROM positions ORDER BY position_id");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Modified query to get candidates with vote counts for current election, excluding abstain
// and filtering representatives by college
$candidatesStmt = $pdo->prepare("
    SELECT 
        c.*, 
        p.position_name,
        pa.party_name as candidate_party,
        col.college_name,
        (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.candidate_id AND v.election_id = ?) as vote_count,
        (SELECT COUNT(*) FROM votes v2 
         JOIN candidates c2 ON v2.candidate_id = c2.candidate_id 
         WHERE c2.position_id = c.position_id 
         AND v2.election_id = ?
         AND (p.position_name != 'Representative' OR 
             (p.position_name = 'Representative' AND c2.college_id = ?))
        ) as total_position_votes
    FROM candidates c
    JOIN positions p ON c.position_id = p.position_id
    LEFT JOIN parties pa ON c.party_id = pa.party_id
    LEFT JOIN colleges col ON c.college_id = col.college_id
    WHERE c.election_id = ? 
    AND c.candidate_name != 'Abstain'
    AND (p.position_name != 'Representative' OR 
        (p.position_name = 'Representative' AND c.college_id = ?))
    ORDER BY p.position_id, vote_count DESC
");

$candidatesStmt->execute([
    $currentElection['election_id'],
    $currentElection['election_id'],
    $userCollege['college_id'],
    $currentElection['election_id'],
    $userCollege['college_id']
]);
$candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);

// Group candidates by position
$candidatesByPosition = [];
foreach ($candidates as $candidate) {
    if (!isset($candidatesByPosition[$candidate['position_id']])) {
        $candidatesByPosition[$candidate['position_id']] = [];
    }
    // Calculate percentage
    $totalVotes = $candidate['total_position_votes'] > 0 ? $candidate['total_position_votes'] : 1;
    $candidate['percentage'] = number_format(($candidate['vote_count'] / $totalVotes) * 100, 1);
    $candidatesByPosition[$candidate['position_id']][] = $candidate;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Live Results</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
        .vote-counter {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            padding: 0.5rem 1.2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 100px;
        }
        .position-card {
            background: linear-gradient(145deg, #fff 0%, #fee2e2 100%);
            border-radius: 1.5rem;
            box-shadow: 0 4px 20px -3px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            width: 100%;
            height: calc(100vh - 280px);
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            border: 1px solid rgba(239, 68, 68, 0.1);
            position: relative;
            overflow: hidden;
        }

        .position-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #ef4444, #991b1b);
            border-radius: 1.5rem 1.5rem 0 0;
        }

        .position-card h2 {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
            color: #1f2937;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .position-card h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.2), transparent);
        }

        .candidates-container {
            overflow-y: auto;
            flex-grow: 1;
            padding-right: 0.5rem;
            margin-right: -0.5rem;
            scrollbar-width: thin;
            scrollbar-color: #ef4444 #fee2e2;
        }
        /* Webkit scrollbar styling */
        .candidates-container::-webkit-scrollbar {
            width: 6px;
        }
        .candidates-container::-webkit-scrollbar-track {
            background: #fee2e2;
            border-radius: 3px;
        }
        .candidates-container::-webkit-scrollbar-thumb {
            background: #ef4444;
            border-radius: 3px;
        }
        .candidates-container::-webkit-scrollbar-thumb:hover {
            background: #dc2626;
        }
        .candidate-card {
            padding: 1.25rem;
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.95);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            border: 1px solid rgba(239, 68, 68, 0.1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .candidate-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(to bottom, #ef4444, transparent);
            border-radius: 0 1.25rem 1.25rem 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .candidate-card:hover {
            transform: translateY(-3px) translateX(-2px);
            box-shadow: 8px 8px 20px -6px rgba(239, 68, 68, 0.15);
        }

        .candidate-card:hover::after {
            opacity: 1;
        }

        .candidate-info {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 1.5rem;
            align-items: start;
            width: 100%;
        }

        .candidate-image-wrapper {
            position: relative;
            width: 80px;
            flex-shrink: 0;
        }

        .candidate-image {
            width: 90px;
            height: 90px;
            border-radius: 1rem;
            object-fit: cover;
            border: 3px solid #fecaca;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
            transition: all 0.3s ease;
        }

        .candidate-card:hover .candidate-image {
            border-color: #ef4444;
            transform: scale(1.05);
        }

        .crown-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            padding: 0.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 8px -2px rgba(251, 191, 36, 0.5);
            transform: rotate(15deg);
            transition: all 0.3s ease;
        }

        .candidate-card:hover .crown-badge {
            transform: rotate(30deg) scale(1.1);
        }

        .candidate-details {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
        }

        .candidate-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
            margin-bottom: -0.25rem;
        }

        .candidate-meta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .meta-tag {
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 6px -2px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .meta-tag:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px -2px rgba(0, 0, 0, 0.15);
        }

        .votes-section {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(239, 68, 68, 0.1);
        }

        .vote-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .vote-counter {
            background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);
            padding: 0.75rem 1.25rem;
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
            transition: all 0.3s ease;
        }

        .candidate-card:hover .vote-counter {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3);
        }

        .vote-counter span:first-child {
            font-size: 1.5rem;
        }

        .vote-counter span:last-child {
            font-size: 0.75rem;
        }

        .vote-percentage {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .vote-percentage svg {
            width: 1rem;
            height: 1rem;
        }

        .progress-bar {
            height: 8px;
            border-radius: 6px;
            background: #fee2e2;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ef4444, #991b1b);
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 6px;
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .election-header {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
            position: relative;
            padding-bottom: 3rem; /* Add space for the refresh timer */
        }

        .election-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.75rem;
        }

        .election-header h2 {
            font-size: 1.25rem;
        }

        .refresh-timer {
            position: absolute;
            top: 4rem; /* Position it below live indicator */
            right: 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 0.75rem;
            backdrop-filter: blur(4px);
            color: white;
        }

        .live-indicator {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 1rem;
            backdrop-filter: blur(4px);
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            animation: blink 1.5s ease-in-out infinite;
        }

        @keyframes blink {
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            color: white;
            margin-top: 0.5rem;
        }

        .status-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .datetime-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-red-50">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-4 mt-4">
        <div class="max-w-8xl mx-auto">
            <!-- Election Info -->
            <div class="election-header">
                <div class="flex justify-between items-start mb-4">
                    <h1 class="text-3xl font-bold text-white">Live Election Results</h1>
                </div>
                
                <span class="live-indicator">
                    <span class="live-dot"></span>
                    <span class="text-sm font-medium text-white">LIVE</span>
                </span>

                <div id="refreshTimer" class="refresh-timer">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Auto-refreshing in 30s</span>
                </div>
                
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold text-red-100">
                        <?php echo htmlspecialchars($currentElection['election_name']); ?>
                    </h2>
                    
                    <div class="status-section">
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <?php echo $electionStatus; ?>
                        </span>
                    </div>
                    
                    <div class="datetime-section">
                        <div class="flex items-center gap-2 text-red-100 font-medium text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <?php 
                                echo (new DateTime($currentElection['start_datetime']))->format('F j, Y - g:i A') . 
                                     ' to ' . 
                                     (new DateTime($currentElection['end_datetime']))->format('F j, Y - g:i A'); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                <?php foreach ($positions as $position): ?>
                    <div class="position-card p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center border-b border-red-100 pb-4">
                            <?= htmlspecialchars($position['position_name']) ?>
                        </h2>
                        
                        <div class="candidates-container">
                            <div class="space-y-4">
                                <?php if (isset($candidatesByPosition[$position['position_id']])): ?>
                                    <?php foreach ($candidatesByPosition[$position['position_id']] as $index => $candidate): ?>
                                        <div class="candidate-card">
                                            <div class="candidate-info">
                                                <div class="candidate-image-wrapper">
                                                    <img src="<?= htmlspecialchars($candidate['candidate_image']) ?>" 
                                                         alt="<?= htmlspecialchars($candidate['candidate_name']) ?>" 
                                                         class="candidate-image">
                                                    <?php if ($index === 0 && $candidate['vote_count'] > 0): ?>
                                                        <div class="crown-badge">
                                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 2l1.85 3.75 4.15.6-3 2.925.7 4.175L10 11.75 6.3 13.45l.7-4.175-3-2.925 4.15-.6L10 2z"/>
                                                            </svg>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="candidate-details">
                                                    <h3 class="candidate-name">
                                                        <?= htmlspecialchars($candidate['candidate_name']) ?>
                                                    </h3>
                                                    
                                                    <div class="candidate-meta">
                                                        <span class="meta-tag bg-red-50 text-red-700">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                            </svg>
                                                            <?= htmlspecialchars($candidate['college_name']) ?>
                                                        </span>
                                                        <?php if ($candidate['candidate_party']): ?>
                                                            <span class="meta-tag <?= 
                                                                $candidate['candidate_party'] === 'CAUSE' ? 'bg-green-50 text-green-700' :
                                                                ($candidate['candidate_party'] === 'SURE' ? 'bg-blue-50 text-blue-700' :
                                                                'bg-purple-50 text-purple-700')
                                                            ?>">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                                </svg>
                                                                <?= htmlspecialchars($candidate['candidate_party']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="votes-section">
                                                        <div class="vote-stats">
                                                            <div class="vote-counter">
                                                                <span class="text-2xl font-bold text-white"><?= $candidate['vote_count'] ?></span>
                                                                <span class="text-sm text-white opacity-90">votes</span>
                                                            </div>
                                                            <div class="vote-percentage">
                                                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                                                </svg>
                                                                <?= $candidate['percentage'] ?>%
                                                            </div>
                                                        </div>
                                                        <div class="progress-bar">
                                                            <div class="progress-fill" style="width: <?= $candidate['percentage'] ?>%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <p class="text-gray-500">No candidates found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function updateCountdown() {
            let countdown = 30;
            const timerSpan = document.querySelector('#refreshTimer span');

            const timer = setInterval(() => {
                countdown--;
                timerSpan.textContent = `Auto-refreshing in ${countdown}s`;
                if (countdown <= 0) clearInterval(timer);
            }, 1000);
        }

        // Initialize countdown and auto-refresh
        document.addEventListener('DOMContentLoaded', updateCountdown);
        setInterval(() => location.reload(), 30000);
    </script>
</body>
</html>
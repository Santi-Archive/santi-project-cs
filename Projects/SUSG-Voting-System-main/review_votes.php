<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

require_once 'connect.php';

// Check if user has voted by querying the database
$student_id = $_SESSION['user']['student_id'];
$check_votes_stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE student_id = ?");
$check_votes_stmt->execute([$student_id]);
$has_voted = (bool)$check_votes_stmt->fetchColumn();

if (!$has_voted) {
    $_SESSION['show_popup'] = 'not-voted';
    header('Location: homepage.php');
    exit();
}

$user = $_SESSION['user'];

require_once 'connect.php';

// Fetch positions from the database
$positions_stmt = $pdo->query("SELECT * FROM positions");
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch candidate images from the database
$candidate_images_stmt = $pdo->query("SELECT candidate_name, candidate_image FROM candidates");
$candidate_images = $candidate_images_stmt->fetchAll(PDO::FETCH_ASSOC);
$candidate_images_map = [];
foreach ($candidate_images as $candidate_image) {
    $candidate_images_map[$candidate_image['candidate_name']] = $candidate_image['candidate_image'];
}

// Default image for abstain
$default_abstain_image = 'path/to/default_abstain_image.png';

// Modified vote fetching logic
$selectedVotes = [];
$votes_stmt = $pdo->prepare("SELECT v.candidate_id, c.candidate_name, c.college_id, c.candidate_image,
                                    p.position_name, col.college_name, par.party_name
                             FROM votes v
                             JOIN candidates c ON v.candidate_id = c.candidate_id
                             JOIN positions p ON v.position_id = p.position_id
                             JOIN colleges col ON c.college_id = col.college_id
                             LEFT JOIN parties par ON c.party_id = par.party_id
                             WHERE v.student_id = ?");
$votes_stmt->execute([$user['student_id']]);
$votes = $votes_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($votes as $vote) {
    if ($vote['position_name'] === 'Representative') {
        if (!isset($selectedVotes[$vote['position_name']])) {
            $selectedVotes[$vote['position_name']] = [];
        }
        $selectedVotes[$vote['position_name']][] = [
            'candidate_id' => $vote['candidate_id'],
            'candidate_name' => $vote['candidate_name'],
            'college_name' => $vote['college_name'],
            'party_name' => $vote['party_name'],
            'candidate_image' => $vote['candidate_image']
        ];
    } else {
        $selectedVotes[$vote['position_name']] = [
            'candidate_id' => $vote['candidate_id'],
            'candidate_name' => $vote['candidate_name'],
            'college_name' => $vote['college_name'],
            'party_name' => $vote['party_name'],
            'candidate_image' => $vote['candidate_image']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Review Votes</title>
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
        /* Add abstain icon styles */
        .abstain-icon-placeholder {
            width: 64px;
            height: 64px;
            background: #FEF3C7;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .abstain-icon-placeholder i {
            font-size: 32px;
            color: #D97706;
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>

<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <main class="min-h-screen p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Review Header -->
            <div class="bg-white rounded-xl shadow-xl p-8 mb-8 border-l-4 border-red-600">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Review Your Votes</h1>
                <div class="text-gray-600">
                    Here's a summary of your submitted votes for the SUSG Election.
                </div>
            </div>

            <!-- Votes Summary Box -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                    <h2 class="text-xl font-semibold text-red-800">Your Submitted Votes</h2>
                </div>
                
                <div class="p-6 grid gap-6">
                    <?php foreach ($positions as $position): ?>
                        <?php if (isset($selectedVotes[$position['position_name']])): ?>
                            <?php if ($position['position_name'] === 'Representative'): ?>
                                <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-user-tie text-red-600 mr-2"></i>
                                        <?php echo htmlspecialchars($position['position_name']); ?>
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
                                                <?php if ($representative['candidate_id'] == 0 || $representative['candidate_name'] === 'Abstain'): ?>
                                                    <!-- Abstain Card -->
                                                    <div class="flex items-center bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="abstain-icon">
                                                            <i class="fas fa-ban"></i>
                                                        </div>
                                                        <div class="ml-4">
                                                            <h4 class="text-lg font-medium text-yellow-800">Abstain</h4>
                                                            <p class="text-sm text-yellow-600">You abstained for this position</p>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex items-center bg-white rounded-lg p-4 border border-gray-200">
                                                        <img class="w-16 h-16 rounded-lg object-cover shadow-sm" 
                                                             src="<?php echo htmlspecialchars($representative['candidate_image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($representative['candidate_name']); ?>">
                                                        <div class="ml-4">
                                                            <!-- Rest of the existing representative display code -->
                                                            <h4 class="text-lg font-medium text-gray-800">
                                                                <?php echo htmlspecialchars($representative['candidate_name']); ?>
                                                            </h4>
                                                            <div class="flex flex-row items-center space-x-3">
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
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <!-- Regular position display code -->
                                <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                        <i class="fas fa-user-tie text-red-600 mr-2"></i>
                                        <?php echo htmlspecialchars($position['position_name']); ?>
                                    </h3>
                                    
                                    <?php if ($selectedVotes[$position['position_name']]['candidate_id'] == 0): ?>
                                        <!-- Abstain Card -->
                                        <div class="flex items-center bg-white rounded-lg p-4 border border-gray-200">
                                            <div class="abstain-icon">
                                                <i class="fas fa-ban"></i>
                                            </div>
                                            <div class="ml-4">
                                                <h4 class="text-lg font-medium text-yellow-800">Abstain</h4>
                                                <p class="text-sm text-yellow-600">You abstained for this position</p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Candidate Card -->
                                        <div class="flex items-center bg-white rounded-lg p-4 border border-gray-200">
                                            <?php if ($selectedVotes[$position['position_name']]['candidate_name'] === 'Abstain'): ?>
                                                <div class="abstain-icon">
                                                    <i class="fas fa-ban"></i>
                                                </div>
                                            <?php else: ?>
                                                <img class="w-16 h-16 rounded-lg object-cover shadow-sm" 
                                                     src="<?php echo htmlspecialchars($candidate_images_map[$selectedVotes[$position['position_name']]['candidate_name']] ?? 'asset/default-candidate.png'); ?>" 
                                                     alt="<?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?>">
                                            <?php endif; ?>
                                            <div class="ml-4">
                                                <h4 class="text-lg font-medium <?php echo $selectedVotes[$position['position_name']]['candidate_name'] === 'Abstain' ? 'text-yellow-800' : 'text-gray-800'; ?>">
                                                    <?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?>
                                                </h4>
                                                <?php if ($selectedVotes[$position['position_name']]['candidate_name'] !== 'Abstain'): ?>
                                                    <div class="flex flex-row items-center space-x-3">
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
                                                <?php else: ?>
                                                    <p class="text-sm text-yellow-600">You abstained for this position</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Back Button -->
            <div class="flex justify-center mt-8">
                <?php 
                $fromParam = isset($_GET['from']) ? $_GET['from'] : '';
                $showHomeButton = ($fromParam === 'home');
                ?>
                
                <button onclick="navigateTo('<?php echo $showHomeButton ? 'homepage.php' : 'countdown.php'; ?>')" 
                        class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg flex items-center justify-center transition duration-300 transform hover:-translate-y-1">
                    <i class="fas <?php echo $showHomeButton ? 'fa-home' : 'fa-arrow-left'; ?> mr-2"></i>
                    <?php echo $showHomeButton ? 'Back to Homepage' : 'Back to Countdown'; ?>
                </button>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function navigateTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

// Get current election
try {
    require_once '../connect.php';
    $stmt = $pdo->prepare("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
    $stmt->execute();
    $currentElection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no current election exists, get the most recent one
    if (!$currentElection) {
        $stmt = $pdo->query("SELECT * FROM elections ORDER BY created_at DESC LIMIT 1");
        $currentElection = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $currentElection = null;
}

// Determine the current page for dynamic highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Side Bar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Side Bar Content -->
    <div class="fixed top-0 left-0 h-full w-64 bg-red-700 text-white p-6 flex flex-col justify-between shadow-lg">
        <div class="space-y-8">
            <div class="space-y-6">
                <h2 class="text-xl font-bold tracking-wider">SUSG COMELEC</h2>
                
                <!-- Current Election Status -->
                <div class="bg-red-800 rounded-lg p-4">
                    <p class="text-sm text-red-300 uppercase tracking-wider mb-2">Current Election</p>
                    <?php if ($currentElection): ?>
                        <p class="font-semibold text-white truncate mb-2"><?php echo htmlspecialchars($currentElection['election_name']); ?></p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?php echo strtolower($currentElection['status']) === 'ongoing' 
                                ? 'bg-green-100 text-green-800' 
                                : (strtolower($currentElection['status']) === 'scheduled' 
                                    ? 'bg-blue-100 text-blue-800' 
                                    : 'bg-gray-100 text-gray-800'); ?>">
                            <?php echo htmlspecialchars($currentElection['status']); ?>
                        </span>
                    <?php else: ?>
                        <p class="text-red-300 italic">No active election</p>
                    <?php endif; ?>
                </div>

                <nav class="space-y-2">
                    <a href="admin-home.php" 
                       class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'admin-home.php' ? 'bg-white text-red-700' : 'hover:bg-red-600'; ?>">
                        <i class="fas fa-home w-6"></i>
                        <span>Home</span>
                    </a>
                    <a href="admin-liveresults.php" 
                       class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'admin-liveresults.php' ? 'bg-white text-red-700' : 'hover:bg-red-600'; ?>">
                        <i class="fas fa-chart-bar w-6"></i>
                        <span>Live Results</span>
                    </a>
                </nav>
            </div>

            <div class="space-y-6">
                <h3 class="text-sm uppercase tracking-wider text-red-200">Management</h3>
                <nav class="space-y-2">
                    <a href="admin-voters.php" 
                       class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'admin-voters.php' ? 'bg-white text-red-700' : 'hover:bg-red-600'; ?>">
                        <i class="fas fa-user-friends w-6"></i>
                        <span>Voters</span>
                    </a>
                    <a href="admin-candidates.php" 
                       class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'admin-candidates.php' ? 'bg-white text-red-700' : 'hover:bg-red-600'; ?>">
                        <i class="fas fa-user-tie w-6"></i>
                        <span>Candidates</span>
                    </a>
                </nav>
            </div>

            <div class="space-y-6">
                <h3 class="text-sm uppercase tracking-wider text-red-200">Feedback</h3>
                <nav class="space-y-2">
                    <a href="admin-feedback.php" 
                       class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'admin-feedback.php' ? 'bg-white text-red-700' : 'hover:bg-red-600'; ?>">
                        <i class="fas fa-comments w-6"></i>
                        <span>View Feedback</span>
                    </a>
                </nav>
            </div>

            <div class="space-y-6">
                <h3 class="text-sm uppercase tracking-wider text-red-200">Sentiment Analysis</h3>
                <nav class="space-y-2">
                    <a href="admin-analytics.php" 
                       class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'admin-analytics.php' ? 'bg-white text-red-700' : 'hover:bg-red-600'; ?>">
                        <i class="fas fa-chart-pie w-6"></i>
                        <span>Analytics</span>
                    </a>
                </nav>
            </div>
        </div>

        <div class="pt-6 border-t border-red-600">
            <a href="../logout.php?type=comelec" 
               class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 hover:bg-red-600">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</body>
</html>
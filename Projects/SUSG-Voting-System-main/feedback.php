<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

// Retrieve user details from the session
$user = $_SESSION['user'];

// Include database connection
require_once 'connect.php';

// Get current election
$stmt = $pdo->query("SELECT election_id FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentElection) {
    die("No active election found");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : 0;
    $suggestion = isset($_POST['suggestion']) ? trim($_POST['suggestion']) : '';

    // Debugging: Check received data
    error_log("Received experience: $experience, suggestion: $suggestion");

    // Insert feedback into the database with election_id
    $stmt = $pdo->prepare("
        INSERT INTO feedbacks (
            student_id, 
            experience, 
            suggestion, 
            feedback_timestamp, 
            election_id
        ) VALUES (
            :student_id, 
            :experience, 
            :suggestion, 
            NOW(),
            :election_id
        )
    ");

    $stmt->execute([
        'student_id' => $user['student_id'],
        'experience' => $experience,
        'suggestion' => $suggestion,
        'election_id' => $currentElection['election_id']
    ]);

    // Debugging: Check if insertion was successful
    if ($stmt->rowCount() > 0) {
        error_log("Feedback inserted successfully.");
    } else {
        error_log("Failed to insert feedback.");
    }

    // Return a success response
    echo json_encode(['success' => true]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Leave a Feedback</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>

<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <main class="min-h-screen flex items-center justify-center p-8">
        <div class="w-full max-w-xl"> <!-- Changed from max-w-2xl to max-w-xl -->
            <!-- Feedback Container -->
            <div class="bg-white rounded-xl shadow-xl overflow-hidden transform hover:shadow-2xl transition-all duration-300">
                <!-- Feedback Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-800 px-6 py-5"> <!-- Reduced padding -->
                    <h1 class="text-2xl font-bold text-white text-center">Share Your Thoughts!</h1> <!-- Reduced text size -->
                    <p class="text-red-100 text-center mt-1 text-sm">Help us improve your voting experience</p> <!-- Added text-sm -->
                </div>

                <form id="feedback-form" class="p-6 space-y-6"> <!-- Reduced padding and spacing -->
                    <!-- Rating Section -->
                    <div class="text-center space-y-4"> <!-- Reduced spacing -->
                        <label class="block text-xl font-semibold text-gray-800"> <!-- Reduced text size -->
                            How would you rate your experience?
                        </label>
                        <div class="flex justify-center space-x-4"> <!-- Reduced spacing -->
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <button type="button" 
                                        class="rating-option w-14 h-14 rounded-full border-2 border-gray-300 flex items-center justify-center text-xl font-bold transition-all duration-200 hover:scale-110 transform hover:border-red-500"
                                        data-value="<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="experience" id="experience" value="0">
                        <div class="flex justify-between text-sm text-gray-600 px-8">
                            <span class="flex items-center">
                                <i class="fas fa-frown text-red-500 text-lg mr-2"></i>
                                <span class="font-medium">Poor</span>
                            </span>
                            <span class="flex items-center">
                                <span class="font-medium">Excellent</span>
                                <i class="fas fa-smile text-green-500 text-lg ml-2"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Suggestion Section -->
                    <div class="space-y-3"> <!-- Reduced spacing -->
                        <label class="block text-xl font-semibold text-gray-800 text-center">
                            Any suggestions for improvement?
                        </label>
                        <textarea name="suggestion" 
                                class="w-full h-32 p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none text-gray-700"
                                placeholder="Share your ideas with us..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4"> <!-- Reduced padding -->
                        <button type="submit"
                                class="w-full bg-red-600 hover:bg-red-700 text-white text-base font-semibold rounded-lg py-3 transition duration-300 flex items-center justify-center group">
                            <i class="fas fa-paper-plane mr-2 group-hover:translate-x-1 transition-transform"></i>
                            Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 transform transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check-circle text-5xl text-green-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Thank You!</h2>
                <p class="text-gray-600 mb-6">Your feedback helps us improve the voting system.</p>
                <button onclick="closeModal()" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ratingOptions = document.querySelectorAll('.rating-option');
            const experienceInput = document.getElementById('experience');
            const feedbackForm = document.getElementById('feedback-form');

            ratingOptions.forEach(option => {
                option.addEventListener('click', function () {
                    // Remove selected state from all options
                    ratingOptions.forEach(opt => {
                        opt.classList.remove('bg-red-600', 'text-white', 'border-red-600', 'scale-110');
                    });
                    // Add selected state to clicked option
                    option.classList.add('bg-red-600', 'text-white', 'border-red-600', 'scale-110');
                    experienceInput.value = option.getAttribute('data-value');
                });
            });

            feedbackForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (experienceInput.value === "0") {
                    alert("Please provide a rating before submitting.");
                    return;
                }

                const formData = new FormData(feedbackForm);

                fetch('feedback.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('successModal').classList.remove('hidden');
                    }
                });
            });
        });

        function closeModal() {
            document.getElementById('successModal').classList.add('hidden');
            document.getElementById('feedback-form').reset();
            // Remove selected state from all rating options
            document.querySelectorAll('.rating-option').forEach(opt => {
                opt.classList.remove('bg-red-600', 'text-white', 'border-red-600', 'scale-110');
            });
            document.getElementById('experience').value = "0";
        }

        function navigateTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
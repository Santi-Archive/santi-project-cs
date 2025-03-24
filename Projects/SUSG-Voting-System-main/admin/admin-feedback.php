<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Get current election
$stmt = $pdo->query("SELECT election_id, election_name FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentElection) {
    die("Please set a current election first before viewing feedback.");
}

// Fetch feedbacks from the database for current election only
$stmt = $pdo->prepare("
    SELECT f.*, s.student_name 
    FROM feedbacks f 
    JOIN students s ON f.student_id = s.student_id 
    WHERE f.election_id = :election_id 
    ORDER BY f.feedback_timestamp DESC
");
$stmt->execute(['election_id' => $currentElection['election_id']]);
$feedbacks = $stmt->fetchAll();

require_once dirname(__FILE__) . '/../cache/SentimentCache.php';
$sentimentCache = new SentimentCache();
$overallSentiment = $sentimentCache->get('overall_sentiment', $currentElection['election_id']);
$sentimentScore = $overallSentiment ? $overallSentiment['score'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Voter's Feedback</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.tailwind.min.js"></script>
    <style>
        /* Custom DataTables Styling */
        .dataTables_wrapper {
            padding: 1rem;
        }
        
        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc {
            position: relative;
            background-image: none !important;
        }

        table.dataTable thead .sorting:after,
        table.dataTable thead .sorting_asc:after,
        table.dataTable thead .sorting_desc:after {
            position: absolute;
            right: 8px;
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            font-size: 0.8em;
        }

        table.dataTable thead .sorting:after {
            content: "\f0dc";
            color: #ddd;
        }

        table.dataTable thead .sorting_asc:after {
            content: "\f0de";
            color: #666;
        }

        table.dataTable thead .sorting_desc:after {
            content: "\f0dd";
            color: #666;
        }

        .dataTables_info {
            margin-top: 1rem;
            padding-top: 0.5rem !important;
            color: #6b7280;
        }

        .dataTables_paginate {
            margin-top: 1rem !important;
            padding-top: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main class="ml-64 p-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section with Current Election -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Voter's Feedback</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Current Election: <span class="font-semibold text-red-600"><?php echo htmlspecialchars($currentElection['election_name']); ?></span>
                    </p>
                </div>
                <div class="text-sm text-gray-600">
                    Total Feedbacks: <?php echo count($feedbacks); ?>
                </div>
            </div>

            <!-- Feedback Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <?php
                $ratings = array_column($feedbacks, 'experience');
                $avgRating = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
                
                // Read sentiment cache
                $sentimentScore = $overallSentiment ? $overallSentiment['score'] : 0;

                ?>
                <!-- Average Rating Card -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 mb-1">Average Rating</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($avgRating, 1); ?>/5.0</div>
                    <div class="text-sm text-gray-600 mt-2">Based on all feedback</div>
                </div>
                <!-- Latest Activity Card -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 mb-1">Latest Activity</div>
                    <div class="text-sm text-gray-600 mt-2">
                        <?php
                        if (count($feedbacks) > 0) {
                            $latestFeedback = $feedbacks[0];
                            $latestFeedbackDate = date('M d, Y \a\t h:i A', strtotime($latestFeedback['feedback_timestamp']));
                            $latestFeedbackAuthor = htmlspecialchars($latestFeedback['student_name']);
                            $latestFeedbackComment = htmlspecialchars($latestFeedback['suggestion']);
                            $latestFeedbackRating = $latestFeedback['experience'];

                            $ratingClass = '';
                            if ($latestFeedbackRating >= 4) {
                                $ratingClass = 'bg-green-100 text-green-800';
                            } elseif ($latestFeedbackRating >= 2) {
                                $ratingClass = 'bg-yellow-100 text-yellow-800';
                            } else {
                                $ratingClass = 'bg-red-100 text-red-800';
                            }

                            echo "<div class='text-sm font-medium text-gray-900'>Author: $latestFeedbackAuthor</div>";
                            echo "<div class='text-sm text-gray-600'>Comment: $latestFeedbackComment</div>";
                            echo "<div class='text-sm text-gray-600'>Rating: <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $ratingClass'>$latestFeedbackRating/5</span></div>";
                            echo "<div class='text-sm text-gray-500'>Submitted on: $latestFeedbackDate</div>";
                        } else {
                            echo "<div class='text-sm text-gray-600'>No feedback available.</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden p-4">
                <div class="overflow-x-auto">
                    <table id="feedbackTable" class="w-full whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted on</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($feedbacks as $feedback): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($feedback['student_name']); ?></div>
                                </td>
                                <td class="px-6 py-4" data-order="<?php echo $feedback['experience']; ?>">
                                    <?php 
                                    $ratingClass = '';
                                    if ($feedback['experience'] >= 4) {
                                        $ratingClass = 'bg-green-100 text-green-800';
                                    } elseif ($feedback['experience'] >= 2) {
                                        $ratingClass = 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $ratingClass = 'bg-red-100 text-red-800';
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $ratingClass; ?>">
                                        <?php echo $feedback['experience']; ?>/5
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php 
                                        $shortText = htmlspecialchars($feedback['suggestion']);
                                        if (strlen($shortText) > 100) {
                                            $shortText = substr($shortText, 0, 100) . '...';
                                            echo $shortText;
                                            echo '<button class="expand-comment ml-2 text-blue-600 hover:text-blue-800 text-sm">Read more</button>';
                                            echo '<span class="hidden full-comment">' . htmlspecialchars($feedback['suggestion']) . '</span>';
                                        } else {
                                            echo $shortText;
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4" data-order="<?php echo strtotime($feedback['feedback_timestamp']); ?>">
                                    <div class="text-sm text-gray-500"><?php echo date('M d, Y \a\t h:i A', strtotime($feedback['feedback_timestamp'])); ?></div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="remove-btn inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" 
                                            data-id="<?php echo $feedback['feedback_id']; ?>">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <!-- Add this modal at the bottom of the body, before closing body tag -->
    <div id="commentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Full Comment</h3>
                <button class="close-modal text-gray-400 hover:text-gray-500">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>
            <div class="comment-content space-y-4">
                <div class="text-sm text-gray-600">
                    <div class="font-medium text-gray-900 mb-1">Author</div>
                    <div id="modalAuthor" class="mb-4"></div>
                    
                    <div class="font-medium text-gray-900 mb-1">Rating</div>
                    <div id="modalRating" class="mb-4"></div>
                    
                    <div class="font-medium text-gray-900 mb-1">Date</div>
                    <div id="modalDate" class="mb-4"></div>
                    
                    <div class="font-medium text-gray-900 mb-1">Comment</div>
                    <div id="modalComment" class="leading-normal"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#feedbackTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[3, 'desc']], // Sort by date by default
                columnDefs: [
                    { orderable: false, targets: 4 }, // Disable sorting for action column
                    { 
                        targets: 2,
                        render: function(data, type, row) {
                            if (type === 'display') {
                                let shortText = data;
                                if (data.length > 100) {
                                    shortText = data.substr(0, 100) + '...';
                                    return `<div class="text-sm text-gray-900">
                                        ${shortText}
                                        <button class="expand-comment ml-2 text-blue-600 hover:text-blue-800 text-sm">
                                            Read more
                                        </button>
                                        <span class="hidden full-comment">${data}</span>
                                    </div>`;
                                }
                                return `<div class="text-sm text-gray-900">${shortText}</div>`;
                            }
                            return data;
                        }
                    }
                ],
                dom: '<"flex flex-col sm:flex-row justify-between items-center"lf><"overflow-x-auto"rt><"flex flex-col sm:flex-row justify-between items-center"ip>',
                language: {
                    search: "Search feedback:",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ feedback entries",
                },
                drawCallback: function() {
                    // Re-apply Tailwind classes to DataTables elements
                    $('.dataTables_length select').addClass('rounded-lg border-gray-300 mx-2');
                    $('.dataTables_filter input').addClass('rounded-lg border-gray-300 ml-2');
                    $('.dataTables_paginate .paginate_button').addClass('px-3 py-1 mx-1 rounded-lg hover:bg-gray-100');
                    $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-500 text-white hover:bg-blue-600');
                    $('.dataTables_info').addClass('text-sm text-gray-600');
                }
            });

            // Handle comment expansion
            $('#feedbackTable').on('click', '.expand-comment', function(e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                const authorName = row.find('td:first-child .text-sm').text();
                const rating = row.find('td:nth-child(2) span').clone();
                const fullComment = $(this).siblings('.full-comment').text();
                const date = row.find('td:nth-child(4) .text-sm').text();

                // Populate modal
                $('#modalAuthor').text(authorName);
                $('#modalRating').html(rating);
                $('#modalComment').text(fullComment);
                $('#modalDate').text(date);

                // Show modal
                $('#commentModal').removeClass('hidden');
            });

            // Close modal handlers
            $('.close-modal, #commentModal').on('click', function(e) {
                if (e.target === this) {
                    $('#commentModal').addClass('hidden');
                }
            });

            // Prevent modal close when clicking inside modal content
            $('.modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        });

        // JavaScript for removing rows
        document.addEventListener('DOMContentLoaded', function () {
            const removeButtons = document.querySelectorAll('.remove-btn');
            removeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const feedbackId = this.getAttribute('data-id');
                    if (confirm('Do you really want to delete this feedback?')) {
                        fetch(`remove-feedback.php?id=${feedbackId}`, {
                            method: 'GET'
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  const row = this.parentNode.parentNode;
                                  row.parentNode.removeChild(row);
                              } else {
                                  alert('Failed to remove feedback.');
                              }
                          });
                    }
                });
            });
        });

        // Sidebar active link handling
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarLinks = document.querySelectorAll('.sidebar a');

            // Remove 'active' class from all links
            sidebarLinks.forEach(link => link.classList.remove('active'));

            // Set 'active' class based on current URL
            sidebarLinks.forEach(link => {
                if (link.href === window.location.href) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
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
    die("Please set a current election first before managing candidates.");
}

// Fetch candidates with party names
$stmt = $pdo->prepare("
    SELECT c.*, co.college_name, p.position_name, pa.party_name 
    FROM candidates c 
    LEFT JOIN colleges co ON c.college_id = co.college_id 
    LEFT JOIN positions p ON c.position_id = p.position_id
    LEFT JOIN parties pa ON c.party_id = pa.party_id
    WHERE c.election_id = :election_id
");
$stmt->execute(['election_id' => $currentElection['election_id']]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch positions
$positionsStmt = $pdo->query("SELECT * FROM positions");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch colleges, excluding "Abstain"
$collegesStmt = $pdo->query("SELECT * FROM colleges WHERE college_name != 'Abstain'");
$colleges = $collegesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch parties
$partiesStmt = $pdo->query("SELECT * FROM parties ORDER BY party_name");
$parties = $partiesStmt->fetchAll(PDO::FETCH_ASSOC);

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Candidates Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="../script/adminload.js" type="module" defer></script>
</head>
<body class="bg-gray-50">
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section - Adjust width and padding -->
    <main class="ml-64 p-8"> <!-- Changed p-4 to p-8 for more space from edges -->
        <div class="max-w-7xl mx-auto"> <!-- Changed from max-w-[90%] to max-w-7xl -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Candidates Management</h1>
                <div class="text-sm text-gray-600">
                    Current Election: <span class="font-semibold text-red-600"><?php echo htmlspecialchars($currentElection['election_name']); ?></span>
                </div>
            </div>

            <div class="mb-8 text-right">
                <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:-translate-y-1" id="openModalBtn">
                    <i class="fas fa-plus-circle mr-2"></i> File New Candidate
                </button>
            </div>

            <!-- Add more padding to container -->
            <div class="bg-white rounded-xl shadow-xl p-8"> <!-- Changed p-6 to p-8 -->
                <div class="overflow-x-auto"> <!-- Add this wrapper div -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-red-50">
                            <tr>
                                <th class="px-4 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/6 cursor-pointer hover:bg-red-100 transition-colors" data-sort="candidate_name">
                                    <div class="flex items-center">
                                        Candidate Name
                                        <span class="sort-icon ml-1">↕</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/8 cursor-pointer hover:bg-red-100 transition-colors" data-sort="party_name">
                                    <div class="flex items-center">
                                        Party
                                        <span class="sort-icon ml-1">↕</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/8 cursor-pointer hover:bg-red-100 transition-colors" data-sort="position_name">
                                    <div class="flex items-center">
                                        Position
                                        <span class="sort-icon ml-1">↕</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/8 cursor-pointer hover:bg-red-100 transition-colors" data-sort="college_name">
                                    <div class="flex items-center">
                                        College
                                        <span class="sort-icon ml-1">↕</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/8 cursor-pointer hover:bg-red-100 transition-colors" data-sort="qualified">
                                    <div class="flex items-center">
                                        Qualified
                                        <span class="sort-icon ml-1">↕</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/6 cursor-pointer hover:bg-red-100 transition-colors" data-sort="remarks">
                                    <div class="flex items-center">
                                        Remarks
                                        <span class="sort-icon ml-1">↕</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/8"></th>
                                <th class="px-4 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/8"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($candidates as $candidate): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                    <div class="truncate max-w-[150px]"><?php echo htmlspecialchars($candidate['candidate_name']); ?></div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900">
                                    <div class="truncate max-w-[100px]"><?php echo htmlspecialchars($candidate['party_name']); ?></div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900">
                                    <div class="truncate max-w-[120px]"><?php echo htmlspecialchars($candidate['position_name']); ?></div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900">
                                    <div class="truncate max-w-[120px]" title="<?php echo htmlspecialchars($candidate['college_name']); ?>">
                                        <?php echo htmlspecialchars($candidate['college_name']); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $candidate['qualified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $candidate['qualified'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900">
                                    <div class="truncate max-w-[150px]" title="<?php echo htmlspecialchars($candidate['remarks']); ?>">
                                        <?php echo htmlspecialchars($candidate['remarks']); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <button class="edit-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm transition duration-300 transform hover:-translate-y-1" 
                                            data-candidate='<?php echo json_encode($candidate); ?>'>
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <button class="delete-btn bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded-lg text-sm transition duration-300 transform hover:-translate-y-1" 
                                            data-candidate-id="<?php echo $candidate['candidate_id']; ?>">
                                        <i class="fas fa-trash-alt mr-1"></i> Delete
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

    <!-- Update both modals with enhanced styling -->
    <div id="myModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-8 border w-full max-w-4xl shadow-2xl rounded-2xl bg-white transform transition-all">
            <!-- Header Section -->
            <div class="absolute top-0 left-0 right-0 h-16 bg-gradient-to-r from-red-600 to-red-800 rounded-t-2xl">
                <div class="flex justify-between items-center h-full px-8">
                    <h3 class="text-2xl font-bold text-white">File New Candidacy</h3>
                    <span class="close cursor-pointer text-white text-3xl hover:text-gray-200 transition-colors">&times;</span>
                </div>
            </div>

            <!-- Form Section with added top padding for header -->
            <form class="space-y-6 pt-20" id="newCandidateForm" method="POST" action="create_candidate.php" enctype="multipart/form-data">
                <input type="hidden" name="election_id" value="<?php echo $currentElection['election_id']; ?>">
                
                <div class="grid grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6 bg-gray-50 p-6 rounded-xl">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Candidate Name</label>
                            <input type="text" id="candidateName" name="candidateName" required
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Party:</label>
                            <select id="partyId" name="partyId" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <option value="">Select Party</option>
                                <?php foreach ($parties as $party): ?>
                                    <option value="<?php echo htmlspecialchars($party['party_id']); ?>">
                                        <?php echo htmlspecialchars($party['party_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Position:</label>
                                <select id="position" name="position" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select Position</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo htmlspecialchars($position['position_id']); ?>">
                                            <?php echo htmlspecialchars($position['position_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">College/Department:</label>
                                <select id="college" name="college" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select College</option>
                                    <?php foreach ($colleges as $college): ?>
                                        <option value="<?php echo htmlspecialchars($college['college_id']); ?>">
                                            <?php echo htmlspecialchars($college['college_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6 bg-gray-50 p-6 rounded-xl">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Candidate Image</label>
                            <div class="flex flex-col items-center space-y-4">
                                <!-- Image Preview -->
                                <div class="w-32 h-32 relative rounded-lg overflow-hidden bg-gray-100">
                                    <img id="imagePreview" class="w-full h-full object-cover hidden">
                                    <div id="placeholderIcon" class="absolute inset-0 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- Upload Button -->
                                <label class="w-full flex items-center justify-center px-4 py-2 bg-white rounded-lg border-2 border-dashed border-gray-300 cursor-pointer hover:border-red-500 transition-all">
                                    <span class="text-sm text-gray-500">Choose image</span>
                                    <input type="file" id="candidateImage" name="candidateImage" accept="image/*" required class="hidden">
                                </label>
                                <p class="text-xs text-gray-500">JPG, JPEG, PNG only</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Qualified:</label>
                                <select id="qualified" name="qualified" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select Qualification</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks:</label>
                                <input type="text" id="remarks" name="remarks"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Section -->
                <div class="pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-red-600 to-red-800 text-white font-bold py-3 px-8 rounded-xl hover:from-red-700 hover:to-red-900 transform hover:-translate-y-0.5 transition-all duration-200">
                        Submit Candidacy
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Apply similar styling to Edit Modal -->
    <div id="editModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-8 border w-full max-w-4xl shadow-2xl rounded-2xl bg-white transform transition-all">
            <!-- Header Section -->
            <div class="absolute top-0 left-0 right-0 h-16 bg-gradient-to-r from-red-600 to-red-800 rounded-t-2xl">
                <div class="flex justify-between items-center h-full px-8">
                    <h3 class="text-2xl font-bold text-white">Edit Candidacy</h3>
                    <span class="close cursor-pointer text-white text-3xl hover:text-gray-200 transition-colors">&times;</span>
                </div>
            </div>

            <!-- Form Section with added top padding for header -->
            <form class="space-y-6 pt-20" id="editCandidateForm" method="POST" action="edit_candidate.php" enctype="multipart/form-data">
                <input type="hidden" id="editCandidateId" name="candidateId">
                <input type="hidden" name="election_id" value="<?php echo $currentElection['election_id']; ?>">
                
                <div class="grid grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6 bg-gray-50 p-6 rounded-xl">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Candidate Name</label>
                            <input type="text" id="editCandidateName" name="candidateName" required
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Party:</label>
                            <select id="editPartyId" name="partyId" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <option value="">Select Party</option>
                                <?php foreach ($parties as $party): ?>
                                    <option value="<?php echo htmlspecialchars($party['party_id']); ?>">
                                        <?php echo htmlspecialchars($party['party_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Position:</label>
                                <select id="editPosition" name="position" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select Position</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo htmlspecialchars($position['position_id']); ?>">
                                            <?php echo htmlspecialchars($position['position_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">College/Department:</label>
                                <select id="editCollege" name="college" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select College</option>
                                    <?php foreach ($colleges as $college): ?>
                                        <option value="<?php echo htmlspecialchars($college['college_id']); ?>">
                                            <?php echo htmlspecialchars($college['college_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6 bg-gray-50 p-6 rounded-xl">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Candidate Image</label>
                            <div class="flex flex-col items-center space-y-4">
                                <!-- Image Preview -->
                                <div class="w-32 h-32 relative rounded-lg overflow-hidden bg-gray-100">
                                    <img id="editImagePreview" class="w-full h-full object-cover hidden">
                                    <div id="editPlaceholderIcon" class="absolute inset-0 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- Upload Button -->
                                <label class="w-full flex items-center justify-center px-4 py-2 bg-white rounded-lg border-2 border-dashed border-gray-300 cursor-pointer hover:border-red-500 transition-all">
                                    <span class="text-sm text-gray-500">Choose image</span>
                                    <input type="file" id="editCandidateImage" name="candidateImage" accept="image/*" class="hidden">
                                </label>
                                <p class="text-xs text-gray-500">JPG, JPEG, PNG only</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Qualified:</label>
                                <select id="editQualified" name="qualified" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">Select Qualification</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks:</label>
                                <input type="text" id="editRemarks" name="remarks"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Section -->
                <div class="pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-red-600 to-red-800 text-white font-bold py-3 px-8 rounded-xl hover:from-red-700 hover:to-red-900 transform hover:-translate-y-0.5 transition-all duration-200">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add these styles to your existing styles section -->
    <style>
        .modal-content {
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-10%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal {
            backdrop-filter: blur(5px);
        }

        .modal input:focus, .modal select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }

        .image-preview-container {
            transition: all 0.3s ease;
        }

        .image-preview-container:hover {
            transform: scale(1.05);
        }

        #imagePreview, #editImagePreview {
            transition: all 0.3s ease;
        }

        .sort-icon {
            opacity: 0.5;
            font-size: 0.8em;
        }

        th[data-sort] {
            position: relative;
        }

        th[data-sort].asc .sort-icon::after {
            content: '↑';
        }

        th[data-sort].desc .sort-icon::after {
            content: '↓';
        }

        th[data-sort].active {
            background-color: rgba(239, 68, 68, 0.1);
        }

        th[data-sort].active .sort-icon {
            opacity: 1;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        tbody tr {
            animation: none;
        }
    </style>

    <script>
        // Modal functionality
        const modal = document.getElementById("myModal");
        const openModalBtn = document.getElementById("openModalBtn");
        const closeBtns = document.querySelectorAll(".close");
        const editModal = document.getElementById("editModal");

        openModalBtn.addEventListener("click", () => modal.style.display = "block");
        closeBtns.forEach(btn => btn.addEventListener("click", () => {
            modal.style.display = "none";
            editModal.style.display = "none";
        }));
        window.addEventListener("click", (event) => {
            if (event.target == modal) modal.style.display = "none";
            if (event.target == editModal) editModal.style.display = "none";
        });

        // Client-side validation for image file type
        document.getElementById('newCandidateForm').addEventListener('submit', function(event) {
            const fileInput = document.getElementById('candidateImage');
            const filePath = fileInput.value;
            const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

            if (!allowedExtensions.exec(filePath)) {
                alert('Only JPG, JPEG, and PNG files are allowed.');
                fileInput.value = '';
                event.preventDefault();
            }
        });

        document.getElementById('editCandidateForm').addEventListener('submit', function(event) {
            const fileInput = document.getElementById('editCandidateImage');
            const filePath = fileInput.value;
            const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

            if (filePath && !allowedExtensions.exec(filePath)) {
                alert('Only JPG, JPEG, and PNG files are allowed.');
                fileInput.value = '';
                event.preventDefault();
            }
        });

        // Edit button functionality
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const candidate = JSON.parse(this.getAttribute('data-candidate'));
                document.getElementById('editCandidateId').value = candidate.candidate_id;
                document.getElementById('editCandidateName').value = candidate.candidate_name;
                document.getElementById('editPartyId').value = candidate.party_id;
                document.getElementById('editPosition').value = candidate.position_id;
                document.getElementById('editCollege').value = candidate.college_id;
                document.getElementById('editQualified').value = candidate.qualified;
                document.getElementById('editRemarks').value = candidate.remarks;
                editModal.style.display = "block";
            });
        });

        // Delete button functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const candidateId = this.getAttribute('data-candidate-id');
                if (confirm('Are you sure you want to delete this candidate?')) {
                    fetch('delete_candidate.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ candidateId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to delete candidate. Please try again.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });

        // Image preview functionality
        function setupImagePreview(inputId, previewId, placeholderId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById(placeholderId);

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                }
            });
        }

        // Initialize image preview for both modals
        document.addEventListener('DOMContentLoaded', function() {
            setupImagePreview('candidateImage', 'imagePreview', 'placeholderIcon');
            setupImagePreview('editCandidateImage', 'editImagePreview', 'editPlaceholderIcon');
        });

        // Table sorting functionality
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('table');
            const headers = table.querySelectorAll('th[data-sort]');
            let currentSort = {
                column: null,
                direction: 'asc'
            };

            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const column = header.dataset.sort;
                    
                    // Reset all headers
                    headers.forEach(h => {
                        h.classList.remove('asc', 'desc', 'active');
                    });

                    // Determine sort direction
                    if (currentSort.column === column) {
                        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.column = column;
                        currentSort.direction = 'asc';
                    }

                    // Add appropriate classes
                    header.classList.add(currentSort.direction, 'active');

                    // Get table body and rows
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));

                    // Sort rows
                    const sortedRows = rows.sort((a, b) => {
                        let aValue = a.querySelector(`td:nth-child(${Array.from(headers).indexOf(header) + 1})`).textContent.trim();
                        let bValue = b.querySelector(`td:nth-child(${Array.from(headers).indexOf(header) + 1})`).textContent.trim();

                        // Handle numeric values for 'qualified' column
                        if (column === 'qualified') {
                            aValue = aValue === 'Yes' ? 1 : 0;
                            bValue = bValue === 'Yes' ? 1 : 0;
                        }

                        if (currentSort.direction === 'asc') {
                            return aValue > bValue ? 1 : -1;
                        } else {
                            return aValue < bValue ? 1 : -1;
                        }
                    });

                    // Clear and append sorted rows
                    while (tbody.firstChild) {
                        tbody.removeChild(tbody.firstChild);
                    }
                    sortedRows.forEach(row => tbody.appendChild(row));

                    // Add animation to sorted rows
                    sortedRows.forEach((row, index) => {
                        row.style.animation = `fadeIn 0.3s ease-out ${index * 0.05}s`;
                    });
                });
            });
        });
    </script>
</body>
</html>
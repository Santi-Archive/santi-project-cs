<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['upload_errors'])) {
    $upload_errors = $_SESSION['upload_errors'];
    unset($_SESSION['upload_errors']);
}

require_once '../connect.php';

// Get current election
$stmt = $pdo->query("SELECT election_id, election_name FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentElection) {
    die("Please set a current election first before managing voters.");
}

// Modify the query to include password
$query = "
    SELECT s.*, c.college_name 
    FROM students s 
    JOIN colleges c ON s.college_id = c.college_id 
    WHERE s.election_id = :election_id
    ORDER BY s.student_id
";
$stmt = $pdo->prepare($query);
$stmt->execute(['election_id' => $currentElection['election_id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get colleges for the dropdown
$collegesQuery = "SELECT * FROM colleges WHERE college_id != 0 ORDER BY college_name";
$collegesStmt = $pdo->query($collegesQuery);
$colleges = $collegesStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics for current election
$totalStudents = count($students);
$votedStudents = array_reduce($students, function($carry, $student) {
    return $carry + ($student['has_voted'] ? 1 : 0);
}, 0);
$notVotedStudents = $totalStudents - $votedStudents;
$votingPercentage = $totalStudents > 0 ? round(($votedStudents / $totalStudents) * 100, 2) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Voters Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="../script/adminload.js" type="module" defer></script>
    <style>
        /* Add this to your existing styles */
        .blur-text {
            filter: blur(4px);
            transition: filter 0.2s ease;
        }
        .blur-text:hover {
            filter: blur(0);
        }
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

        /* Update blur-text style */
        .blur-text {
            filter: blur(4px);
            transition: filter 0.3s ease;
        }
        .blur-text:hover {
            filter: blur(0);
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
</head>
<body class="bg-gray-50">
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main class="ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Manage Voters</h1>
                <div class="text-sm text-gray-600">
                    Current Election: <span class="font-semibold text-red-600"><?php echo htmlspecialchars($currentElection['election_name']); ?></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Total Voters</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $totalStudents; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Voted</h3>
                    <p class="text-2xl font-bold text-green-600"><?php echo $votedStudents; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Not Voted</h3>
                    <p class="text-2xl font-bold text-red-600"><?php echo $notVotedStudents; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Voting Percentage</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $votingPercentage; ?>%</p>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8" role="alert">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8" role="alert">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($upload_errors) && count($upload_errors) > 0): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8" role="alert">
                    <p class="font-bold">Warnings:</p>
                    <ul class="list-disc list-inside">
                        <?php foreach ($upload_errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Add margin-bottom to create space between button and table container -->
            <div class="mb-8 text-right space-x-4">
                <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:-translate-y-1" id="bulkUploadBtn">
                    <i class="fas fa-file-upload mr-2"></i> Add Bulk Students
                </button>
                <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:-translate-y-1" id="openModalBtn">
                    <i class="fas fa-plus-circle mr-2"></i> Add New Student
                </button>
            </div>

            <!-- Increased padding and added more shadow -->
            <div class="bg-white rounded-xl shadow-xl p-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider cursor-pointer hover:bg-red-100 transition-colors" data-sort="student_id">
                                <div class="flex items-center">
                                    Student ID
                                    <span class="sort-icon ml-1">↕</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider cursor-pointer hover:bg-red-100 transition-colors" data-sort="student_name">
                                <div class="flex items-center">
                                    Student Name
                                    <span class="sort-icon ml-1">↕</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider cursor-pointer hover:bg-red-100 transition-colors" data-sort="college_name">
                                <div class="flex items-center">
                                    College
                                    <span class="sort-icon ml-1">↕</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider w-1/8">Password</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider cursor-pointer hover:bg-red-100 transition-colors" data-sort="has_voted">
                                <div class="flex items-center">
                                    Has Voted
                                    <span class="sort-icon ml-1">↕</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider"></th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($students as $student): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['college_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="blur-text cursor-pointer inline-block px-2 py-1 bg-gray-100 rounded">
                                    <?php echo htmlspecialchars($student['password']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo $student['has_voted'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $student['has_voted'] ? 'Yes' : 'No'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="edit-btn bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-300 transform hover:-translate-y-1" 
                                        data-student='<?php echo json_encode($student); ?>'>
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="delete-btn bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition duration-300 transform hover:-translate-y-1" 
                                        data-student-id="<?php echo $student['student_id']; ?>">
                                    <i class="fas fa-trash-alt mr-2"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Update both modals with enhanced styling -->
    <div id="myModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-8 border w-full max-w-4xl shadow-2xl rounded-2xl bg-white transform transition-all">
            <!-- Header Section -->
            <div class="absolute top-0 left-0 right-0 h-16 bg-gradient-to-r from-red-600 to-red-800 rounded-t-2xl">
                <div class="flex justify-between items-center h-full px-8">
                    <h3 class="text-2xl font-bold text-white" id="modalTitle">Add New Student</h3>
                    <span class="close cursor-pointer text-white text-3xl hover:text-gray-200 transition-colors">&times;</span>
                </div>
            </div>

            <!-- Form Section with added top padding for header -->
            <form class="space-y-6 pt-20" id="studentForm" method="POST" action="create_student.php">
                <input type="hidden" id="studentFormId" name="studentFormId">
                
                <div class="grid grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6 bg-gray-50 p-6 rounded-xl">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Student ID</label>
                            <input type="text" id="studentId" name="studentId" required
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Student Name</label>
                            <input type="text" id="studentName" name="studentName" required
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6 bg-gray-50 p-6 rounded-xl">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">College/Department</label>
                            <select id="college" name="college" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                                <option value="">Select College</option>
                                <?php foreach ($colleges as $college): ?>
                                    <option value="<?php echo htmlspecialchars($college['college_id']); ?>">
                                        <?php echo htmlspecialchars($college['college_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Has Voted</label>
                            <select id="hasVoted" name="hasVoted" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                        onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="togglePassword"></i>
                                </button>
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

    <div id="bulkUploadModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-8 border w-full max-w-2xl shadow-2xl rounded-2xl bg-white transform transition-all">
            <!-- Header Section -->
            <div class="absolute top-0 left-0 right-0 h-16 bg-gradient-to-r from-red-600 to-red-800 rounded-t-2xl">
                <div class="flex justify-between items-center h-full px-8">
                    <h3 class="text-2xl font-bold text-white">Bulk Upload Voters</h3>
                    <span class="close cursor-pointer text-white text-3xl hover:text-gray-200 transition-colors">&times;</span>
                </div>
            </div>

            <!-- Form Section -->
            <form class="space-y-6 pt-20" id="bulkUploadForm" method="POST" action="process_bulk_upload.php" enctype="multipart/form-data">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                CSV file must contain these columns in order:<br>
                                <code class="bg-yellow-100 px-1 rounded">student_id, student_name, college_id, password</code>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 bg-gray-50 p-6 rounded-xl">
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-file-csv text-4xl text-gray-400 mb-2"></i>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-gray-500">CSV file only</p>
                            </div>
                            <input type="file" name="csvFile" id="csvFile" accept=".csv" class="hidden" required>
                        </label>
                    </div>
                    <div id="fileNameDisplay" class="text-sm text-gray-600 text-center hidden">
                        Selected file: <span class="font-medium"></span>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-200">
                    <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-800 text-white font-bold py-3 px-8 rounded-xl hover:from-red-700 hover:to-red-900 transform hover:-translate-y-0.5 transition-all duration-200">
                        Upload and Process
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById("myModal");
        const openModalBtn = document.getElementById("openModalBtn");
        const closeBtns = document.querySelectorAll(".close");
        const modalTitle = document.getElementById("modalTitle");

        openModalBtn.addEventListener("click", () => {
            document.getElementById('studentForm').action = 'create_student.php';
            document.getElementById('studentFormId').value = '';
            document.getElementById('studentId').value = '';
            document.getElementById('studentName').value = '';
            document.getElementById('college').value = '';
            document.getElementById('hasVoted').value = '0';
            document.getElementById('password').value = '';
            modalTitle.textContent = "Add New Student";
            modal.style.display = "block";
        });
        closeBtns.forEach(btn => btn.addEventListener("click", () => modal.style.display = "none"));
        window.addEventListener("click", (event) => {
            if (event.target == modal) modal.style.display = "none";
        });

        // Edit button functionality
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const student = JSON.parse(this.getAttribute('data-student'));
                document.getElementById('studentForm').action = 'edit_student.php';
                document.getElementById('studentFormId').value = student.student_id;
                document.getElementById('studentId').value = student.student_id;
                document.getElementById('studentName').value = student.student_name;
                document.getElementById('password').value = student.password;
                document.getElementById('college').value = student.college_id;
                document.getElementById('hasVoted').value = student.has_voted;
                modalTitle.textContent = "Edit Student";
                modal.style.display = "block";
            });
        });

        // Delete button functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-student-id');
                if (confirm('Are you sure you want to delete this student?')) {
                    fetch('delete_student.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ studentId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to delete student. Please try again.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });

        // Add password toggle functionality
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Updated table sorting functionality
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('table');
            const allHeaders = table.querySelectorAll('th');
            let currentSort = {
                column: null,
                direction: 'asc'
            };

            allHeaders.forEach((header, headerIndex) => {
                if (header.dataset.sort) {
                    header.addEventListener('click', () => {
                        const column = header.dataset.sort;
                        
                        // Reset all headers
                        allHeaders.forEach(h => {
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
                            let aValue = a.querySelector(`td:nth-child(${headerIndex + 1})`).textContent.trim();
                            let bValue = b.querySelector(`td:nth-child(${headerIndex + 1})`).textContent.trim();

                            // Special handling for has_voted column
                            if (column === 'has_voted') {
                                aValue = aValue === 'Yes' ? 1 : 0;
                                bValue = bValue === 'Yes' ? 1 : 0;

                                return currentSort.direction === 'asc' ? aValue - bValue : bValue - aValue;
                            }

                            // Handle student_id as strings with numeric comparison
                            if (column === 'student_id') {
                                return currentSort.direction === 'asc' 
                                    ? aValue.localeCompare(bValue, undefined, { numeric: true })
                                    : bValue.localeCompare(aValue, undefined, { numeric: true });
                            }

                            // For other columns
                            if (currentSort.direction === 'asc') {
                                return aValue.localeCompare(bValue);
                            } else {
                                return bValue.localeCompare(aValue);
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
                }
            });
        });

        // Bulk upload modal functionality
        const bulkUploadModal = document.getElementById("bulkUploadModal");
        const bulkUploadBtn = document.getElementById("bulkUploadBtn");
        const csvFileInput = document.getElementById("csvFile");
        const fileNameDisplay = document.getElementById("fileNameDisplay");

        bulkUploadBtn.addEventListener("click", () => {
            bulkUploadModal.style.display = "block";
        });

        // File input change handler
        csvFileInput.addEventListener("change", (e) => {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                fileNameDisplay.classList.remove("hidden");
                fileNameDisplay.querySelector("span").textContent = fileName;
            } else {
                fileNameDisplay.classList.add("hidden");
            }
        });

        // Add bulk upload modal to the existing close buttons handler
        document.querySelectorAll(".close").forEach(btn => {
            btn.addEventListener("click", () => {
                bulkUploadModal.style.display = "none";
                csvFileInput.value = "";
                fileNameDisplay.classList.add("hidden");
            });
        });
    </script>
</body>
</html>
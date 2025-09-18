<?php
// STEP 1: Include the database connection.
// This also starts the session and gives us access to our helper functions.
require_once 'core/db_connect.php';

// STEP 2: Secure the page.
// The require_login() function will check if a user_id exists in the session.
// If not, it will automatically redirect the user to polling-unit-login.php and stop script execution.
require_login();

// We can also add another layer of security to ensure only clerks can access this page.
if ($_SESSION['role'] !== 'clerk') {
    // If an admin somehow gets here, destroy their session and send to the home page.
    session_destroy();
    header("Location: index.php");
    exit;
}

// STEP 3: Fetch all required data for the page in one go.
// This is more efficient than multiple separate queries.
$userId = $_SESSION['user_id'];
$pollingUnitId = $_SESSION['polling_unit_id'];

// Prepare the main query to get user and location details
$stmt = $conn->prepare(
    "SELECT u.full_name, u.phone_number, pu.name AS pu_name, pu.pu_code, w.name AS ward_name, l.name AS lga_name, s.name AS state_name
     FROM users u
     JOIN polling_units pu ON u.polling_unit_id = pu.id
     JOIN wards w ON pu.ward_id = w.id
     JOIN lgas l ON w.lga_id = l.id
     JOIN states s ON l.state_id = s.id
     WHERE u.id = ?"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$clerkData = $result->fetch_assoc();

if (!$clerkData) {
    // If for some reason the clerk data doesn't exist, log out safely.
    session_destroy();
    header("Location: index.php");
    exit;
}
$stmt->close();

// Sanitize data for safe display
$clerkName = htmlspecialchars($clerkData['full_name']);
$clerkPhone = htmlspecialchars($clerkData['phone_number']);
$puName = htmlspecialchars($clerkData['pu_name']);
$puCode = htmlspecialchars($clerkData['pu_code']);
$wardName = htmlspecialchars($clerkData['ward_name']);
$lgaName = htmlspecialchars($clerkData['lga_name']);
$stateName = htmlspecialchars($clerkData['state_name']);

// Fetch all political parties to dynamically build the scoring form
$parties_result = $conn->query("SELECT id, name, acronym, logo_path FROM political_parties ORDER BY acronym ASC");
$political_parties = $parties_result->fetch_all(MYSQLI_ASSOC);

// Fetch all states for the location dropdown
$states_result = $conn->query("SELECT id, name FROM states ORDER BY name ASC");
$states = $states_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Entry - INEC Portal</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Syne (Headings) & Lexend (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Link to external stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/webp" href="assets/images/favicon.webp">

    <!-- Custom Tailwind Configuration -->
    <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Lexend', 'sans-serif'],
            display: ['Syne', 'sans-serif'],
          },
          colors: {
            'inec-green': '#006A4E', 
            'inec-red': '#D40028',
          }
        }
      }
    }
    </script>
    <style>
        /* Custom style for invalid form fields */
        .form-input-error {
            border-color: #D40028 !important;
            box-shadow: 0 0 0 1px #D40028 !important;
        }
        .error-message {
            color: #D40028;
            font-size: 0.875rem; /* text-sm */
            margin-top: 0.25rem; /* mt-1 */
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="container mx-auto px-6 py-3 flex items-center justify-between">
            <a href="index.php" class="flex items-center">
                <img src="assets/images/INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto">
            </a>
            <button type="button" id="cancel-button" class="bg-red-100 text-inec-red font-semibold px-4 py-2 rounded-lg hover:bg-red-200 transition-colors">
                <i class="bi bi-x-circle mr-2"></i>Cancel Entry
            </button>
        </div>
    </header>

    <main class="container mx-auto my-12 px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6 sm:p-10 border border-slate-200">
            
            <!-- User Details Display Section - DYNAMIC -->
            <div class="bg-slate-50 border border-dashed border-slate-300 rounded-lg p-6 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-slate-500">Clerk</p>
                        <p class="font-semibold text-slate-800 text-base"><?php echo $clerkName; ?> (<?php echo $clerkPhone; ?>)</p>
                    </div>
                     <div>
                        <p class="text-slate-500">Polling Unit</p>
                        <p class="font-semibold text-slate-800 text-base"><?php echo $puName; ?> (<?php echo $puCode; ?>)</p>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                         <p class="text-slate-500">Location</p>
                        <p class="font-semibold text-slate-800 text-base"><?php echo $wardName; ?> | <?php echo $lgaName; ?> | <?php echo $stateName; ?></p>
                    </div>
                </div>
            </div>

            <!-- Stepper -->
            <div class="flex items-start mb-10">
                <div class="step active flex flex-col items-center w-1/4">
                    <div class="step-icon w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-200 flex items-center justify-center font-bold text-lg transition-all duration-300">1</div>
                    <p class="mt-2 text-sm font-semibold text-center">Location</p>
                </div>
                <div class="connector flex-1 h-1 bg-gray-200 transition-all duration-300 mt-5"></div>
                <div class="step flex flex-col items-center w-1/4">
                    <div class="step-icon w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-200 flex items-center justify-center font-bold text-lg transition-all duration-300">2</div>
                    <p class="mt-2 text-sm text-gray-500 text-center">Statistics</p>
                </div>
                <div class="connector flex-1 h-1 bg-gray-200 transition-all duration-300 mt-5"></div>
                <div class="step flex flex-col items-center w-1/4">
                    <div class="step-icon w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-200 flex items-center justify-center font-bold text-lg transition-all duration-300">3</div>
                    <p class="mt-2 text-sm text-gray-500 text-center">Scores</p>
                </div>
                <div class="connector flex-1 h-1 bg-gray-200 transition-all duration-300 mt-5"></div>
                <div class="step flex flex-col items-center w-1/4">
                    <div class="step-icon w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-200 flex items-center justify-center font-bold text-lg transition-all duration-300">4</div>
                    <p class="mt-2 text-sm text-gray-500 text-center">Review</p>
                </div>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-inec-red text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                </div>
            <?php endif; ?>

            <form id="electionForm" method="POST" action="core/process_result.php" enctype="multipart/form-data" novalidate>
                <!-- Hidden fields to pass essential IDs to the server -->
                <input type="hidden" name="polling_unit_id" value="<?php echo htmlspecialchars($pollingUnitId); ?>">
                
                <!-- Step 1: Location -->
                <div class="form-step" data-step="1">
                    <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 1: Polling Unit Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="state" class="block text-sm font-medium text-slate-700 mb-1">State</label>
                            <select id="state" name="state" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required>
                                <option value="" selected disabled>Select State...</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state['id']); ?>"><?php echo htmlspecialchars($state['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="lga" class="block text-sm font-medium text-slate-700 mb-1">Local Government (LGA)</label>
                            <select id="lga" name="lga" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required disabled>
                                <option value="" selected disabled>Select LGA...</option>
                            </select>
                        </div>
                        <div>
                            <label for="ward" class="block text-sm font-medium text-slate-700 mb-1">Ward (Registration Area)</label>
                            <select id="ward" name="ward" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required disabled>
                                <option value="" selected disabled>Select Ward...</option>
                            </select>
                        </div>
                        <div>
                            <label for="polling_unit_name" class="block text-sm font-medium text-slate-700 mb-1">Polling Unit Name / Number</label>
                            <input type="text" id="polling_unit_name" name="polling_unit_name" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" placeholder="e.g., Town Hall 003" required>
                        </div>
                    </div>
                    <div class="flex justify-end mt-8"><button type="button" class="next-btn bg-inec-green text-white font-semibold py-3 px-6 rounded-md hover:opacity-90 transition shadow">Next: Statistics &rarr;</button></div>
                </div>

                <!-- Step 2: Statistics -->
                <div class="form-step hidden" data-step="2">
                    <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 2: Voter & Ballot Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label for="registered_voters" class="block text-sm font-medium text-slate-700 mb-1">Number of Voters on the Register</label><input type="number" id="registered_voters" name="registered_voters" min="0" class="w-full p-3 border border-slate-300 rounded-md" required></div>
                        <div><label for="accredited_voters" class="block text-sm font-medium text-slate-700 mb-1">Number of Accredited Voters</label><input type="number" id="accredited_voters" name="accredited_voters" min="0" class="w-full p-3 border border-slate-300 rounded-md" required></div>
                        <div><label for="ballot_papers_issued" class="block text-sm font-medium text-slate-700 mb-1">Ballot Papers Issued</label><input type="number" id="ballot_papers_issued" name="ballot_papers_issued" min="0" class="w-full p-3 border border-slate-300 rounded-md" required></div>
                        <div><label for="unused_ballots" class="block text-sm font-medium text-slate-700 mb-1">Unused Ballot Papers</label><input type="number" id="unused_ballots" name="unused_ballots" min="0" class="w-full p-3 border border-slate-300 rounded-md" required></div>
                        <div><label for="spoiled_ballots" class="block text-sm font-medium text-slate-700 mb-1">Spoiled Ballot Papers</label><input type="number" id="spoiled_ballots" name="spoiled_ballots" min="0" class="w-full p-3 border border-slate-300 rounded-md" required></div>
                        <div><label for="rejected_ballots" class="block text-sm font-medium text-slate-700 mb-1">Rejected Ballots</label><input type="number" id="rejected_ballots" name="rejected_ballots" min="0" class="w-full p-3 border border-slate-300 rounded-md" required></div>
                    </div>
                    <div class="flex justify-between mt-8"><button type="button" class="back-btn bg-slate-200 text-slate-800 font-semibold py-3 px-6 rounded-md hover:bg-slate-300">Back</button><button type="button" class="next-btn bg-inec-green text-white font-semibold py-3 px-6 rounded-md hover:opacity-90 transition shadow">Next: Party Scores &rarr;</button></div>
                </div>
                
                <!-- Step 3: Scores -->
                <div class="form-step hidden" data-step="3">
                     <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 3: Political Party Scores</h3>
                    <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200"><thead class="bg-slate-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Party</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase w-48">Score</th></tr></thead><tbody class="bg-white divide-y divide-slate-200">
                        <?php foreach ($political_parties as $party): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="<?php echo htmlspecialchars($party['logo_path'] ?? 'assets/images/INEC-Logo.png'); ?>" class="h-10 w-10 object-contain">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($party['name']); ?></div>
                                        <div class="text-sm text-slate-500"><?php echo htmlspecialchars($party['acronym']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <input type="number" name="party_scores[<?php echo htmlspecialchars($party['id']); ?>]" min="0" class="party-score-input w-full p-2 border border-slate-300 rounded-md" value="0" required data-party-name="<?php echo htmlspecialchars($party['acronym']); ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                    <div class="mt-6">
                        <label for="total_valid_votes" class="block text-sm font-medium text-slate-700 mb-1">Total Valid Votes (Auto-calculated)</label>
                        <input type="number" id="total_valid_votes" name="total_valid_votes" class="w-full p-3 bg-slate-100 border font-bold rounded-md" readonly>
                    </div>
                    <div class="flex justify-between mt-8"><button type="button" class="back-btn bg-slate-200 text-slate-800 font-semibold py-3 px-6 rounded-md">Back</button><button type="button" class="next-btn bg-inec-green text-white font-semibold py-3 px-6 rounded-md">Next: Review &rarr;</button></div>
                </div>

                <!-- Step 4: Review -->
                <div class="form-step hidden" data-step="4">
                    <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 4: Review and Submit</h3>
                    <div id="review-section" class="space-y-6 bg-slate-50 p-6 rounded-lg border">
                        <!-- Review content will be populated by JavaScript -->
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Upload Scanned Result Sheet (Image files only)</label>
                        <label for="result_sheet" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6"><i class="bi bi-cloud-upload-fill text-4xl text-slate-400"></i><p class="mb-2 text-sm text-slate-500"><span class="font-semibold">Click to upload</span> or drag and drop</p></div>
                            <input id="result_sheet" name="result_sheet" type="file" class="hidden" required accept="image/png, image/jpeg, image/webp">
                        </label>
                        <p id="file-name-display" class="mt-2 text-sm text-slate-600 font-medium"></p>
                    </div>
                    <div class="flex justify-between mt-8"><button type="button" class="back-btn bg-slate-200 text-slate-800 font-semibold py-3 px-6 rounded-md">Back</button><button type="submit" id="submit-btn" class="bg-inec-red text-white font-semibold py-3 px-6 rounded-md hover:opacity-90 transition shadow">Confirm & Submit</button></div>
                </div>
            </form>
        </div>
    </main>
    
    <!-- Cancel Modal -->
    <div id="cancel-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-xl shadow-2xl p-8 text-center w-full max-w-md transform transition-all duration-300 scale-95 opacity-0">
            <h2 class="font-display font-bold text-2xl text-slate-800 mb-2">Are you sure?</h2>
            <p class="text-slate-600 mb-8">Any unsaved progress on this form will be lost.</p>
            <div class="flex justify-center gap-4">
                <button id="stay-btn" type="button" class="bg-slate-200 font-semibold w-full py-3 rounded-lg hover:bg-slate-300 transition">No, Stay</button>
                <a href="thank-you.php?action=cancel" class="bg-inec-red text-white font-semibold w-full py-3 rounded-lg hover:opacity-90 transition">Yes, Exit</a>
            </div>
        </div>
    </div>
    
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="container mx-auto px-6 py-6"><p class="text-center text-slate-500 text-sm">&copy; <?php echo date('Y'); ?> Electoral Commission. All Rights Reserved.</p></div>
    </footer>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('electionForm');
        const formSteps = Array.from(form.querySelectorAll('.form-step'));
        const stepperItems = Array.from(document.querySelectorAll('.step'));
        const connectors = Array.from(document.querySelectorAll('.connector'));
        let currentStep = 0;

        // --- DYNAMIC LOCATION DROPDOWN LOGIC ---
        // This section will fetch location data from a backend API endpoint.
        const stateSelect = document.getElementById('state');
        const lgaSelect = document.getElementById('lga');
        const wardSelect = document.getElementById('ward');

        async function fetchLocations(level, parentId) {
            // NOTE: 'core/api_get_locations.php' needs to be created.
            // It should accept 'level' (lga or ward) and 'id' (state_id or lga_id)
            // and return a JSON array of objects with 'id' and 'name'.
            try {
                const response = await fetch(`core/api_get_locations.php?level=${level}&id=${parentId}`);
                if (!response.ok) throw new Error('Network response was not ok');
                return await response.json();
            } catch (error) {
                console.error('Failed to fetch locations:', error);
                return []; // Return empty array on error
            }
        }

        stateSelect.addEventListener('change', async function() {
            lgaSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
            wardSelect.innerHTML = '<option value="" selected disabled>Select Ward...</option>';
            lgaSelect.disabled = true; wardSelect.disabled = true;

            if (this.value) {
                const lgas = await fetchLocations('lga', this.value);
                lgaSelect.innerHTML = '<option value="" selected disabled>Select LGA...</option>';
                lgas.forEach(lga => lgaSelect.add(new Option(lga.name, lga.id)));
                lgaSelect.disabled = false;
            }
        });

        lgaSelect.addEventListener('change', async function() {
            wardSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
            wardSelect.disabled = true;
            if (this.value) {
                const wards = await fetchLocations('ward', this.value);
                wardSelect.innerHTML = '<option value="" selected disabled>Select Ward...</option>';
                wards.forEach(ward => wardSelect.add(new Option(ward.name, ward.id)));
                wardSelect.disabled = false;
            }
        });

        // --- FORM VALIDATION LOGIC ---
        function clearError(input) {
            input.classList.remove('form-input-error');
            const errorMsg = input.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        }

        function showError(input, message) {
            clearError(input);
            input.classList.add('form-input-error');
            const error = document.createElement('p');
            error.className = 'error-message';
            error.textContent = message;
            input.parentElement.appendChild(error);
        }

        function validateStep(stepIndex) {
            let isValid = true;
            const stepDiv = formSteps[stepIndex];
            const inputs = stepDiv.querySelectorAll('input[required], select[required]');

            inputs.forEach(input => {
                clearError(input);
                if (!input.value || (input.type === 'select-one' && input.value === "")) {
                    isValid = false;
                    showError(input, 'This field is required.');
                } else if (input.type === 'number' && parseInt(input.value) < 0) {
                     isValid = false;
                    showError(input, 'Value cannot be negative.');
                }
            });
            return isValid;
        }

        // --- MULTI-STEP FORM NAVIGATION ---
        const updateView = () => {
            formSteps.forEach((step, index) => step.classList.toggle('hidden', index !== currentStep));
            stepperItems.forEach((step, index) => {
                const icon = step.querySelector('.step-icon');
                const text = step.querySelector('p');
                icon.classList.remove('bg-inec-green', 'text-white', 'border-inec-green', 'bg-green-100', 'border-green-500', 'text-green-600');
                text.classList.remove('font-bold', 'text-inec-green');

                if (index < currentStep) {
                    icon.classList.add('bg-green-100', 'border-green-500', 'text-green-600');
                    icon.innerHTML = '&#10003;'; // Checkmark
                } else if (index === currentStep) {
                    icon.classList.add('bg-inec-green', 'text-white', 'border-inec-green');
                    text.classList.add('font-bold', 'text-inec-green');
                    icon.innerHTML = index + 1;
                } else {
                    icon.innerHTML = index + 1;
                }
            });
            connectors.forEach((connector, index) => {
                connector.classList.toggle('bg-inec-green', index < currentStep);
            });
        };

        const populateReview = () => {
            const formData = new FormData(form);
            const getSelectedText = (selectId) => {
                const sel = document.getElementById(selectId);
                return sel.selectedIndex > 0 ? sel.options[sel.selectedIndex].text : 'N/A';
            };
            
            let partyScoresHtml = Array.from(form.querySelectorAll('.party-score-input')).map(input => `
                <tr>
                    <td class="py-1 font-medium text-slate-500">${input.dataset.partyName}</td>
                    <td class="py-1 font-semibold text-slate-800 text-right">${parseInt(input.value || 0).toLocaleString()}</td>
                </tr>
            `).join('');

            document.getElementById('review-section').innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                    <div>
                        <h4 class="font-display text-lg font-bold mb-2">Polling Unit Information</h4>
                        <dl class="text-sm space-y-2">
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">State</dt><dd class="font-semibold text-slate-800">${getSelectedText('state')}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">LGA</dt><dd class="font-semibold text-slate-800">${getSelectedText('lga')}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">Ward</dt><dd class="font-semibold text-slate-800">${getSelectedText('ward')}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">Polling Unit</dt><dd class="font-semibold text-slate-800">${formData.get('polling_unit_name') || 'N/A'}</dd></div>
                        </dl>
                    </div>
                     <div>
                        <h4 class="font-display text-lg font-bold mb-2">Voter & Ballot Statistics</h4>
                        <dl class="text-sm space-y-2">
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">Registered Voters</dt><dd class="font-semibold text-slate-800">${parseInt(formData.get('registered_voters') || 0).toLocaleString()}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">Accredited Voters</dt><dd class="font-semibold text-slate-800">${parseInt(formData.get('accredited_voters') || 0).toLocaleString()}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">Ballot Papers Issued</dt><dd class="font-semibold text-slate-800">${parseInt(formData.get('ballot_papers_issued') || 0).toLocaleString()}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">Unused Ballots</dt><dd class="font-semibold text-slate-800">${parseInt(formData.get('unused_ballots') || 0).toLocaleString()}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-slate-500">Spoiled Ballots</dt><dd class="font-semibold text-slate-800">${parseInt(formData.get('spoiled_ballots') || 0).toLocaleString()}</dd></div>
                             <div class="flex justify-between"><dt class="font-medium text-slate-500">Rejected Ballots</dt><dd class="font-semibold text-slate-800">${parseInt(formData.get('rejected_ballots') || 0).toLocaleString()}</dd></div>
                        </dl>
                    </div>
                    <div class="md:col-span-2">
                         <h4 class="font-display text-lg font-bold mb-2">Party Scores</h4>
                         <table class="w-full text-sm">
                             <tbody>${partyScoresHtml}</tbody>
                             <tfoot class="border-t-2 border-slate-300">
                                 <tr class="font-bold text-base">
                                     <td class="pt-2">Total Valid Votes</td>
                                     <td class="pt-2 text-right">${formData.get('total_valid_votes')}</td>
                                 </tr>
                             </tfoot>
                         </table>
                    </div>
                </div>`;
        };
        
        form.addEventListener('click', (e) => {
            if (e.target.matches('.next-btn') && currentStep < formSteps.length - 1) {
                if (!validateStep(currentStep)) return;
                if (currentStep === 2) { // Moving from Scores to Review
                    populateReview(); 
                }
                currentStep++;
                updateView();
            } else if (e.target.matches('.back-btn') && currentStep > 0) {
                currentStep--;
                updateView();
            }
        });
        
        form.addEventListener('submit', function(e) {
            // Final validation check before submitting
            if (!validateStep(0) || !validateStep(1) || !validateStep(2)) {
                e.preventDefault();
                alert('Please fill out all required fields in all steps before submitting.');
                return;
            }
             const fileInput = document.getElementById('result_sheet');
             clearError(fileInput.closest('label'));
             if (fileInput.files.length === 0) {
                 e.preventDefault();
                 showError(fileInput.closest('label'), 'Uploading the result sheet is mandatory.');
                 return;
             }
             // Disable button to prevent multiple submissions
             document.getElementById('submit-btn').disabled = true;
             document.getElementById('submit-btn').textContent = 'Submitting...';
        });

        // --- SCORE CALCULATION LOGIC ---
        const scoreInputs = form.querySelectorAll('.party-score-input');
        const totalVotesField = form.querySelector('#total_valid_votes');
        const calculateTotalVotes = () => { 
            let total = 0; 
            scoreInputs.forEach(input => { total += parseInt(input.value) || 0; }); 
            totalVotesField.value = total.toLocaleString(); 
        };
        scoreInputs.forEach(input => input.addEventListener('input', calculateTotalVotes));
        
        // --- ENHANCED FILE UPLOAD LOGIC ---
        const fileInput = document.getElementById('result_sheet');
        const fileDisplay = document.getElementById('file-name-display');
        const dropZone = fileInput.closest('label');

        fileInput.addEventListener('change', () => { 
            clearError(dropZone);
            if (fileInput.files.length > 0) {
                 const file = fileInput.files[0];
                 const fileSize = file.size / 1024 / 1024; // in MB
                 if (fileSize > 5) { // Max 5MB
                     showError(dropZone, 'File is too large. Maximum size is 5MB.');
                     fileInput.value = ''; // Clear the input
                     return;
                 }
                 fileDisplay.textContent = `File selected: ${file.name}`;
            } else {
                 fileDisplay.textContent = '';
            }
        });
        ['dragenter', 'dragover'].forEach(eventName => { dropZone.addEventListener(eventName, (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.add('bg-slate-200', 'border-inec-green'); }, false); });
        ['dragleave', 'drop'].forEach(eventName => { dropZone.addEventListener(eventName, (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('bg-slate-200', 'border-inec-green'); }, false); });
        dropZone.addEventListener('drop', (e) => { fileInput.files = e.dataTransfer.files; fileInput.dispatchEvent(new Event('change')); });

        // --- CANCEL MODAL LOGIC ---
        const cancelButton = document.getElementById('cancel-button');
        const cancelModal = document.getElementById('cancel-modal');
        const modalContent = cancelModal.querySelector('div');
        const stayBtn = document.getElementById('stay-btn');
        
        cancelButton.addEventListener('click', () => {
            cancelModal.classList.remove('hidden');
            setTimeout(() => {
                cancelModal.classList.remove('opacity-0');
                modalContent.classList.remove('opacity-0', 'scale-95');
            }, 10);
        });

        const hideCancelModal = () => {
            cancelModal.classList.add('opacity-0');
            modalContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => cancelModal.classList.add('hidden'), 300);
        };

        stayBtn.addEventListener('click', hideCancelModal);
        
        // Initial setup
        calculateTotalVotes();
        updateView();
    });
    </script>

</body>
</html>
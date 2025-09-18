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
        <style>/* Custom styles for the stepper */.step.active .step-icon { @apply bg-inec-green text-white border-inec-green; } .step.completed .step-icon { @apply bg-green-500 text-white border-green-500; } .step.completed .step-icon:after { content: '✓'; font-weight: bold; } .connector.completed { @apply bg-green-500; }</style>
    </head>
    <body class="bg-slate-50 font-sans text-slate-700">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-6 py-3"><a href="index.php" class="flex items-center"> <img src="INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto"> </a>
            </div>
        </header>
        <main class="container mx-auto my-12 px-4">
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6 sm:p-10 border border-slate-200">
                <!-- ADDED: User Details Display Section -->
                <div class="bg-slate-50 border border-dashed border-slate-300 rounded-lg p-4 mb-8 flex items-center gap-4">
                    <div class="flex-shrink-0"><i class="bi bi-person-check-fill text-3xl text-inec-green"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Data Entry Personnel</h4>
                        <p class="text-sm text-slate-600"> <span class="font-semibold"> <!-- PHP: echo htmlspecialchars($_SESSION['user_name']); -->
                            John Doe </span> 
                        (<span class="text-slate-500"> <!-- PHP: echo htmlspecialchars($_SESSION['user_email']); -->
                            you@example.com </span>) </p>
                    </div>
                </div>
                <!-- ENHANCED: Stepper -->
                <div class="flex items-center mb-10">
                    <div class="step active flex flex-col items-center w-1/4">
                        <div class="step-icon w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-200 flex items-center justify-center font-bold text-lg transition-all duration-300">1</div>
                        <p class="mt-2 text-sm font-semibold text-center">Location</p>
                    </div>
                    <div class="connector flex-1 h-1 bg-gray-200 transition-all duration-300" data-connector="1"></div>
                    <div class="step flex flex-col items-center w-1/4">
                        <div class="step-icon w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-200 flex items-center justify-center font-bold text-lg transition-all duration-300">2</div>
                        <p class="mt-2 text-sm text-gray-500 text-center">Statistics</p>
                    </div>
                    <div class="connector flex-1 h-1 bg-gray-200 transition-all duration-300" data-connector="2"></div>
                    <div class="step flex flex-col items-center w-1/4">
                        <div class="step-icon w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-200 flex items-center justify-center font-bold text-lg transition-all duration-300">3</div>
                        <p class="mt-2 text-sm text-gray-500 text-center">Scores</p>
                    </div>
                    <div class="connector flex-1 h-1 bg-gray-200 transition-all duration-300" data-connector="3"></div>
                    <div class="step flex flex-col items-center w-1/4">
                        <div class="step-icon w-10 h-10 rounded-full bg-gray-200 border-2 border-gray-200 flex items-center justify-center font-bold text-lg transition-all duration-300">4</div>
                        <p class="mt-2 text-sm text-gray-500 text-center">Review</p>
                    </div>
                </div>
                <form id="electionForm" method="POST" action="submit-result.php" enctype="multipart/form-data">
                    <!-- Step 1: Location -->
                    <div class="form-step" data-step="1">
                        <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 1: Polling Unit Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="state" class="block text-sm font-medium text-slate-700 mb-1">State</label>
                                <select id="state" name="state" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required>
                                    <option value="" selected disabled>Select State...</option>
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
                                <label for="polling_unit" class="block text-sm font-medium text-slate-700 mb-1">Polling Unit Name / Number</label>
                                <input type="text" id="polling_unit" name="polling_unit" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" placeholder="e.g., Town Hall 003" required>
                            </div>
                        </div>
                        <div class="flex justify-end mt-8">
                            <button type="button" class="next-btn bg-inec-green text-white font-semibold py-3 px-6 rounded-md hover:opacity-90 transition shadow">Next: Statistics &rarr;</button>
                        </div>
                    </div>
                    <!-- Step 2: Statistics -->
                    <div class="form-step hidden" data-step="2">
                        <!-- ... Step 2 content remains the same ... -->
                        <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 2: Voter & Ballot Statistics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="registered_voters" class="block text-sm font-medium text-slate-700 mb-1">Number of Voters on the Register</label>
                                <input type="number" id="registered_voters" name="registered_voters" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required>
                            </div>
                            <div>
                                <label for="accredited_voters" class="block text-sm font-medium text-slate-700 mb-1">Number of Accredited Voters</label>
                                <input type="number" id="accredited_voters" name="accredited_voters" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required>
                            </div>
                            <div>
                                <label for="ballot_papers_issued" class="block text-sm font-medium text-slate-700 mb-1">Ballot Papers Issued to Polling Unit</label>
                                <input type="number" id="ballot_papers_issued" name="ballot_papers_issued" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required>
                            </div>
                            <div>
                                <label for="unused_ballots" class="block text-sm font-medium text-slate-700 mb-1">Number of Unused Ballot Papers</label>
                                <input type="number" id="unused_ballots" name="unused_ballots" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required>
                            </div>
                            <div>
                                <label for="spoiled_ballots" class="block text-sm font-medium text-slate-700 mb-1">Number of Spoiled Ballot Papers</label>
                                <input type="number" id="spoiled_ballots" name="spoiled_ballots" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required>
                            </div>
                            <div>
                                <label for="rejected_ballots" class="block text-sm font-medium text-slate-700 mb-1">Number of Rejected Ballots</label>
                                <input type="number" id="rejected_ballots" name="rejected_ballots" class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition" required>
                            </div>
                        </div>
                        <div class="flex justify-between mt-8">
                            <button type="button" class="back-btn bg-slate-200 text-slate-800 font-semibold py-3 px-6 rounded-md hover:bg-slate-300 transition">&larr; Back</button>
                            <button type="button" class="next-btn bg-inec-green text-white font-semibold py-3 px-6 rounded-md hover:opacity-90 transition shadow">Next: Party Scores &rarr;</button>
                        </div>
                    </div>
                    <!-- Step 3: Scores -->
                    <div class="form-step hidden" data-step="3">
                        <!-- ... Step 3 content remains the same ... -->
                        <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 3: Political Party Scores</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Political Party</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-48">Votes Scored</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-slate-200 rounded-full flex items-center justify-center">
                                                    <i class="bi bi-flag text-slate-400"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900">All Progressives Congress</div>
                                                    <div class="text-sm text-slate-500">APC</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="party_scores[APC]" class="party-score-input w-full p-2 border border-slate-300 rounded-md" value="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-slate-200 rounded-full flex items-center justify-center">
                                                    <i class="bi bi-flag text-slate-400"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900">Labour Party</div>
                                                    <div class="text-sm text-slate-500">LP</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="party_scores[LP]" class="party-score-input w-full p-2 border border-slate-300 rounded-md" value="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-slate-200 rounded-full flex items-center justify-center">
                                                    <i class="bi bi-flag text-slate-400"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900">People's Democratic Party</div>
                                                    <div class="text-sm text-slate-500">PDP</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="party_scores[PDP]" class="party-score-input w-full p-2 border border-slate-300 rounded-md" value="0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            <label for="total_valid_votes" class="block text-sm font-medium text-slate-700 mb-1">Total Valid Votes (Auto-calculated)</label>
                            <input type="text" id="total_valid_votes" name="total_valid_votes" class="w-full p-3 bg-slate-100 border border-slate-300 rounded-md font-bold" readonly>
                        </div>
                        <div class="flex justify-between mt-8">
                            <button type="button" class="back-btn bg-slate-200 text-slate-800 font-semibold py-3 px-6 rounded-md hover:bg-slate-300 transition">&larr; Back</button>
                            <button type="button" class="next-btn bg-inec-green text-white font-semibold py-3 px-6 rounded-md hover:opacity-90 transition shadow">Next: Review &rarr;</button>
                        </div>
                    </div>
                    <!-- Step 4: Review -->
                    <div class="form-step hidden" data-step="4">
                        <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 4: Review and Submit</h3>
                        <div id="review-section" class="space-y-6 bg-slate-50 p-6 rounded-lg border border-slate-200">
                            <!-- JS will populate this section -->
                        </div>
                        <!-- ENHANCED: File Upload Section -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Upload Scanned Result Sheet</label>
                            <label for="result_sheet" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6"><i class="bi bi-cloud-upload-fill text-4xl text-slate-400"></i>
                                    <p class="mb-2 text-sm text-slate-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-slate-500">PNG, JPG or PDF</p>
                                </div>
                                <input id="result_sheet" name="result_sheet" type="file" class="hidden" required>
                            </label>
                            <p id="file-name-display" class="mt-2 text-sm text-slate-600 font-medium"></p>
                        </div>
                        <div class="flex justify-between mt-8">
                            <button type="button" class="back-btn bg-slate-200 text-slate-800 font-semibold py-3 px-6 rounded-md hover:bg-slate-300 transition">&larr; Back</button>
                            <button type="submit" class="bg-inec-red text-white font-semibold py-3 px-6 rounded-md hover:opacity-90 transition shadow">Confirm & Submit Result</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
        <footer class="bg-white border-t border-slate-200 mt-auto">
            <div class="container mx-auto px-6 py-6">
                <p class="text-center text-slate-500 text-sm">&copy; 2025 Electoral Commission. All Rights Reserved.</p>
            </div>
        </footer>
        <script>
        // --- DATA FOR CASCADING DROPDOWNS ---
        const electoralData = {
          "Lagos": { "Lagos Mainland": ["Otto/Iddo", "Yaba"], "Ikeja": ["Alausa", "Oregun"], "Ikorodu": ["Igbogbo-Baiyeku", "Imota"] },
          "Rivers": { "Port Harcourt": ["PHC Ward 1", "Diobu"], "Obio/Akpor": ["Rumuokoro", "Rumuodara"] },
          "Kano": { "Kano Municipal": ["Sharada", "Tudun Wazirchi"], "Fagge": ["Kantin Kwari", "Sabon Gari"] }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // ... CASCADING DROPDOWN LOGIC (remains the same) ...
            const stateSelect = document.getElementById('state');
            const lgaSelect = document.getElementById('lga');
            const wardSelect = document.getElementById('ward');
            for (const state in electoralData) { stateSelect.add(new Option(state, state)); }
            stateSelect.addEventListener('change', function() {
                lgaSelect.innerHTML = '<option value="" selected disabled>Select LGA...</option>';
                wardSelect.innerHTML = '<option value="" selected disabled>Select Ward...</option>';
                lgaSelect.disabled = true; wardSelect.disabled = true;
                if (this.value && electoralData[this.value]) {
                    lgaSelect.disabled = false;
                    for (const lga in electoralData[this.value]) { lgaSelect.add(new Option(lga, lga)); }
                }
            });
            lgaSelect.addEventListener('change', function() {
                wardSelect.innerHTML = '<option value="" selected disabled>Select Ward...</option>';
                wardSelect.disabled = true;
                const state = stateSelect.value;
                if (this.value && electoralData[state][this.value]) {
                    wardSelect.disabled = false;
                    electoralData[state][this.value].forEach(ward => wardSelect.add(new Option(ward, ward)));
                }
            });

            // --- MULTI-STEP FORM LOGIC ---
            const form = document.getElementById('electionForm');
            const formSteps = Array.from(form.querySelectorAll('.form-step'));
            const stepperItems = Array.from(document.querySelectorAll('.step'));
            const connectors = Array.from(document.querySelectorAll('.connector'));
            let currentStep = 0;

            const updateView = () => {
                formSteps.forEach((step, index) => step.classList.toggle('hidden', index !== currentStep));
                stepperItems.forEach((step, index) => {
                    const icon = step.querySelector('.step-icon');
                    const text = step.querySelector('p');
                    icon.classList.remove('active', 'completed');
                    icon.innerHTML = index + 1;
                    text.classList.add('text-gray-500');
                    if (index < currentStep) {
                        icon.classList.add('completed');
                        icon.innerHTML = '';
                    } else if (index === currentStep) {
                        icon.classList.add('active');
                        text.classList.remove('text-gray-500');
                    }
                });
                // Update connector lines
                connectors.forEach((connector, index) => {
                    connector.classList.toggle('completed', index < currentStep);
                });
            };

            const populateReview = () => { /* ... populate review logic (remains the same) ... */ };
            
            form.addEventListener('click', (e) => {
                if (e.target.matches('.next-btn') && currentStep < formSteps.length - 1) {
                    if (currentStep === 2) { populateReview(); }
                    currentStep++;
                    updateView();
                } else if (e.target.matches('.back-btn') && currentStep > 0) {
                    currentStep--;
                    updateView();
                }
            });

            // --- SCORE CALCULATION LOGIC (remains the same) ---
            const scoreInputs = form.querySelectorAll('.party-score-input');
            const totalVotesField = form.querySelector('#total_valid_votes');
            const calculateTotalVotes = () => {
                let total = 0;
                scoreInputs.forEach(input => { total += parseInt(input.value) || 0; });
                totalVotesField.value = total;
            };
            scoreInputs.forEach(input => input.addEventListener('input', calculateTotalVotes));
            
            // --- ENHANCED FILE UPLOAD LOGIC ---
            const fileInput = document.getElementById('result_sheet');
            const fileDisplay = document.getElementById('file-name-display');
            const dropZone = fileInput.closest('label');

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    fileDisplay.textContent = `File selected: ${fileInput.files[0].name}`;
                } else {
                    fileDisplay.textContent = '';
                }
            });

            // Add visual feedback for drag and drop
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('bg-slate-200', 'border-inec-green');
                }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('bg-slate-200', 'border-inec-green');
                }, false);
            });
            dropZone.addEventListener('drop', (e) => {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change')); // Trigger change event
            });
            
            // Initial setup
            calculateTotalVotes();
            updateView();
        });
    </script>
    </body>
</html>
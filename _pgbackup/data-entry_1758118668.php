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
        <style>
        /* Custom styles for the stepper */
        .step.active .step-icon { @apply bg-inec-green text-white border-inec-green; }
        .step.completed .step-icon { @apply bg-green-500 text-white border-green-500; }
        /* This rule now uses JS to set the content, but the styling remains */
        .step.completed .step-icon { font-weight: bold; }
        .connector.completed { @apply bg-green-500; }
    </style>
    </head>
    <body class="bg-slate-50 font-sans text-slate-700">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="container mx-auto px-6 py-3 flex justify-between items-center"><a href="index.php" class="flex items-center"> <img src="INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto"> </a>
                <!-- ADDED: Cancel Button -->
                <button type="button" id="cancel-btn" class="bg-red-100 text-inec-red font-semibold inline-block px-6 py-3 rounded-lg hover:bg-red-200 transition-colors"><i class="bi bi-x-circle mr-2"></i>Cancel Entry
                </button>
            </div>
        </header>
        <main class="container mx-auto my-12 px-4">
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6 sm:p-10 border border-slate-200">
                <!-- User Details Display Section -->
                <div class="bg-slate-50 border border-dashed border-slate-300 rounded-lg p-4 mb-8 flex items-center gap-4">
                    <div class="flex-shrink-0"><i class="bi bi-person-check-fill text-3xl text-inec-green"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Data Entry Personnel</h4>
                        <p class="text-sm text-slate-600"> <span class="font-semibold"> <!-- PHP would echo the user's name from session here -->
                            John Doe </span> 
                        (<span class="text-slate-500"> <!-- PHP would echo the user's email from session here -->
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
                    <!-- All form steps are included here... -->
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
                    <!-- Step 4: Review -->
                    <div class="form-step hidden" data-step="4">
                        <h3 class="font-display text-2xl font-bold mb-6 text-slate-800">Step 4: Review and Submit</h3>
                        <div id="review-section" class="space-y-6 bg-slate-50 p-6 rounded-lg border border-slate-200"></div>
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
        <!-- ADDED: Cancel Confirmation Modal -->
        <div id="cancel-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0 hidden">
            <div id="cancel-modal-content" class="bg-white rounded-xl shadow-2xl p-8 text-center w-full max-w-md transform transition-all duration-300 opacity-0 scale-95">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="bi bi-exclamation-triangle-fill text-5xl text-inec-red"></i>
                </div>
                <h1 class="font-display font-bold text-2xl text-slate-900">Confirm Exit</h1>
                <p class="text-slate-600 mt-2 mb-8">Are you sure you want to exit? All unsaved progress on this form will be lost.</p>
                <div class="flex justify-center gap-4">
                    <button id="close-modal-btn" type="button" class="bg-slate-200 text-slate-800 font-semibold py-3 px-8 rounded-md hover:bg-slate-300 transition">No, Stay</button><a href="index.php" class="bg-inec-red text-white font-semibold py-3 px-8 rounded-md hover:opacity-90 transition">Yes, Exit</a>
                </div>
            </div>
        </div>
        <script>
        // --- DATA FOR CASCADING DROPDOWNS ---
        const electoralData = { "Lagos": { "Lagos Mainland": ["Otto/Iddo", "Yaba"], "Ikeja": ["Alausa", "Oregun"] }, "Rivers": { "Port Harcourt": ["PHC Ward 1", "Diobu"] }, "Kano": { "Kano Municipal": ["Sharada"], "Fagge": ["Sabon Gari"] } };

        document.addEventListener('DOMContentLoaded', function() {
            // --- CASCADING DROPDOWN LOGIC (UNCHANGED) ---
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

            // --- STEPPER AND FORM LOGIC ---
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
                    icon.innerHTML = index + 1; // Default to number
                    text.classList.add('text-gray-500');

                    if (index < currentStep) {
                        icon.classList.add('completed');
                        icon.innerHTML = '✓'; // CORRECTED: Set innerHTML to checkmark
                    } else if (index === currentStep) {
                        icon.classList.add('active');
                        text.classList.remove('text-gray-500');
                    }
                });
                connectors.forEach((connector, index) => {
                    connector.classList.toggle('completed', index < currentStep);
                });
            };
            
            form.addEventListener('click', (e) => {
                if (e.target.matches('.next-btn') && currentStep < formSteps.length - 1) { currentStep++; updateView(); } 
                else if (e.target.matches('.back-btn') && currentStep > 0) { currentStep--; updateView(); }
            });

            // --- FILE UPLOAD LOGIC (UNCHANGED) ---
            const fileInput = document.getElementById('result_sheet');
            const fileDisplay = document.getElementById('file-name-display');
            fileInput.addEventListener('change', () => { fileDisplay.textContent = fileInput.files.length > 0 ? `File selected: ${fileInput.files[0].name}` : ''; });

            // --- CANCEL MODAL LOGIC ---
            const cancelBtn = document.getElementById('cancel-btn');
            const cancelModal = document.getElementById('cancel-modal');
            const cancelModalContent = document.getElementById('cancel-modal-content');
            const closeModalBtn = document.getElementById('close-modal-btn');

            const showModal = () => {
                cancelModal.classList.remove('hidden');
                setTimeout(() => {
                    cancelModal.classList.remove('opacity-0');
                    cancelModalContent.classList.remove('opacity-0', 'scale-95');
                }, 10);
            };

            const hideModal = () => {
                cancelModal.classList.add('opacity-0');
                cancelModalContent.classList.add('opacity-0', 'scale-95');
                setTimeout(() => cancelModal.classList.add('hidden'), 300); // Wait for transition to finish
            };

            cancelBtn.addEventListener('click', showModal);
            closeModalBtn.addEventListener('click', hideModal);
            cancelModal.addEventListener('click', (e) => {
                if (e.target === cancelModal) { // Close if clicking on the background overlay
                    hideModal();
                }
            });
            
            // --- INITIALIZE FORM ---
            updateView();
        });
    </script>
    </body>
</html>
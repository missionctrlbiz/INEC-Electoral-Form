<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Details - INEC Portal</title>
        <!-- Tailwind CSS via CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Google Fonts: Syne (Headings) & Lexend (Body) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            // Defining the official INEC colors
            'inec-green': '#006A4E', 
            'inec-red': '#D40028',
          }
        }
      }
    }
    </script>
    </head>
    <body class="bg-slate-50 font-sans text-slate-700">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-6 py-3"><a href="index.php" class="flex items-center"> <img src="INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto"> </a>
            </div>
        </header>
        <!-- Main Content: Centered Form -->
        <main class="flex items-center justify-center py-12 px-6">
            <div class="w-full max-w-lg bg-white p-8 md:p-10 rounded-xl shadow-lg border border-slate-200">
                <div class="text-center mb-8">
                    <h1 class="font-display font-bold text-3xl text-slate-900">Personnel Information</h1>
                    <p class="text-slate-500 mt-2">Please enter your details to proceed with data entry.</p>
                </div>
                <!-- Form with an ID for JavaScript targeting -->
                <form id="details-form" class="space-y-6">
                    <!-- Full Name Field -->
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="bi bi-person text-gray-400"></i>
                            </div>
                            <input type="text" name="full_name" id="full_name" class="block w-full rounded-md border-gray-300 py-3 pl-10 pr-3 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="e.g., John Doe" required>
                        </div>
                    </div>
                    <!-- Email Address Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="bi bi-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" id="email" class="block w-full rounded-md border-gray-300 py-3 pl-10 pr-3 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="you@example.com" required>
                        </div>
                    </div>
                    <!-- Phone Number Field -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="bi bi-telephone text-gray-400"></i>
                            </div>
                            <input type="tel" name="phone_number" id="phone_number" class="block w-full rounded-md border-gray-300 py-3 pl-10 pr-3 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="e.g., 08012345678" pattern="[0-9]{11}" title="Please enter an 11-digit phone number" required>
                        </div>
                    </div>
                    <!-- Submit Button -->
                    <div>
                        <!-- Changed type to "button" to prevent default submission -->
                        <button type="button" id="submit-button" class="group relative flex w-full justify-center rounded-md border border-transparent bg-inec-green py-3 px-4 text-lg font-semibold text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-inec-green focus:ring-offset-2 transition-all">
                            Save & Proceed<span class="absolute right-0 inset-y-0 flex items-center pr-4"> <i class="bi bi-arrow-right-circle-fill text-white"></i> </span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto">
            <div class="container mx-auto px-6 py-6">
                <p class="text-center text-slate-500 text-sm">&copy; 2025 Electoral Commission. All Rights Reserved.</p>
            </div>
        </footer>
        <!-- MODAL POP-UP: Hidden by default -->
        <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0 hidden">
            <div id="modal-content" class="bg-white rounded-xl shadow-2xl p-8 text-center w-full max-w-md transform transition-all duration-300 opacity-0 scale-95">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="bi bi-check2-circle text-5xl text-green-600"></i>
                </div>
                <h1 class="font-display font-bold text-3xl text-slate-900">Details Confirmed!</h1>
                <p class="text-slate-600 mt-2 mb-6">Your details have been securely saved. You may now proceed to the main data entry form.</p>
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-left space-y-2 mb-8">
                    <div>
                        <p class="text-xs text-slate-500">Full Name</p>
                        <p id="modal-name" class="font-semibold text-slate-800"></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Email Address</p>
                        <p id="modal-email" class="font-semibold text-slate-800"></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Phone Number</p>
                        <p id="modal-phone" class="font-semibold text-slate-800"></p>
                    </div>
                </div><a href="data-entry.php" class="group relative flex w-full justify-center rounded-md border border-transparent bg-inec-green py-3 px-4 text-lg font-semibold text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-inec-green focus:ring-offset-2 transition-all">
                Proceed to Data Entry <span class="absolute right-0 inset-y-0 flex items-center pr-4"> <i class="bi bi-arrow-right-circle text-white"></i> </span> </a>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('details-form');
            const submitButton = document.getElementById('submit-button');
            const modal = document.getElementById('success-modal');
            const modalContent = document.getElementById('modal-content');
            
            const modalName = document.getElementById('modal-name');
            const modalEmail = document.getElementById('modal-email');
            const modalPhone = document.getElementById('modal-phone');

            submitButton.addEventListener('click', () => {
                // Check if the form is valid using HTML5 validation
                if (form.checkValidity()) {
                    // In a real app, you would use fetch() here to submit data to the server
                    // For this UI demo, we'll just show the modal directly.
                    
                    // 1. Get values from the form
                    const name = document.getElementById('full_name').value;
                    const email = document.getElementById('email').value;
                    const phone = document.getElementById('phone_number').value;

                    // 2. Populate the modal with the form data
                    modalName.textContent = name;
                    modalEmail.textContent = email;
                    modalPhone.textContent = phone;

                    // 3. Show the modal with animation
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        modalContent.classList.remove('opacity-0', 'scale-95');
                    }, 10); // A tiny delay ensures the transition is smooth
                } else {
                    // If the form is not valid, trigger the browser's validation UI
                    form.reportValidity();
                }
            });
        });
    </script>
    </body>
</html>
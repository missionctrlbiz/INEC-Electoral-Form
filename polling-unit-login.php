<?php
// This must be the very first thing on the page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If the user is already logged in, redirect them to the data entry page.
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'clerk') {
    header('Location: data-entry.php');
    exit;
}
?>
<?php require_once 'core/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polling Unit Authentication - INEC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/webp" href="assets/images/favicon.webp">
    <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Lexend', 'sans-serif'], display: ['Syne', 'sans-serif'] },
          colors: { 'inec-green': '#006A4E', 'inec-red': '#D40028' }
        }
      }
    }
    </script>
    <style>
        /* Additional styling to prevent autofill styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #374151 !important;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700">

    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                <img src="assets/images/INEC-Logo.png" alt="INEC Logo" class="mx-auto h-20 w-auto">
                <h1 class="mt-6 font-display font-bold text-3xl text-slate-900">Polling Unit Authentication</h1>
                <p class="mt-2 text-slate-600">Please authenticate to proceed with result submission.</p>
            </div>
            
            <div class="bg-white p-8 rounded-xl shadow-lg border border-slate-200">
                <div id="error-container" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert"></div>

                <form id="pin-form" class="space-y-6" autocomplete="off">
                    <div>
                        <label for="pu_code" class="block text-sm font-medium text-gray-700 mb-1">Polling Unit Code</label>
                        <input id="pu_code" name="pu_code" type="text" required 
                               autocomplete="off" 
                               autocapitalize="off" 
                               autocorrect="off"
                               spellcheck="false"
                               class="block w-full rounded-md border-2 border-gray-300 py-3 px-4 shadow-sm focus:border-inec-green focus:ring-inec-green text-lg" 
                               placeholder="Enter polling unit code">
                    </div>
                    <div>
                        <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">PIN</label>
                        <input id="pin" name="pin" type="password" required 
                               autocomplete="new-password" 
                               autocapitalize="off" 
                               autocorrect="off"
                               spellcheck="false"
                               class="block w-full rounded-md border-2 border-gray-300 py-3 px-4 shadow-sm focus:border-inec-green focus:ring-inec-green text-lg" 
                               placeholder="Enter your PIN">
                    </div>
                    <div>
                        <button type="submit" id="pin-submit-btn" class="group w-full flex justify-center py-3 px-4 rounded-md bg-inec-green text-lg font-semibold text-white hover:opacity-90 transition-all">
                            <span class="btn-text">Verify & Proceed</span>
                            <span class="spinner hidden animate-spin h-6 w-6 border-t-2 border-r-2 border-white rounded-full"></span>
                        </button>
                    </div>
                </form>
            </div>
             <p class="text-center text-slate-500 text-sm">&copy; 2025 Electoral Commission. All Rights Reserved.</p>
        </div>
    </div>
    
    <!-- VERIFICATION MODAL (Replaces OTP Modal) -->
    <div id="verification-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0 hidden">
        <div id="verification-modal-content" class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md transform transition-all duration-300 opacity-0 scale-95 relative">
             <button id="close-modal-btn" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg text-2xl"></i>
             </button>
             <div class="text-center">
                <h1 class="font-display font-bold text-3xl text-slate-900">Confirm Your Identity</h1>
                <p class="mt-2 text-slate-600">
                    For security, please enter the last 4 digits of your registered phone number.
                </p>
            </div>
             <form id="verification-form" action="core/process_verification.php" method="POST" class="space-y-6 mt-6" autocomplete="off">
                <div>
                    <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-1">Enter Last 4 Digits</label>
                    <input id="verification_code" name="verification_code" type="text" inputmode="numeric" pattern="\d{4}" maxlength="4" required 
                           autocomplete="off" 
                           autocapitalize="off" 
                           autocorrect="off"
                           spellcheck="false"
                           class="block w-full rounded-md border-2 border-gray-300 py-3 px-4 text-center text-2xl tracking-[.5em] font-semibold" 
                           placeholder="0000" 
                           autofocus>
                </div>
                <div>
                    <button type="submit" class="group w-full flex justify-center py-3 px-4 rounded-md bg-inec-green text-lg font-semibold text-white">Complete Authentication</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT FOR THE POP-UP -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pinForm = document.getElementById('pin-form');
            const pinSubmitBtn = document.getElementById('pin-submit-btn');
            const errorContainer = document.getElementById('error-container');
            const verificationModal = document.getElementById('verification-modal');
            const verificationModalContent = document.getElementById('verification-modal-content');
            const closeModalBtn = document.getElementById('close-modal-btn');

            // Clear all form fields on page load
            document.getElementById('pu_code').value = '';
            document.getElementById('pin').value = '';
            document.getElementById('verification_code').value = '';

            // Additional measure to prevent autofill
            setTimeout(() => {
                document.getElementById('pu_code').value = '';
                document.getElementById('pin').value = '';
            }, 100);

            function showModal() {
                verificationModal.classList.remove('hidden');
                setTimeout(() => {
                    verificationModal.classList.remove('opacity-0');
                    verificationModalContent.classList.remove('opacity-0', 'scale-95');
                }, 10);
            }

            function hideModal() {
                verificationModal.classList.add('opacity-0');
                verificationModalContent.classList.add('opacity-0', 'scale-95');
                setTimeout(() => verificationModal.classList.add('hidden'), 300);
            }

            closeModalBtn.addEventListener('click', hideModal);

            pinForm.addEventListener('submit', async (e) => {
                e.preventDefault(); 
                pinSubmitBtn.disabled = true;
                pinSubmitBtn.querySelector('.btn-text').classList.add('hidden');
                pinSubmitBtn.querySelector('.spinner').classList.remove('hidden');
                errorContainer.classList.add('hidden');

                try {
                    const response = await fetch('core/process_pu_login.php', {
                        method: 'POST',
                        body: new FormData(pinForm)
                    });
                    if (!response.ok) throw new Error(`Server Error: ${response.status}`);
                    const result = await response.json();

                    if (result.success) {
                        showModal();
                    } else {
                        errorContainer.innerHTML = `<p>${result.message || 'Error'}</p>`;
                        errorContainer.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Fetch Error:', error);
                    errorContainer.innerHTML = `<p><strong>A fatal server error occurred.</strong></p>`;
                } finally {
                    pinSubmitBtn.disabled = false;
                    pinSubmitBtn.querySelector('.btn-text').classList.remove('hidden');
                    pinSubmitBtn.querySelector('.spinner').classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
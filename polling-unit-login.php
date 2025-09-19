<?php
// Start the session just so we can manage it.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- THE CRITICAL FIX ---
// This block completely destroys any existing session (e.g., from a previous user).
// This ensures every visit to the login page starts with a clean slate.
$_SESSION = []; // Unset all session variables.

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy(); // Finally, destroy the session.

// Now, start a brand new, clean session for the new login attempt.
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polling Unit Login - INEC Portal</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Syne & Lexend -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    
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
</head>
<body class="bg-slate-50 font-sans text-slate-700 flex items-center justify-center min-h-screen">

    <div class="bg-white rounded-xl shadow-xl p-8 sm:p-12 w-full max-w-md border border-slate-200">
        <a href="index.php" class="flex justify-center mb-6">
            <img src="assets/images/INEC-Logo.png" alt="INEC Logo" class="h-20 w-auto">
        </a>
        <div class="text-center mb-8">
            <h1 class="font-display text-3xl font-bold text-slate-800">PU Clerk Portal</h1>
            <p class="text-slate-500">Enter your credentials to begin.</p>
        </div>
        
        <div id="error-message" class="hidden bg-red-100 border-l-4 border-inec-red text-red-700 p-4 mb-6" role="alert"></div>

        <!-- Step 1: Credentials Form -->
        <form id="login-form" class="space-y-6">
            <div>
                <label for="pu_code" class="block text-sm font-medium text-slate-700 mb-1">Polling Unit Code</label>
                <input type="text" id="pu_code" name="pu_code" required
                       class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition"
                       placeholder="e.g., 26/08/04/003">
            </div>
            <div>
                <label for="pin" class="block text-sm font-medium text-slate-700 mb-1">Secure PIN</label>
                <input type="password" id="pin" name="pin" required maxlength="4"
                       class="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition"
                       placeholder="&bull;&bull;&bull;&bull;">
            </div>
            <div>
                <button type="submit" id="login-btn"
                        class="w-full bg-inec-green text-white font-semibold py-3 px-6 rounded-md hover:opacity-90 transition shadow-md">
                    Proceed to Verification
                </button>
            </div>
        </form>
         <div class="text-center mt-6">
            <a href="index.php" class="text-sm text-inec-green hover:underline">&larr; Back to Main Site</a>
        </div>
    </div>

    <!-- Step 2: Verification Modal -->
    <div id="verification-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md transform transition-all duration-300 scale-95 opacity-0">
            <div class="text-center">
                <h2 class="font-display font-bold text-2xl text-slate-800 mb-2">Two-Factor Authentication</h2>
                <p class="text-slate-600 mb-6">For your security, please enter the last 4 digits of your registered phone number. </p>
            </div>
            <div id="modal-error-message" class="hidden bg-red-100 border-l-4 border-inec-red text-red-700 p-4 mb-6" role="alert"></div>
            <form id="verification-form">
                <div>
                    <label for="phone_digits" class="sr-only">Last 4 digits of phone number</label>
                    <input type="text" id="phone_digits" name="phone_digits" required maxlength="4" pattern="\d{4}"
                           class="w-full p-4 text-center text-2xl tracking-[1em] font-mono border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green transition"
                           placeholder="----">
                </div>
                <div class="mt-6 flex justify-center gap-4">
                     <button id="cancel-verification-btn" type="button" class="bg-slate-200 font-semibold w-full py-3 rounded-lg hover:bg-slate-300 transition">Cancel</button>
                    <button id="verify-btn" type="submit" class="bg-inec-green text-white font-semibold w-full py-3 rounded-lg hover:opacity-90 transition">Verify & Sign In</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>

</body>
</html>
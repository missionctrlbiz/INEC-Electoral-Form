<?php
// Start the session just so we can manage it.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// This block completely destroys any existing session.
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
// Now, start a brand new, clean session for the new login attempt.
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polling Unit Login - INEC Portal</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/webp" href="assets/images/favicon.webp">
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
                    Sign In
                </button>
            </div>
        </form>
         <div class="text-center mt-6">
            <a href="index.php" class="text-sm text-inec-green hover:underline">&larr; Back to Main Site</a>
        </div>
    </div>

    <!-- The verification modal has been removed from here -->
    
    <script src="assets/js/main.js"></script>

</body>
</html>
<?php
// STEP 1: Start or resume the current session.
// This is necessary to access and manage the session.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// STEP 2: Unset all of the session variables.
// This clears all data stored in the session for this user.
$_SESSION = [];

// STEP 3: Destroy the session cookie on the client's browser.
// This is a critical security step. It tells the browser to invalidate the cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// STEP 4: Finally, destroy the session data on the server.
session_destroy();

// The user is now fully and securely logged out.
// The rest of the page is the confirmation message.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signed Out - INEC Portal</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Corrected Favicon Path -->
    <link rel="icon" type="image/webp" href="../assets/images/favicon.webp">
    
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
</head>
<body class="bg-slate-50 font-sans text-slate-700">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md text-center">
            <!-- Corrected Logo Path -->
            <img src="../assets/images/INEC-Logo.png" alt="INEC Logo" class="mx-auto h-20 w-auto mb-8">
            
            <div class="bg-white p-8 md:p-10 rounded-xl shadow-lg border border-slate-200">
                <!-- Success Icon -->
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <i class="bi bi-check2-circle text-6xl text-green-600"></i>
                </div>
                
                <!-- Message -->
                <h1 class="font-display font-bold text-3xl text-slate-900">Successfully Signed Out</h1>
                <p class="mt-2 text-slate-600 mb-8">You have been securely logged out of the portal.</p>
                
                <!-- Button to return home -->
                <a href="../index.php" class="bg-inec-green font-semibold inline-block w-full px-8 py-3 rounded-md text-lg text-white hover:opacity-90 transition-opacity shadow-lg">
                    Return to Home Page
                </a>
            </div>
            
            <!-- Dynamic Copyright Year -->
            <p class="text-center text-slate-500 text-sm mt-8">&copy; <?php echo date('Y'); ?> Electoral Commission. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
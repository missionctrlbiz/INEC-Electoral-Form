<?php
// STEP 1: START THE SESSION TO ACCESS IT
session_start();

// STEP 2: CLEAR ALL SESSION DATA
$_SESSION = [];

// STEP 3: DESTROY THE SESSION
session_destroy();

// After this PHP block, the user is officially logged out.
// The HTML below will now be sent to the browser.
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Logging Out... - INEC Admin Portal</title>
        <!-- IMPORTANT: This meta tag will automatically redirect the user after 3 seconds -->
        <meta http-equiv="disable-refresh" content="3;url=login.php">
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
    </head>
    <body class="bg-slate-50 font-sans text-slate-700">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="w-full max-w-md text-center">
                <!-- Logo -->
                <img src="../INEC-Logo.png" alt="INEC Logo" class="mx-auto h-20 w-auto mb-8">
                <div class="bg-white p-8 rounded-xl shadow-lg border border-slate-200">
                    <!-- Spinner Icon -->
                    <div class="text-5xl text-inec-green animate-spin mb-4"><i class="bi bi-arrow-clockwise"></i>
                    </div>
                    <!-- Message -->
                    <h1 class="font-display font-bold text-3xl text-slate-900">Successfully Signed Out</h1>
                    <p class="mt-2 text-slate-600">You will be redirected to the login page shortly.</p>
                </div>
                <p class="text-center text-slate-500 text-sm mt-8">&copy; 2025 Electoral Commission. All Rights Reserved.</p>
            </div>
        </div>
    </body>
</html>
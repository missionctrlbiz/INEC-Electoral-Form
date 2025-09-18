<?php
// This must be the very first thing on the page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If an admin is already logged in, redirect them to their dashboard.
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - INEC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/webp" href="../assets/images/favicon.webp">
    <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['Lexend', 'sans-serif'], display: ['Syne', 'sans-serif'] }, colors: { 'inec-green': '#006A4E', 'inec-red': '#D40028' } } }
    }
    </script>
</head>
<body class="bg-slate-50 font-sans text-slate-700">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                <img src="../assets/images/INEC-Admin-Logo.png" alt="INEC Logo" class="mx-auto h-20 w-auto">
                <h1 class="mt-6 font-display font-bold text-3xl text-slate-900">Admin Portal Sign In</h1>
                <p class="mt-2 text-slate-600">Please enter your credentials to access the dashboard.</p>
            </div>
            
            <div class="bg-white p-8 rounded-xl shadow-lg border border-slate-200">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                        <strong class="font-bold">Error:</strong>
                        <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <form action="../core/process_admin_login.php" method="POST" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="admin@example.com">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="••••••••">
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-inec-green focus:ring-inec-green">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-inec-green hover:text-green-800">Forgot your password?</a>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-inec-green py-3 px-4 text-lg font-semibold text-white hover:opacity-90 transition-all">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>
             <p class="text-center text-slate-500 text-sm">&copy; 2025 Electoral Commission. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
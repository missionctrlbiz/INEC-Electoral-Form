<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Submission Successful - INEC Portal</title>
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
            // Defining the official INEC colors
            'inec-green': '#006A4E', 
            'inec-red': '#D40028',
          }
        }
      }
    }
    </script>
    </head>
    <body class="bg-slate-50 font-sans text-slate-700 flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-6 py-3"><a href="index.php" class="flex items-center"> <img src="INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto"> </a>
            </div>
        </header>
        <!-- Main Content: Centered Confirmation -->
        <main class="flex-grow flex items-center justify-center py-12 px-6">
            <div class="w-full max-w-lg bg-white p-8 md:p-12 rounded-xl shadow-lg border border-slate-200 text-center">
                <!-- Success Icon -->
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5"><i class="bi bi-check2-circle text-6xl text-green-600"></i>
                </div>
                <!-- Headline -->
                <h1 class="font-display font-bold text-4xl text-slate-900">Submission Successful!</h1>
                <!-- Description -->
                <p class="text-slate-600 mt-3 mb-8">The polling unit result has been successfully recorded in the system. Thank you for your service.</p>
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4"><a href="data-entry.php" class="flex-1 bg-inec-green font-semibold inline-block px-6 py-3 rounded-lg text-white hover:opacity-90 transition-opacity shadow"> <i class="bi bi-plus-circle mr-2"></i>Add New Entry </a><a href="index.php" class="flex-1 bg-slate-600 font-semibold inline-block px-6 py-3 rounded-lg text-white hover:bg-slate-700 transition-colors shadow"> <i class="bi bi-box-arrow-right mr-2"></i>Sign Out </a>
                </div>
            </div>
        </main>
        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto">
            <div class="container mx-auto px-6 py-6">
                <p class="text-center text-slate-500 text-sm">&copy; 2025 Electoral Commission. All Rights Reserved.</p>
            </div>
        </footer>
    </body>
</html>
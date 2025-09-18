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
        <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;7-800&display=swap" rel="stylesheet">
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
                <!-- The form will submit to data-entry.php -->
                <form action="thank-you.php" method="POST" class="space-y-6">
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
                        <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-inec-green py-3 px-4 text-lg font-semibold text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-inec-green focus:ring-offset-2 transition-all">
                            Proceed to Data Entry<span class="absolute right-0 inset-y-0 flex items-center pr-4"> <i class="bi bi-arrow-right-circle-fill text-white"></i> </span>
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
    </body>
</html>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Details Confirmed - INEC Portal</title>
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
    <body class="bg-slate-50 font-sans text-slate-700">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-6 py-3"><a href="index.php" class="flex items-center"> <img src="INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto"> </a>
            </div>
        </header>
        <!-- Main Content Area (for centering the modal) -->
        <main class="flex items-center justify-center min-h-[80vh] px-4">
            <!-- Animated Success Modal -->
            <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
                <div id="modal-content" class="bg-white rounded-xl shadow-2xl p-8 text-center w-full max-w-md transform transition-all duration-300 opacity-0 scale-95">
                    <!-- Success Icon -->
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="bi bi-check2-circle text-5xl text-green-600"></i>
                    </div>
                    <!-- Headline -->
                    <h1 class="font-display font-bold text-3xl text-slate-900">Details Confirmed!</h1>
                    <!-- Description -->
                    <p class="text-slate-600 mt-2 mb-6">Your details have been securely saved. You may now proceed to the main data entry form.</p>
                    <!-- User Data Confirmation -->
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-left space-y-2 mb-8">
                        <div>
                            <p class="text-xs text-slate-500">Full Name</p>
                            <p class="font-semibold text-slate-800"> <!-- PHP: echo htmlspecialchars($userName); -->
                            John Doe </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Email Address</p>
                            <p class="font-semibold text-slate-800"> <!-- PHP: echo htmlspecialchars($userEmail); -->
                            you@example.com </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Phone Number</p>
                            <p class="font-semibold text-slate-800"> <!-- PHP: echo htmlspecialchars($userPhone); -->
                           08012345678 </p>
                        </div>
                    </div>
                    <!-- Call to Action --><a href="data-entry.php" class="group relative flex w-full justify-center rounded-md border border-transparent bg-inec-green py-3 px-4 text-lg font-semibold text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-inec-green focus:ring-offset-2 transition-all">
                    Proceed to Data Entry <span class="absolute right-0 inset-y-0 flex items-center pr-4"> <i class="bi bi-arrow-right-circle text-white"></i> </span> </a>
                </div>
            </div>
        </main>
        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto">
            <div class="container mx-auto px-6 py-6">
                <p class="text-center text-slate-500 text-sm">&copy; 2025 Electoral Commission. All Rights Reserved.</p>
            </div>
        </footer>
        <script>
        // Simple script to trigger the modal animation on page load
        window.addEventListener('load', () => {
            const modal = document.getElementById('success-modal');
            const modalContent = document.getElementById('modal-content');

            // Use a small timeout to allow the browser to render the initial state before transitioning
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('opacity-0', 'scale-95');
            }, 100); // 100ms delay
        });
    </script>
    </body>
</html>
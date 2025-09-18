<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Polling Unit Login - INEC Portal</title>
        <!-- FAVICON LINK -->
        <link rel="icon" type="image/webp" href="favicon.webp">
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
          fontFamily: { sans: ['Lexend', 'sans-serif'], display: ['Syne', 'sans-serif'] },
          colors: { 'inec-green': '#006A4E', 'inec-red': '#D40028' }
        }
      }
    }
    </script>
    </head>
    <body class="bg-slate-50 font-sans text-slate-700">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center"><a href="index.php"><img src="INEC-Logo.png" alt="INEC Logo" class="mx-auto h-20 w-auto"></a>
                    <h1 class="mt-6 font-display font-bold text-3xl text-slate-900">Polling Unit Login</h1>
                    <p class="mt-2 text-slate-600">Please authenticate to begin result submission.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-lg border border-slate-200">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                            <button class="tab-btn active shrink-0 border-b-2 border-inec-green px-1 pb-4 text-sm font-medium text-inec-green" data-tab="pu-code">
                                PU Code & PIN
</button>
                            <button class="tab-btn shrink-0 border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700" data-tab="otp">
                                OTP
</button>
                        </nav>
                    </div>
                    <!-- Tab Content -->
                    <div class="mt-6">
                        <!-- PU Code & PIN Form (Visible by default) -->
                        <div id="pu-code-content" class="tab-content">
                            <form action="data-entry.php" method="POST" class="space-y-6">
                                <div>
                                    <label for="pu_code" class="block text-sm font-medium text-gray-700 mb-1">Polling Unit (PU) Code</label>
                                    <input id="pu_code" name="pu_code" type="text" required class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="e.g., 26/08/04/001">
                                </div>
                                <div>
                                    <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">PIN</label>
                                    <input id="pin" name="pin" type="password" required class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="••••">
                                </div>
                                <div>
                                    <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-inec-green py-3 px-4 text-lg font-semibold text-white hover:opacity-90 transition-all">
                                        Login
</button>
                                </div>
                            </form>
                        </div>
                        <!-- OTP Form (Hidden by default) -->
                        <div id="otp-content" class="tab-content hidden">
                            <form action="data-entry.php" method="POST" class="space-y-6">
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Registered Phone Number</label>
                                    <input id="phone_number" name="phone_number" type="tel" required class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="08012345678">
                                </div>
                                <div>
                                    <button type="button" class="group relative flex w-full justify-center rounded-md border border-transparent bg-slate-600 py-3 px-4 text-md font-semibold text-white hover:bg-slate-700 transition-all">
                                        Send OTP
</button>
                                </div>
                                <div>
                                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">One-Time Password (OTP)</label>
                                    <input id="otp" name="otp" type="text" required class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-inec-green focus:ring-inec-green" placeholder="Enter OTP received">
                                </div>
                                <div>
                                    <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-inec-green py-3 px-4 text-lg font-semibold text-white hover:opacity-90 transition-all">
                                        Login with OTP
</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <p class="text-center text-slate-500 text-sm mt-8">&copy; 2025 Electoral Commission. All Rights Reserved.</p>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabId = button.getAttribute('data-tab');

                    // Update button styles
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'border-inec-green', 'text-inec-green');
                        btn.classList.add('border-transparent', 'text-gray-500', 'hover:border-gray-300', 'hover:text-gray-700');
                    });
                    button.classList.add('active', 'border-inec-green', 'text-inec-green');
                    button.classList.remove('border-transparent', 'text-gray-500');

                    // Show/hide content
                    tabContents.forEach(content => {
                        if (content.id === `${tabId}-content`) {
                            content.classList.remove('hidden');
                        } else {
                            content.classList.add('hidden');
                        }
                    });
                });
            });
        });
    </script>
    </body>
</html>
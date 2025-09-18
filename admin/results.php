<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detailed Results - INEC Admin Portal</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="icon" type="image/webp" href="favicon.webp">
        <link rel="icon" type="image/webp" href="favicon.webp">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        .sidebar .nav-link.active { @apply bg-inec-green text-white; }
    </style>
    </head>
    <body class="bg-slate-100 font-sans">
        <div class="flex min-h-screen">
            <!-- Sidebar Navigation -->
            <aside class="w-64 bg-slate-800 text-white flex flex-col">
                <div class="p-6 text-center border-b border-slate-700">
                    <a href="dashboard.php"><img src="../assets/images/INEC-Admin-Logo.png" alt="INEC Logo" class="h-14 w-auto mx-auto"></a>
                </div>
                <nav class="flex-1 px-4 py-4"><a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors" href="dashboard.php"><i class="bi bi-speedometer2 mr-3 text-lg"></i> Dashboard</a><a class="nav-link active flex items-center p-3 rounded-lg font-semibold" href="results.php"><i class="bi bi-table mr-3 text-lg"></i> Detailed Results</a><a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="users.php"><i class="bi bi-people mr-3 text-lg"></i> User Management</a>
                </nav>
                <div class="p-4 border-t border-slate-700">
                    <a class="flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors" href="logout.php"><i class="bi bi-box-arrow-left mr-3 text-lg"></i> Logout</a>
                </div>
            </aside>
            <!-- Main Content -->
            <main class="flex-1 p-8">
                <header class="flex justify-between items-center mb-8">
                    <h1 class="font-display text-3xl font-bold text-gray-800">All Submitted Results</h1><a href="#" class="bg-inec-green text-white font-semibold py-2 px-4 rounded-md hover:opacity-90 transition"> <i class="bi bi-download mr-2"></i> Export to CSV </a>
                </header>
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <select class="w-full p-2 border border-gray-300 rounded-md bg-slate-50">
                            <option>Filter by State</option>
                        </select>
                        <select class="w-full p-2 border border-gray-300 rounded-md bg-slate-50">
                            <option>Filter by LGA</option>
                        </select>
                        <select class="w-full p-2 border border-gray-300 rounded-md bg-slate-50">
                            <option>Filter by Status</option>
                        </select>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th class="px-6 py-3">State</th>
                                <th class="px-6 py-3">LGA</th>
                                <th class="px-6 py-3">Polling Unit</th>
                                <th class="px-6 py-3">APC</th>
                                <th class="px-6 py-3">LP</th>
                                <th class="px-6 py-3">PDP</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">Lagos</td>
                                <td class="px-6 py-4">Lagos Mainland</td>
                                <td class="px-6 py-4 font-medium">FAGBAYI ST.</td>
                                <td class="px-6 py-4">81</td>
                                <td class="px-6 py-4">12</td>
                                <td class="px-6 py-4">17</td>
                                <td class="px-6 py-4"><span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Verified</span></td>
                                <td class="px-6 py-4 space-x-2 text-center">
                                    <button class="text-blue-600 hover:text-blue-800 p-1">
                                        <i class="bi bi-eye-fill text-lg"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700 p-1">
                                        <i class="bi bi-pencil-fill text-lg"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-800 p-1">
                                        <i class="bi bi-flag-fill text-lg"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">Kano</td>
                                <td class="px-6 py-4">Kano Municipal</td>
                                <td class="px-6 py-4 font-medium">Town Hall 003</td>
                                <td class="px-6 py-4">102</td>
                                <td class="px-6 py-4">5</td>
                                <td class="px-6 py-4">25</td>
                                <td class="px-6 py-4"><span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Pending</span></td>
                                <td class="px-6 py-4 space-x-2 text-center">
                                    <button class="text-blue-600 hover:text-blue-800 p-1">
                                        <i class="bi bi-eye-fill text-lg"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700 p-1">
                                        <i class="bi bi-pencil-fill text-lg"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-800 p-1">
                                        <i class="bi bi-flag-fill text-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </body>
</html>
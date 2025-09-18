<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard - INEC Admin Portal</title>
        <script src="https://cdn.tailwindcss.com"></script>
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
                <div class="p-6 text-center border-b border-slate-700"><a href="dashboard.php"><img src="../INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto mx-auto"></a>
                </div>
                <nav class="flex-1 px-4 py-4"><a class="nav-link active flex items-center p-3 rounded-lg font-semibold" href="dashboard.php"><i class="bi bi-speedometer2 mr-3 text-lg"></i> Dashboard</a><a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="results.php"><i class="bi bi-table mr-3 text-lg"></i> Detailed Results</a><a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="users.php"><i class="bi bi-people mr-3 text-lg"></i> User Management</a>
                </nav>
                <div class="p-4 border-t border-slate-700"><a class="flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors" href="logout.php"><i class="bi bi-box-arrow-left mr-3 text-lg"></i> Logout</a>
                </div>
            </aside>
            <!-- Main Content -->
            <main class="flex-1 p-8">
                <header class="flex justify-between items-center mb-8">
                    <h1 class="font-display text-3xl font-bold text-gray-800">Dashboard</h1>
                    <div class="flex items-center gap-4"><span class="font-semibold text-slate-600">Welcome, Admin!</span>
                        <img src="https://via.placeholder.com/40" class="rounded-full" alt="User Avatar">
                    </div>
                </header>
                <!-- KPI Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-inec-green">
                        <h4 class="text-gray-500 font-semibold">Total Results Submitted</h4>
                        <p class="text-3xl font-bold mt-1">1,402</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
                        <h4 class="text-gray-500 font-semibold">Accredited Voters</h4>
                        <p class="text-3xl font-bold mt-1">250,678</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
                        <h4 class="text-gray-500 font-semibold">Pending Review</h4>
                        <p class="text-3xl font-bold mt-1">15</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-inec-red">
                        <h4 class="text-gray-500 font-semibold">Flagged for Review</h4>
                        <p class="text-3xl font-bold mt-1">3</p>
                    </div>
                </div>
                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-8">
                    <div class="lg:col-span-3 bg-white rounded-lg shadow p-5">
                        <h3 class="font-display font-bold text-lg mb-4">Results Submitted Over Time</h3>
                        <div class="bg-slate-100 h-64 flex items-center justify-center text-gray-500 rounded">Line Chart Placeholder</div>
                    </div>
                    <div class="lg:col-span-2 bg-white rounded-lg shadow p-5">
                        <h3 class="font-display font-bold text-lg mb-4">Vote Share Distribution</h3>
                        <div class="bg-slate-100 h-64 flex items-center justify-center text-gray-500 rounded">Doughnut Chart Placeholder</div>
                    </div>
                </div>
                <!-- Recent Submissions Table -->
                <div class="bg-white rounded-lg shadow mt-8">
                    <div class="p-5 border-b border-slate-200">
                        <h3 class="font-display font-bold text-lg">Recent Submissions</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3">Polling Unit</th>
                                    <th class="px-6 py-3">LGA</th>
                                    <th class="px-6 py-3">State</th>
                                    <th class="px-6 py-3">Time</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium">Town Hall 003</td>
                                    <td class="px-6 py-4">Kano Municipal</td>
                                    <td class="px-6 py-4">Kano</td>
                                    <td class="px-6 py-4">2 mins ago</td>
                                    <td class="px-6 py-4"><span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Pending</span></td>
                                </tr>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium">FAGBAYI ST. (BY HOUSE 9)</td>
                                    <td class="px-6 py-4">Lagos Mainland</td>
                                    <td class="px-6 py-4">Lagos</td>
                                    <td class="px-6 py-4">5 mins ago</td>
                                    <td class="px-6 py-4"><span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Verified</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
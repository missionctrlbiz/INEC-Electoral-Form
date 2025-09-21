<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INEC Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/webp" href="../assets/images/favicon.webp">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['Lexend', 'sans-serif'], display: ['Syne', 'sans-serif'], }, colors: { 'inec-green': '#006A4E', 'inec-red': '#D40028', } } }
    }
    </script>
    <style>
        .sidebar .nav-link.active { @apply bg-inec-green text-white; }
        .sidebar .nav-link-sub.active { @apply bg-inec-green/20 text-white; }
    </style>
</head>
<body class="bg-slate-100 font-sans">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-800 text-white flex flex-col flex-shrink-0">
            <div class="p-6 text-center border-b border-slate-700">
                <a href="dashboard.php"><img src="../assets/images/INEC-Admin-Logo.png" alt="INEC Admin Logo" class="h-14 w-auto mx-auto"></a>
            </div>
            <nav class="flex-1 px-4 py-4">
                <a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="dashboard.php"><i class="bi bi-speedometer2 mr-3 text-lg"></i> Dashboard</a>
                
                <!-- START: RESTRUCTURED HIERARCHICAL MENU -->
                <div>
                    <button id="results-menu-toggle" class="nav-link w-full flex items-center justify-between p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2">
                        <span class="flex items-center"><i class="bi bi-bar-chart-line-fill mr-3 text-lg"></i> Result Summaries</span>
                        <i id="results-menu-chevron" class="bi bi-chevron-down transition-transform"></i>
                    </button>
                    <div id="results-submenu" class="pl-4 mt-1 hidden">
                        <!-- Restored National Summary (Drill-Down) -->
                        <a class="nav-link-sub flex items-center p-2 rounded-lg hover:bg-slate-700 transition-colors text-sm" href="national-summary.php"><i class="bi bi-globe mr-3"></i> National Level</a>
                        <!-- Dedicated Summary Pages -->
                        <a class="nav-link-sub flex items-center p-2 rounded-lg hover:bg-slate-700 transition-colors text-sm" href="state-summary.php"><i class="bi bi-map mr-3"></i> State Level</a>
                        <a class="nav-link-sub flex items-center p-2 rounded-lg hover:bg-slate-700 transition-colors text-sm" href="lga-summary.php"><i class="bi bi-pin-map mr-3"></i> LGA Level</a>
                        <a class="nav-link-sub flex items-center p-2 rounded-lg hover:bg-slate-700 transition-colors text-sm" href="ward-summary.php"><i class="bi bi-signpost-split mr-3"></i> Ward Level</a>
                    </div>
                </div>
                <!-- END: RESTRUCTURED HIERARCHICAL MENU -->
                
                <a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="polling-unit-results.php"><i class="bi bi-table mr-3 text-lg"></i> All Polling Units</a>
                <a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="users.php"><i class="bi bi-people mr-3 text-lg"></i> User Management</a>
                <a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="parties.php"><i class="bi bi-flag mr-3 text-lg"></i> Political Parties</a>
            </nav>
            <div class="p-4 border-t border-slate-700">
                <a class="flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors" href="logout.php"><i class="bi bi-box-arrow-left mr-3 text-lg"></i> Logout</a>
            </div>
        </aside>
        <div class="flex-1 flex flex-col">
            <div class="p-6"> 

    <script>
        const resultsMenuToggle = document.getElementById('results-menu-toggle');
        const resultsSubmenu = document.getElementById('results-submenu');
        const resultsMenuChevron = document.getElementById('results-menu-chevron');

        if (resultsMenuToggle) {
            resultsMenuToggle.addEventListener('click', () => {
                resultsSubmenu.classList.toggle('hidden');
                resultsMenuChevron.classList.toggle('rotate-180');
            });
        }
        
        const currentPage = window.location.pathname.split('/').pop();
        const resultsPages = ['state-summary.php', 'lga-summary.php', 'ward-summary.php', 'national-summary.php'];
        if (resultsPages.includes(currentPage)) {
            resultsSubmenu.classList.remove('hidden');
            resultsMenuChevron.classList.add('rotate-180');
            resultsMenuToggle.classList.add('active');
            
            document.querySelectorAll('.nav-link-sub').forEach(link => {
                if (link.href.includes(currentPage)) {
                    link.classList.add('active');
                }
            });
        } else if (currentPage === 'polling-unit-results.php') {
             document.querySelector('a[href="polling-unit-results.php"]').classList.add('active');
        }
    </script>
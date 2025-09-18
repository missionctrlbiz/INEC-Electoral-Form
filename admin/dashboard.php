<?php require_once '../includes/admin_header.php'; ?>

<header class="flex justify-between items-center mb-8">
    <h1 class="font-display text-3xl font-bold text-gray-800">Dashboard</h1>
    <div class="flex items-center gap-4">
        <span class="font-semibold text-slate-600">Welcome, Admin!</span>
        <!-- Updated path for the avatar placeholder -->
        <img src="../assets/images/favicon.webp" class="w-10 h-10 rounded-full" alt="User Avatar">
    </div>
</header>

<!-- KPI Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-inec-green"><h4 class="text-gray-500 font-semibold">Total Results Submitted</h4><p class="text-3xl font-bold mt-1">1,402</p></div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500"><h4 class="text-gray-500 font-semibold">Accredited Voters</h4><p class="text-3xl font-bold mt-1">250,678</p></div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500"><h4 class="text-gray-500 font-semibold">Pending Review</h4><p class="text-3xl font-bold mt-1">15</p></div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-inec-red"><h4 class="text-gray-500 font-semibold">Flagged for Review</h4><p class="text-3xl font-bold mt-1">3</p></div>
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
                    <th class="px-6 py-3">Polling Unit</th><th class="px-6 py-3">LGA</th><th class="px-6 py-3">State</th><th class="px-6 py-3">Time</th><th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">Town Hall 003</td><td class="px-6 py-4">Kano Municipal</td><td class="px-6 py-4">Kano</td><td class="px-6 py-4">2 mins ago</td>
                    <td class="px-6 py-4"><span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Pending</span></td>
                </tr>
                 <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">FAGBAYI ST. (BY HOUSE 9)</td><td class="px-6 py-4">Lagos Mainland</td><td class="px-6 py-4">Lagos</td><td class="px-6 py-4">5 mins ago</td>
                    <td class="px-6 py-4"><span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Verified</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- This script makes sure the correct sidebar link is highlighted -->
<script>
    document.querySelector('.nav-link[href="dashboard.php"]').classList.add('active');
</script>

<?php require_once '../includes/admin_footer.php'; ?>
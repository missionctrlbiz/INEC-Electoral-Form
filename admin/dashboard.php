<?php
require_once '../core/db_connect.php';
require_admin(); // Protect the page

// --- DATA FETCHING FOR KPI CARDS ---
$total_results_query = $conn->query("SELECT COUNT(id) as total FROM results");
$total_results = $total_results_query->fetch_assoc()['total'] ?? 0;

$total_voters_query = $conn->query("SELECT SUM(accredited_voters) as total FROM results");
$total_voters = $total_voters_query->fetch_assoc()['total'] ?? 0;

$pending_results_query = $conn->query("SELECT COUNT(id) as total FROM results WHERE status = 'pending'");
$pending_results = $pending_results_query->fetch_assoc()['total'] ?? 0;

$flagged_results_query = $conn->query("SELECT COUNT(id) as total FROM results WHERE status = 'flagged'");
$flagged_results = $flagged_results_query->fetch_assoc()['total'] ?? 0;

// --- DATA FETCHING FOR CHARTS ---
$party_scores_query = $conn->query("
    SELECT pp.acronym, SUM(ps.score) as total_score
    FROM party_scores ps
    JOIN political_parties pp ON ps.party_id = pp.id
    GROUP BY pp.acronym
    ORDER BY total_score DESC
");
$party_data = [];
if ($party_scores_query) {
    while($row = $party_scores_query->fetch_assoc()) {
        $party_data[] = $row;
    }
}
$party_data_json = json_encode($party_data);

// --- DATA FETCHING FOR RECENT SUBMISSIONS TABLE (MODIFIED) ---
// Fetch 11 results to determine if we need a "show more" and "view all" state.
$recent_submissions_query = $conn->query("
    SELECT pu.name as pu_name, l.name as lga_name, s.name as state_name, r.status
    FROM results r
    JOIN polling_units pu ON r.polling_unit_id = pu.id
    JOIN wards w ON pu.ward_id = w.id
    JOIN lgas l ON w.lga_id = l.id
    JOIN states s ON l.state_id = s.id
    ORDER BY r.submitted_at DESC
    LIMIT 11
");

require_once '../includes/admin_header.php'; 
?>
<main class="flex-1 p-6 bg-slate-100">
    <header class="flex justify-between items-center mb-8">
        <h1 class="font-display text-3xl font-bold text-slate-900">Dashboard</h1>
        <div class="flex items-center gap-4">
            <span class="font-semibold text-slate-600">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</span>
            <img src="../assets/images/favicon.webp" class="w-10 h-10 rounded-full" alt="User Avatar">
        </div>
    </header>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-inec-green"><h4 class="text-gray-500 font-semibold">Total Results Submitted</h4><p class="text-3xl font-bold mt-1 text-slate-800"><?php echo number_format($total_results); ?></p></div>
        <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-500"><h4 class="text-gray-500 font-semibold">Accredited Voters</h4><p class="text-3xl font-bold mt-1 text-slate-800"><?php echo number_format($total_voters); ?></p></div>
        <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-yellow-500"><h4 class="text-gray-500 font-semibold">Pending Review</h4><p class="text-3xl font-bold mt-1 text-slate-800"><?php echo number_format($pending_results); ?></p></div>
        <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-inec-red"><h4 class="text-gray-500 font-semibold">Flagged for Review</h4><p class="text-3xl font-bold mt-1 text-slate-800"><?php echo number_format($flagged_results); ?></p></div>
    </div>

    <!-- Charts Grid -->
    <div id="charts-container" class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-8" data-chart-data='<?php echo htmlspecialchars($party_data_json, ENT_QUOTES, 'UTF-8'); ?>'>
        <div class="lg:col-span-3 bg-white rounded-xl shadow-lg p-5">
            <h3 class="font-display font-bold text-lg mb-4 text-slate-800">Overall Party Results</h3>
            <div class="relative h-64"><canvas id="partyResultsChart"></canvas></div>
        </div>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-5">
            <h3 class="font-display font-bold text-lg mb-4 text-slate-800">Vote Share Distribution</h3>
            <div class="relative h-64"><canvas id="voteShareChart"></canvas></div>
        </div>
    </div>

    <!-- Recent Submissions Table (MODIFIED) -->
    <div class="bg-white rounded-xl shadow-lg mt-8">
        <div class="p-5 border-b border-slate-200"><h3 class="font-display font-bold text-lg text-slate-800">Recent Submissions</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-100"><tr><th class="px-6 py-3">Polling Unit</th><th class="px-6 py-3">LGA</th><th class="px-6 py-3">State</th><th class="px-6 py-3">Status</th></tr></thead>
                <tbody>
                    <?php
                    $submissions = $recent_submissions_query ? $recent_submissions_query->fetch_all(MYSQLI_ASSOC) : [];
                    $total_fetched = count($submissions);

                    if ($total_fetched > 0):
                        for ($i = 0; $i < $total_fetched && $i < 10; $i++):
                            $row = $submissions[$i];
                            $hidden_class = ($i >= 5) ? 'hidden extra-row' : '';
                    ?>
                            <tr class="bg-white border-b hover:bg-slate-50 <?php echo $hidden_class; ?>">
                                <td class="px-6 py-4 font-bold text-slate-800"><?php echo htmlspecialchars($row['pu_name']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['lga_name']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['state_name']); ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                        if ($row['status'] === 'verified') echo '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Verified</span>';
                                        elseif ($row['status'] === 'flagged') echo '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Flagged</span>';
                                        else echo '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Pending</span>';
                                    ?>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    <?php else: ?>
                        <tr class="bg-white border-b"><td colspan="4" class="px-6 py-4 text-center text-slate-500">No recent submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if ($total_fetched > 5): ?>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-center p-4 bg-slate-50/50">
                            <div id="table-footer-action">
                                <button id="show-more-btn" class="text-inec-green font-semibold hover:underline transition">
                                    <i class="bi bi-chevron-down mr-1"></i> Show 5 More
                                </button>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar active link highlighter
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.href.includes('dashboard.php')) link.classList.add('active');
        else link.classList.remove('active');
    });

    // Chart rendering logic... (This part is unchanged and correct)
    const chartsContainer = document.getElementById('charts-container');
    if (chartsContainer) {
        // Your existing chart logic here...
        const barCanvas = document.getElementById('partyResultsChart');
        const doughnutCanvas = document.getElementById('voteShareChart');
        if (typeof Chart !== 'undefined' && barCanvas && doughnutCanvas) {
            try {
                const partyData = JSON.parse(chartsContainer.dataset.chartData);
                if (!partyData || partyData.length === 0) {
                     barCanvas.parentElement.innerHTML = '<p class="text-slate-500 text-center flex items-center justify-center h-full">No party score data available.</p>';
                     doughnutCanvas.parentElement.innerHTML = '<p class="text-slate-500 text-center flex items-center justify-center h-full">No party score data available.</p>';
                } else {
                    const labels = partyData.map(d => d.acronym);
                    const scores = partyData.map(d => d.total_score);
                    new Chart(barCanvas.getContext('2d'), { type: 'bar', data: { labels: labels, datasets: [{ label: 'Total Votes', data: scores, backgroundColor: 'rgba(0, 106, 78, 0.7)', borderColor: 'rgba(0, 106, 78, 1)', borderWidth: 1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
                    new Chart(doughnutCanvas.getContext('2d'), { type: 'doughnut', data: { labels: labels, datasets: [{ label: 'Vote Share', data: scores, backgroundColor: ['#006A4E', '#D40028', '#3B82F6', '#F59E0B', '#6D28D9'], hoverOffset: 4 }] }, options: { responsive: true, maintainAspectRatio: false } });
                }
            } catch (e) {
                console.error('Error initializing charts:', e);
            }
        }
    }
    
    // --- START: NEW SCRIPT FOR "SHOW MORE" BUTTON ---
    const showMoreBtn = document.getElementById('show-more-btn');
    if (showMoreBtn) {
        // PHP tells us if there are more than 10 submissions in total (since we fetched 11)
        const hasMoreThanTen = <?php echo ($total_fetched > 10) ? 'true' : 'false'; ?>;

        showMoreBtn.addEventListener('click', () => {
            // Show the hidden rows (6 through 10)
            document.querySelectorAll('.extra-row').forEach(row => {
                row.classList.remove('hidden');
            });

            const footerActionContainer = document.getElementById('table-footer-action');
            let newHtml = '';

            // If there are still more results beyond the 10 we've shown, link to the full analysis page.
            if (hasMoreThanTen) {
                newHtml = `
                    <a href="results-analysis.php" class="bg-inec-green text-white font-semibold py-2 px-5 rounded-lg hover:opacity-90 transition shadow-sm inline-block">
                        View All Results Analysis
                    </a>`;
            } else {
                // If there are no more results, just show a simple message.
                newHtml = `<p class="text-sm text-slate-500">All recent submissions are now showing.</p>`;
            }
            
            footerActionContainer.innerHTML = newHtml;
        });
    }
    // --- END: NEW SCRIPT FOR "SHOW MORE" BUTTON ---
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>
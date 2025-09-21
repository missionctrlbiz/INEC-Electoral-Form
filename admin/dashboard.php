<?php
require_once '../core/db_connect.php';
require_admin(); // Protect the page

// --- DATA FETCHING FOR KPI CARDS ---
$total_results_query = $conn->query("SELECT COUNT(id) as total FROM results");
$total_results = $total_results_query->fetch_assoc()['total'] ?? 0;

$total_voters_query = $conn->query("SELECT SUM(accredited_voters) as total FROM results");
$total_voters = $total_voters_query->fetch_assoc()['total'] ?? 0;

// --- DATA FETCHING FOR CHARTS ---
$party_scores_query = $conn->query("SELECT pp.acronym, SUM(ps.score) as total_score FROM party_scores ps JOIN political_parties pp ON ps.party_id = pp.id GROUP BY pp.acronym ORDER BY total_score DESC");
$party_data = $party_scores_query ? $party_scores_query->fetch_all(MYSQLI_ASSOC) : [];
$party_data_json = json_encode($party_data);

// --- DATA FETCHING FOR RECENT SUBMISSIONS TABLE ---
$recent_submissions_query = $conn->query("
    SELECT 
        r.id as result_id, pu.pu_code, pu.name as pu_name, w.name as ward_name,
        l.name as lga_name, s.name as state_name, r.status, r.result_sheet_path,
        GROUP_CONCAT(CONCAT(pp.acronym, ':', ps.score) ORDER BY pp.acronym SEPARATOR ', ') as scores
    FROM results r
    JOIN polling_units pu ON r.polling_unit_id = pu.id
    JOIN wards w ON pu.ward_id = w.id
    JOIN lgas l ON w.lga_id = l.id
    JOIN states s ON l.state_id = s.id
    LEFT JOIN party_scores ps ON ps.result_id = r.id
    LEFT JOIN political_parties pp ON ps.party_id = pp.id
    GROUP BY r.id
    ORDER BY r.submitted_at DESC
    LIMIT 11
");

require_once '../includes/admin_header.php'; 
?>
<!-- START: ADDED STYLE BLOCK FOR MODAL REFINEMENT -->
<style>
    .modal-backdrop {
        transition: opacity 300ms ease-in-out;
    }
    .modal-content {
        transition: opacity 300ms ease-in-out, transform 300ms ease-in-out;
    }
    .modal-backdrop.open {
        opacity: 1;
        pointer-events: auto;
    }
    .modal-backdrop.open .modal-content {
        transform: scale(1);
        opacity: 1;
    }
</style>
<!-- END: ADDED STYLE BLOCK -->

<main id="main-content" class="flex-1 p-6 bg-slate-100">
    <header class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-slate-900">Dashboard</h1>
            <p class="text-slate-600 mt-1">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</p>
        </div>
        <button id="clear-data-btn" type="button" class="bg-red-100 text-inec-red font-semibold py-2 px-4 rounded-lg hover:bg-red-200 transition shadow-sm border border-red-200">
            <i class="bi bi-trash mr-2"></i> Clear All Demo Data
        </button>
    </header>

    <!-- Success/Error Message Display -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-5 border-l-4 border-inec-green"><h4 class="text-gray-500 font-semibold">Total Results Submitted</h4><p class="text-3xl font-bold mt-1 text-slate-800"><?php echo number_format($total_results); ?></p></div>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-500"><h4 class="text-gray-500 font-semibold">Total Accredited Voters</h4><p class="text-3xl font-bold mt-1 text-slate-800"><?php echo number_format($total_voters); ?></p></div>
    </div>

    <div id="charts-container" class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-8" data-chart-data='<?php echo htmlspecialchars($party_data_json, ENT_QUOTES, 'UTF-8'); ?>'>
        <div class="lg:col-span-3 bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg mb-4 text-slate-800">Overall Party Results</h3><div class="relative h-64"><canvas id="partyResultsChart"></canvas></div></div>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg mb-4 text-slate-800">Vote Share Distribution</h3><div class="relative h-64"><canvas id="voteShareChart"></canvas></div></div>
    </div>

    <div class="bg-white rounded-xl shadow-lg mt-8">
        <div class="p-5 border-b border-slate-200"><h3 class="font-display font-bold text-lg text-slate-800">Recent Submissions</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                    <tr><th class="px-6 py-3">PU Code</th><th class="px-6 py-3">LGA</th><th class="px-6 py-3">State</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-center">Actions</th></tr>
                </thead>
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
                                <td class="px-6 py-4 font-bold text-slate-800">
                                    <a href="#" class="view-btn text-inec-green hover:underline" data-pu-name="<?php echo htmlspecialchars($row['pu_name']); ?>" data-location="<?php echo htmlspecialchars($row['ward_name'] . ', ' . $row['lga_name'] . ', ' . $row['state_name']); ?>" data-scores="<?php echo htmlspecialchars($row['scores'] ?? ''); ?>" data-image-path="../<?php echo htmlspecialchars($row['result_sheet_path']); ?>">
                                        <?php echo htmlspecialchars($row['pu_code']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['lga_name']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['state_name']); ?></td>
                                <td class="px-6 py-4"><span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Transmitted</span></td>
                                <td class="px-6 py-4 text-center">
                                    <button class="view-btn text-blue-600 hover:text-blue-800 p-1" title="View Details" data-pu-name="<?php echo htmlspecialchars($row['pu_name']); ?>" data-location="<?php echo htmlspecialchars($row['ward_name'] . ', ' . $row['lga_name'] . ', ' . $row['state_name']); ?>" data-scores="<?php echo htmlspecialchars($row['scores'] ?? ''); ?>" data-image-path="../<?php echo htmlspecialchars($row['result_sheet_path']); ?>"><i class="bi bi-eye-fill text-lg"></i></button>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    <?php else: ?>
                        <tr class="bg-white border-b"><td colspan="5" class="px-6 py-4 text-center text-slate-500">No recent submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if ($total_fetched > 5): ?>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-center p-4 bg-slate-50/50">
                            <div id="table-footer-action">
                                <button id="show-more-btn" class="text-inec-green font-semibold hover:underline transition"><i class="bi bi-chevron-down mr-1"></i> Show 5 More</button>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</main>

<!-- REFINED VIEW MODAL (LIGHTBOX) -->
<div id="view-modal" class="modal-backdrop fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 opacity-0 pointer-events-none">
    <div id="view-modal-content" class="modal-content bg-slate-50 rounded-xl shadow-2xl w-full max-w-4xl transform scale-95 opacity-0 mx-4 max-h-[90vh] flex flex-col">
        <header class="p-4 border-b bg-white rounded-t-xl flex justify-between items-center flex-shrink-0">
            <div>
                <h2 id="modal-title" class="font-display font-bold text-2xl text-slate-800"></h2>
                <p id="modal-location" class="text-slate-500"></p>
            </div>
            <button id="close-view-modal-btn" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </header>
        <div class="p-6 overflow-y-auto">
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-bold text-lg mb-2 text-slate-700">Submitted Scores</h3>
                    <div id="modal-scores" class="bg-white p-4 rounded-lg border text-sm font-mono"></div>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-2 text-slate-700">Scanned Result Sheet</h3>
                    <div class="border rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center min-h-[200px]">
                        <img id="modal-image" src="" alt="Scanned Result Sheet" class="w-full h-auto object-contain max-h-[50vh]">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="confirm-clear-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl p-8 text-center w-full max-w-md">
        <h2 class="font-display font-bold text-2xl text-slate-800 mb-2">Are you sure?</h2>
        <p class="text-slate-600 mb-8">This will permanently delete all submitted results and party scores. This action cannot be undone.</p>
        <div class="flex justify-center gap-4">
            <button id="cancel-clear-btn" type="button" class="bg-slate-200 font-semibold w-full py-3 rounded-lg hover:bg-slate-300">Cancel</button>
            <form id="clear-data-form" action="../core/clear_data.php" method="POST" class="w-full">
                <button id="confirm-clear-btn" type="submit" class="bg-inec-red text-white font-semibold w-full py-3 rounded-lg hover:opacity-90">Yes, Clear Data</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Chart rendering logic
    const chartsContainer = document.getElementById('charts-container');
    const barCanvas = document.getElementById('partyResultsChart');
    const doughnutCanvas = document.getElementById('voteShareChart');
    if (typeof Chart !== 'undefined' && barCanvas && doughnutCanvas) {
        try {
            const chartDataString = chartsContainer.dataset.chartData;
            if (chartDataString && chartDataString.trim().startsWith('[')) {
                const partyData = JSON.parse(chartDataString);
                if (!partyData || partyData.length === 0 || partyData.reduce((sum, item) => sum + item.total_score, 0) === 0) throw new Error("No data");
                const labels = partyData.map(d => d.acronym);
                const scores = partyData.map(d => d.total_score);
                new Chart(barCanvas.getContext('2d'), { type: 'bar', data: { labels, datasets: [{ label: 'Total Votes', data: scores, backgroundColor: 'rgba(0, 106, 78, 0.7)', borderColor: 'rgba(0, 106, 78, 1)', borderWidth: 1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
                new Chart(doughnutCanvas.getContext('2d'), { type: 'doughnut', data: { labels, datasets: [{ label: 'Vote Share', data: scores, backgroundColor: ['#006A4E', '#D40028', '#3B82F6', '#F59E0B', '#6D28D9'], hoverOffset: 4 }] }, options: { responsive: true, maintainAspectRatio: false } });
            } else { throw new Error("Invalid chart data"); }
        } catch (e) {
            console.error('Chart Error:', e.message);
            barCanvas.parentElement.innerHTML = '<p class="text-slate-500 text-center flex items-center justify-center h-full">No party score data available.</p>';
            doughnutCanvas.parentElement.innerHTML = '<p class="text-slate-500 text-center flex items-center justify-center h-full">No party score data available.</p>';
        }
    }

    // "Show More" button logic
    const showMoreBtn = document.getElementById('show-more-btn');
    if (showMoreBtn) {
        const hasMoreThanTen = <?php echo ($total_fetched > 10) ? 'true' : 'false'; ?>;
        showMoreBtn.addEventListener('click', () => {
            document.querySelectorAll('.extra-row').forEach(row => row.classList.remove('hidden'));
            const footer = document.getElementById('table-footer-action');
            footer.innerHTML = hasMoreThanTen ? `<a href="national-summary.php" class="bg-inec-green text-white font-semibold py-2 px-5 rounded-lg hover:opacity-90 transition shadow-sm inline-block">View All Results Analysis</a>` : `<p class="text-sm text-slate-500">All recent submissions are now showing.</p>`;
        });
    }

    // --- REFINED MODAL & BUTTON LOGIC ---
    const mainContent = document.getElementById('main-content');
    const viewModal = document.getElementById('view-modal');
    const confirmClearModal = document.getElementById('confirm-clear-modal');

    const showModal = (modal) => modal.classList.add('open');
    const hideModal = (modal) => modal.classList.remove('open');
    
    // Close View Modal
    document.getElementById('close-view-modal-btn').addEventListener('click', () => hideModal(viewModal));
    viewModal.addEventListener('click', (e) => { if (e.target === viewModal) hideModal(viewModal) });
    
    // Event Delegation for all main content clicks
    if (mainContent) {
        mainContent.addEventListener('click', (e) => {
            const viewButton = e.target.closest('.view-btn');
            const clearButton = e.target.closest('#clear-data-btn');

            if (viewButton) {
                e.preventDefault();
                document.getElementById('modal-title').textContent = viewButton.dataset.puName;
                document.getElementById('modal-location').textContent = viewButton.dataset.location;
                document.getElementById('modal-image').src = viewButton.dataset.imagePath;
                const scores = viewButton.dataset.scores;
                const scoresHtml = '<ul class="space-y-1">' + (scores && scores.length > 0 ? scores.split(', ').map(s => { const parts = s.split(':'); return `<li class="flex justify-between items-center border-b pb-1"><span>${parts[0] || 'N/A'}</span><span class="font-bold text-slate-800">${parseInt(parts[1] || 0).toLocaleString()}</span></li>`; }).join('') : '<li>No scores recorded.</li>') + '</ul>';
                document.getElementById('modal-scores').innerHTML = scoresHtml;
                showModal(viewModal);
            }

            if (clearButton) {
                e.preventDefault();
                confirmClearModal.classList.remove('hidden');
            }
        });
    }

    // "Clear Data" Modal Cancel Button
    const cancelClearBtn = document.getElementById('cancel-clear-btn');
    if (cancelClearBtn) {
        cancelClearBtn.addEventListener('click', () => {
            confirmClearModal.classList.add('hidden');
        });
    }
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>
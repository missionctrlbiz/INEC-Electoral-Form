<?php
require_once '../core/db_connect.php';
require_admin(); // Protect the page

// --- DATA FETCHING FOR KPI CARDS (SIMPLIFIED) ---
$total_results_query = $conn->query("SELECT COUNT(id) as total FROM results");
$total_results = $total_results_query->fetch_assoc()['total'] ?? 0;

$total_voters_query = $conn->query("SELECT SUM(accredited_voters) as total FROM results");
$total_voters = $total_voters_query->fetch_assoc()['total'] ?? 0;

// --- DATA FETCHING FOR CHARTS ---
$party_scores_query = $conn->query("
    SELECT pp.acronym, SUM(ps.score) as total_score
    FROM party_scores ps
    JOIN political_parties pp ON ps.party_id = pp.id
    GROUP BY pp.acronym
    ORDER BY total_score DESC
");
$party_data = $party_scores_query ? $party_scores_query->fetch_all(MYSQLI_ASSOC) : [];
$party_data_json = json_encode($party_data);

// --- DATA FETCHING FOR RECENT SUBMISSIONS TABLE (UPDATED) ---
// Fetch 11 results and include pu_code, result_id, and data for the modal.
$recent_submissions_query = $conn->query("
    SELECT 
        r.id as result_id,
        pu.pu_code, 
        pu.name as pu_name,
        w.name as ward_name,
        l.name as lga_name, 
        s.name as state_name, 
        r.status,
        r.result_sheet_path,
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
<main class="flex-1 p-6 bg-slate-100">
    <header class="flex justify-between items-center mb-8">
        <h1 class="font-display text-3xl font-bold text-slate-900">Dashboard</h1>
        <div class="flex items-center gap-4">
            <span class="font-semibold text-slate-600">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</span>
        </div>
    </header>

    <!-- KPI Cards Grid (SIMPLIFIED) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-5 border-l-4 border-inec-green"><h4 class="text-gray-500 font-semibold">Total Results Submitted</h4><p class="text-3xl font-bold mt-1 text-slate-800"><?php echo number_format($total_results); ?></p></div>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-500"><h4 class="text-gray-500 font-semibold">Total Accredited Voters</h4><p class="text-3xl font-bold mt-1 text-slate-800"><?php echo number_format($total_voters); ?></p></div>
    </div>

    <!-- Charts Grid -->
    <div id="charts-container" class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-8" data-chart-data='<?php echo htmlspecialchars($party_data_json, ENT_QUOTES, 'UTF-8'); ?>'>
        <div class="lg:col-span-3 bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg mb-4 text-slate-800">Overall Party Results</h3><div class="relative h-64"><canvas id="partyResultsChart"></canvas></div></div>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg mb-4 text-slate-800">Vote Share Distribution</h3><div class="relative h-64"><canvas id="voteShareChart"></canvas></div></div>
    </div>

    <!-- Recent Submissions Table (UPDATED) -->
    <div class="bg-white rounded-xl shadow-lg mt-8">
        <div class="p-5 border-b border-slate-200"><h3 class="font-display font-bold text-lg text-slate-800">Recent Submissions</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                    <tr>
                        <th class="px-6 py-3">PU Code</th>
                        <th class="px-6 py-3">LGA</th>
                        <th class="px-6 py-3">State</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
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
                                <td class="px-6 py-4 font-bold text-slate-800"><?php echo htmlspecialchars($row['pu_code']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['lga_name']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['state_name']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Transmitted</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button class="view-btn text-blue-600 hover:text-blue-800 p-1" title="View Details" 
                                            data-pu-name="<?php echo htmlspecialchars($row['pu_name']); ?>" 
                                            data-location="<?php echo htmlspecialchars($row['ward_name'] . ', ' . $row['lga_name'] . ', ' . $row['state_name']); ?>" 
                                            data-scores="<?php echo htmlspecialchars($row['scores'] ?? ''); ?>" 
                                            data-image-path="../<?php echo htmlspecialchars($row['result_sheet_path']); ?>">
                                        <i class="bi bi-eye-fill text-lg"></i>
                                    </button>
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

<!-- View Modal (Copied from results-analysis.php) -->
<div id="view-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 opacity-0 hidden transition-opacity duration-300 pointer-events-none">
    <div id="view-modal-content" class="bg-slate-50 rounded-xl shadow-2xl w-full max-w-4xl transform scale-95 transition-all duration-300 mx-4 max-h-[90vh] flex flex-col">
        <header class="p-4 border-b bg-white rounded-t-xl flex justify-between items-center">
            <div>
                 <h2 id="modal-title" class="font-display font-bold text-2xl text-slate-800"></h2>
                 <p id="modal-location" class="text-slate-500"></p>
            </div>
            <button id="close-view-modal-btn" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </header>
        <div class="p-6 overflow-y-auto">
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><h3 class="font-bold text-lg mb-2 text-slate-700">Submitted Scores</h3><div id="modal-scores" class="bg-white p-4 rounded-lg border text-sm font-mono"></div></div>
                <div><h3 class="font-bold text-lg mb-2 text-slate-700">Scanned Result Sheet</h3><div class="border rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center min-h-[200px]"><img id="modal-image" src="" alt="Scanned Result Sheet" class="w-full h-auto object-contain max-h-[50vh]"></div></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar active link highlighter
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.href.includes('dashboard.php')) link.classList.add('active');
        else link.classList.remove('active');
    });

    // Chart rendering logic (Unchanged)
    const chartsContainer = document.getElementById('charts-container');
    if (chartsContainer) { /* ... Your existing chart logic ... */ }
    
    // "Show More" Button Logic (Unchanged)
    const showMoreBtn = document.getElementById('show-more-btn');
    if (showMoreBtn) {
        const hasMoreThanTen = <?php echo ($total_fetched > 10) ? 'true' : 'false'; ?>;
        showMoreBtn.addEventListener('click', () => {
            document.querySelectorAll('.extra-row').forEach(row => row.classList.remove('hidden'));
            const footerActionContainer = document.getElementById('table-footer-action');
            let newHtml = hasMoreThanTen
                ? `<a href="results-analysis.php" class="bg-inec-green text-white font-semibold py-2 px-5 rounded-lg hover:opacity-90 transition shadow-sm inline-block">View All Results</a>`
                : `<p class="text-sm text-slate-500">All recent submissions are now showing.</p>`;
            footerActionContainer.innerHTML = newHtml;
        });
    }

    // --- START: NEW SCRIPT FOR VIEW MODAL ---
    const viewModal = document.getElementById('view-modal');
    const viewModalContent = document.getElementById('view-modal-content');
    const showModal = (modal, content) => {
        modal.classList.remove('hidden', 'pointer-events-none', 'opacity-0');
        content.classList.remove('opacity-0', 'scale-95');
    };
    const hideModal = (modal, content) => {
        modal.classList.add('opacity-0');
        content.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden', 'pointer-events-none'), 300);
    };

    document.getElementById('close-view-modal-btn').addEventListener('click', () => hideModal(viewModal, viewModalContent));
    viewModal.addEventListener('click', (e) => { if(e.target === viewModal) hideModal(viewModal, viewModalContent) });

    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('modal-title').textContent = `${button.dataset.puName}`;
            document.getElementById('modal-location').textContent = button.dataset.location;
            document.getElementById('modal-image').src = button.dataset.imagePath;
            const scoresHtml = '<ul class="space-y-1">' + 
                               (button.dataset.scores.length 
                               ? button.dataset.scores.split(', ').map(s => `<li class="flex justify-between items-center border-b pb-1"><span>${s.split(':')[0]}</span><span class="font-bold text-slate-800">${parseInt(s.split(':')[1]).toLocaleString()}</span></li>`).join('') 
                               : '<li>No scores recorded.</li>') + 
                               '</ul>';
            document.getElementById('modal-scores').innerHTML = scoresHtml;
            showModal(viewModal, viewModalContent);
        });
    });
    // --- END: NEW SCRIPT FOR VIEW MODAL ---
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>
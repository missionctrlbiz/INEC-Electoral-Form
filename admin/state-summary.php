<?php
require_once '../core/db_connect.php';
require_admin();

// --- DATA FETCHING ---
// Get all parties and rank them by total votes to determine the top 7
$all_parties_query = $conn->query("SELECT p.id, p.acronym, SUM(ps.score) as total_votes FROM political_parties p LEFT JOIN party_scores ps ON p.id = ps.party_id GROUP BY p.id, p.acronym ORDER BY total_votes DESC");
$all_parties = $all_parties_query->fetch_all(MYSQLI_ASSOC);
$top_parties = array_slice($all_parties, 0, 7);
$other_parties = array_slice($all_parties, 7);

// Dynamically build the SUM clauses for each party for the main query
$select_clauses = ["SUM(r.total_valid_votes) as total_votes", "SUM(r.accredited_voters) as accredited_voters"];
foreach ($all_parties as $party) {
    $party_id = (int)$party['id'];
    $select_clauses[] = "SUM(CASE WHEN ps.party_id = $party_id THEN ps.score ELSE 0 END) AS party_$party_id";
}
$select_sql = implode(', ', $select_clauses);

// Main SQL query: Use LEFT JOIN from `states` to ensure all 37 states are always included in the dataset.
// We will filter them in PHP later.
$sql = "SELECT s.id, s.name, $select_sql 
        FROM states s
        LEFT JOIN lgas l ON s.id = l.state_id
        LEFT JOIN wards w ON l.id = w.lga_id
        LEFT JOIN polling_units pu ON w.id = pu.ward_id
        LEFT JOIN results r ON pu.id = r.polling_unit_id
        LEFT JOIN party_scores ps ON r.id = ps.result_id
        GROUP BY s.id, s.name ORDER BY s.name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$all_states_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// --- START: NEW PHP LOGIC TO SEPARATE STATES ---
$states_with_records = [];
$states_without_records = [];

foreach ($all_states_data as $row) {
    // A state has records if its total_votes is not null and greater than 0
    if (isset($row['total_votes']) && $row['total_votes'] > 0) {
        $states_with_records[] = $row;
    } else {
        $states_without_records[] = $row;
    }
}
// For the initial chart, only use data from states with records.
$chart_data_json = json_encode(array_map(function($row) {
    return ['name' => $row['name'], 'total_votes' => (int)$row['total_votes']];
}, $states_with_records));
// --- END: NEW PHP LOGIC ---

require_once '../includes/admin_header.php';
?>
<main class="flex-1 p-6 bg-slate-100">
    <header class="mb-6">
        <h1 class="font-display text-3xl font-bold text-slate-900">State-Level Summary</h1>
        <p class="text-slate-600 mt-1">Aggregated results from all states.</p>
    </header>

    <div id="visualization-container" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" data-chart-data='<?php echo htmlspecialchars($chart_data_json, ENT_QUOTES, 'UTF-8'); ?>'>
        <div class="bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg text-slate-800">Total Votes per State</h3><div class="relative h-72"><canvas id="summaryBarChart"></canvas></div></div>
        <div class="bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg text-slate-800">Top 5 States by Turnout</h3><div class="relative h-72"><canvas id="topStatesChart"></canvas></div></div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-end mb-4">
            <?php if (count($other_parties) > 0): ?>
            <button id="toggle-parties-btn" class="bg-slate-100 text-slate-700 font-semibold px-4 py-2 rounded-lg hover:bg-slate-200 transition text-sm"><i class="bi bi-arrows-expand mr-2"></i>Show All Parties (<?php echo count($all_parties); ?>)</button>
            <?php endif; ?>
        </div>
        <div class="overflow-x-auto">
            <table id="results-table" class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                    <tr>
                        <th class="px-6 py-3 cursor-pointer sortable" data-sort-dir="asc">State <i class="bi bi-arrow-down-up ml-1"></i></th>
                        <th class="px-6 py-3 text-center cursor-pointer sortable" data-sort-dir="asc">Total Votes <i class="bi bi-arrow-down-up ml-1"></i></th>
                        <th class="px-6 py-3 text-center cursor-pointer sortable" data-sort-dir="asc">Accredited Voters <i class="bi bi-arrow-down-up ml-1"></i></th>
                        <?php foreach ($top_parties as $party): ?><th class="px-6 py-3 text-center cursor-pointer sortable" data-sort-dir="asc"><?php echo htmlspecialchars($party['acronym']); ?> <i class="bi bi-arrow-down-up ml-1"></i></th><?php endforeach; ?>
                        <?php foreach ($other_parties as $party): ?><th class="px-6 py-3 text-center cursor-pointer sortable hidden toggleable-party-col" data-sort-dir="asc"><?php echo htmlspecialchars($party['acronym']); ?> <i class="bi bi-arrow-down-up ml-1"></i></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody id="results-tbody">
                    <?php if (empty($states_with_records)): ?>
                        <tr><td colspan="<?php echo count($all_parties) + 3; ?>" class="text-center py-10 text-slate-500">No states have results yet.</td></tr>
                    <?php endif; ?>
                    <!-- Initially, show only states with records -->
                    <?php foreach ($states_with_records as $row): ?>
                    <tr class="bg-white border-b hover:bg-slate-50">
                        <td class="px-6 py-4 font-bold text-slate-800"><a href="lga-summary.php?state_id=<?php echo $row['id']; ?>" class="text-inec-green hover:underline"><?php echo htmlspecialchars($row['name']); ?></a></td>
                        <td class="px-6 py-4 text-center font-semibold"><?php echo number_format($row['total_votes']); ?></td>
                        <td class="px-6 py-4 text-center"><?php echo number_format($row['accredited_voters']); ?></td>
                        <?php foreach ($top_parties as $party): ?><td class="px-6 py-4 text-center"><?php echo number_format($row['party_' . $party['id']]); ?></td><?php endforeach; ?>
                        <?php foreach ($other_parties as $party): ?><td class="px-6 py-4 text-center hidden toggleable-party-col"><?php echo number_format($row['party_' . $party['id']]); ?></td><?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                    <!-- States without records are hidden by default -->
                    <?php foreach ($states_without_records as $row): ?>
                    <tr class="bg-slate-50 border-b hover:bg-slate-100 hidden zero-record-row">
                        <td class="px-6 py-4 font-medium text-slate-500"><a href="lga-summary.php?state_id=<?php echo $row['id']; ?>" class="text-slate-500 hover:underline"><?php echo htmlspecialchars($row['name']); ?></a></td>
                        <td class="px-6 py-4 text-center text-slate-400">0</td>
                        <td class="px-6 py-4 text-center text-slate-400">0</td>
                        <?php foreach ($all_parties as $party): // Show all party columns for consistency ?>
                            <td class="px-6 py-4 text-center text-slate-400 <?php echo in_array($party, $other_parties, true) ? 'hidden toggleable-party-col' : ''; ?>">0</td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($states_without_records)): ?>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo count($all_parties) + 3; ?>" class="text-center p-4">
                            <div id="table-footer-action">
                                <button id="show-all-states-btn" class="text-inec-green font-semibold hover:underline transition">
                                    <i class="bi bi-chevron-down mr-1"></i> Show All 37 States
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
    // Visualization logic (unchanged)
    const chartContainer = document.getElementById('visualization-container');
    const barCanvas = document.getElementById('summaryBarChart');
    const topStatesCanvas = document.getElementById('topStatesChart');
    try {
        const chartData = JSON.parse(chartContainer.dataset.chartData);
        if (!chartData || chartData.length === 0) throw new Error('No data');
        const labels = chartData.map(d => d.name);
        const scores = chartData.map(d => d.total_votes);
        new Chart(barCanvas.getContext('2d'), { type: 'bar', data: { labels: labels, datasets: [{ label: 'Total Votes', data: scores, backgroundColor: 'rgba(0, 106, 78, 0.2)', borderColor: 'rgba(0, 106, 78, 1)', borderWidth: 1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
        const sortedStates = [...chartData].sort((a, b) => b.total_votes - a.total_votes).slice(0, 5);
        const topLabels = sortedStates.map(d => d.name);
        const topScores = sortedStates.map(d => d.total_votes);
        new Chart(topStatesCanvas.getContext('2d'), { type: 'bar', data: { labels: topLabels, datasets: [{ label: 'Total Votes', data: topScores, backgroundColor: 'rgba(59, 130, 246, 0.7)', borderColor: 'rgba(59, 130, 246, 1)', borderWidth: 1 }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
    } catch (e) {
        chartContainer.innerHTML = '<p class="text-center col-span-full flex items-center justify-center h-full text-slate-500 py-20">No data available for visualization.</p>';
    }

    // Table Sorting and Party Toggle Logic (unchanged)
    // ...

    // --- START: NEW "SHOW ALL STATES" LOGIC ---
    const showAllStatesBtn = document.getElementById('show-all-states-btn');
    if (showAllStatesBtn) {
        showAllStatesBtn.addEventListener('click', () => {
            // Find all hidden rows with the 'zero-record-row' class and show them
            document.querySelectorAll('.zero-record-row').forEach(row => {
                row.classList.remove('hidden');
            });
            
            // Remove the button and its container after it's clicked
            const footerActionContainer = document.getElementById('table-footer-action');
            if(footerActionContainer) {
                footerActionContainer.innerHTML = `<p class="text-sm text-slate-500">Showing all 37 states.</p>`;
            }
        });
    }
    // --- END: NEW "SHOW ALL STATES" LOGIC ---
});
</script>
</main>
<?php require_once '../includes/admin_footer.php'; ?>
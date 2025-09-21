<?php
require_once '../core/db_connect.php';
require_admin();

// --- DATA FETCHING ---
// Task: "only show state with records" - This query achieves that using an INNER JOIN.
$states_with_records_query = $conn->query("
    SELECT DISTINCT s.id, s.name 
    FROM states s
    INNER JOIN lgas l ON s.id = l.state_id
    INNER JOIN wards w ON l.id = w.lga_id
    INNER JOIN polling_units pu ON w.id = pu.ward_id
    INNER JOIN results r ON pu.id = r.polling_unit_id
    ORDER BY s.name ASC
");

$state_id = isset($_GET['state_id']) ? (int)$_GET['state_id'] : null;
$state_name = "";
$data_rows = [];
$chart_data_json = '[]';

// Get all parties and rank them by total votes to determine the top 7
$all_parties_query = $conn->query("SELECT p.id, p.acronym, SUM(ps.score) as total_votes FROM political_parties p LEFT JOIN party_scores ps ON p.id = ps.party_id GROUP BY p.id, p.acronym ORDER BY total_votes DESC");
$all_parties = $all_parties_query->fetch_all(MYSQLI_ASSOC);
$top_parties = array_slice($all_parties, 0, 7);
$other_parties = array_slice($all_parties, 7);

if ($state_id) {
    // This block runs ONLY when a state is selected
    $state_stmt = $conn->prepare("SELECT name FROM states WHERE id = ?");
    $state_stmt->bind_param("i", $state_id);
    $state_stmt->execute();
    $state = $state_stmt->get_result()->fetch_assoc();
    $state_name = $state ? $state['name'] : 'Invalid State';
    
    // Dynamically build SUM clauses
    $select_clauses = ["SUM(r.total_valid_votes) as total_votes", "SUM(r.accredited_voters) as accredited_voters"];
    foreach ($all_parties as $party) {
        $select_clauses[] = "SUM(CASE WHEN ps.party_id = {$party['id']} THEN ps.score ELSE 0 END) AS party_{$party['id']}";
    }
    $select_sql = implode(', ', $select_clauses);

    $sql = "SELECT l.id, l.name, $select_sql FROM lgas l LEFT JOIN wards w ON l.id = w.lga_id LEFT JOIN polling_units pu ON w.id = pu.ward_id LEFT JOIN results r ON pu.id = r.polling_unit_id LEFT JOIN party_scores ps ON r.id = ps.result_id WHERE l.state_id = ? GROUP BY l.id, l.name ORDER BY l.name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $state_id);
    $stmt->execute();
    $data_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $chart_data_json = json_encode(array_map(fn($row) => ['name' => $row['name'], 'total_votes' => (int)($row['total_votes'] ?? 0)], $data_rows));
}

require_once '../includes/admin_header.php';
?>
<main class="flex-1 p-6 bg-slate-100">
    <header class="mb-6">
        <h1 class="font-display text-3xl font-bold text-slate-900">LGA-Level Summary</h1>
        <p class="text-slate-600 mt-1">Aggregated results for Local Government Areas.</p>
    </header>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <form method="GET" class="w-full">
            <div>
                <label for="state-select" class="text-sm font-medium text-slate-700">Select a State to View its LGAs</label>
                <select name="state_id" id="state-select" class="w-full mt-1 p-2 border border-slate-300 rounded-lg bg-slate-50 focus:ring-2 focus:ring-inec-green" onchange="this.form.submit()">
                    <option value="">-- Select a State with Records --</option>
                    <?php while($state = $states_with_records_query->fetch_assoc()): ?>
                        <option value="<?php echo $state['id']; ?>" <?php if($state_id == $state['id']) echo 'selected'; ?>><?php echo htmlspecialchars($state['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($state_id): ?>
        <div id="visualization-container" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" data-chart-data='<?php echo htmlspecialchars($chart_data_json, ENT_QUOTES, 'UTF-8'); ?>'>
            <div class="bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg text-slate-800">Total Votes per LGA in <?php echo htmlspecialchars($state_name); ?></h3><div class="relative h-72"><canvas id="summaryBarChart"></canvas></div></div>
            <div class="bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg text-slate-800">Top 5 LGAs by Turnout</h3><div class="relative h-72"><canvas id="topItemsChart"></canvas></div></div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-display text-xl font-bold text-slate-800">Results for <?php echo htmlspecialchars($state_name); ?></h3>
                <?php if (count($other_parties) > 0): ?>
                    <button id="toggle-parties-btn" class="bg-slate-100 text-slate-700 font-semibold px-4 py-2 rounded-lg hover:bg-slate-200 transition text-sm"><i class="bi bi-arrows-expand mr-2"></i>Show All Parties</button>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table id="results-table" class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                        <tr>
                            <th class="px-6 py-3">LGA</th><th class="px-6 py-3 text-center">Total Votes</th><th class="px-6 py-3 text-center">Accredited Voters</th>
                            <?php foreach ($top_parties as $party): ?><th class="px-6 py-3 text-center"><?php echo htmlspecialchars($party['acronym']); ?></th><?php endforeach; ?>
                            <?php foreach ($other_parties as $party): ?><th class="px-6 py-3 text-center hidden toggleable-party-col"><?php echo htmlspecialchars($party['acronym']); ?></th><?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_rows)): ?><tr><td colspan="<?php echo count($all_parties) + 3; ?>" class="text-center py-10 text-slate-500">No results found for LGAs in this state.</td></tr><?php endif; ?>
                        <?php 
                        $rowIndex = 0;
                        foreach ($data_rows as $row): 
                            $hidden_class = ($rowIndex >= 5) ? 'hidden lga-extra-row' : '';
                        ?>
                        <tr class="bg-white border-b hover:bg-slate-50 <?php echo $hidden_class; ?>">
                            <td class="px-6 py-4 font-bold text-slate-800"><a href="ward-summary.php?lga_id=<?php echo $row['id']; ?>" class="text-inec-green hover:underline"><?php echo htmlspecialchars($row['name']); ?></a></td>
                            <td class="px-6 py-4 text-center font-semibold"><?php echo number_format($row['total_votes'] ?? 0); ?></td>
                            <td class="px-6 py-4 text-center"><?php echo number_format($row['accredited_voters'] ?? 0); ?></td>
                            <?php foreach ($top_parties as $party): ?><td class="px-6 py-4 text-center"><?php echo number_format($row['party_' . $party['id']] ?? 0); ?></td><?php endforeach; ?>
                            <?php foreach ($other_parties as $party): ?><td class="px-6 py-4 text-center hidden toggleable-party-col"><?php echo number_format($row['party_' . $party['id']] ?? 0); ?></td><?php endforeach; ?>
                        </tr>
                        <?php 
                        $rowIndex++;
                        endforeach; 
                        ?>
                    </tbody>
                    <?php if (count($data_rows) > 5): ?>
                    <tfoot>
                        <tr>
                            <td colspan="<?php echo count($all_parties) + 3; ?>" class="text-center p-4">
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
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-lg p-10 text-center"><i class="bi bi-info-circle text-4xl text-blue-500 mb-4"></i><h3 class="font-display text-xl font-bold text-slate-700">Please select a state</h3><p class="text-slate-500 mt-2">Use the dropdown menu above to view the LGA results for a specific state.</p></div>
    <?php endif; ?>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Charting Logic (reusable and correct)
    const chartContainer = document.querySelector('[data-chart-data]');
    if (chartContainer) {
        const barCanvas = document.getElementById('summaryBarChart');
        const topItemsCanvas = document.getElementById('topItemsChart');
        try {
            const chartData = JSON.parse(chartContainer.dataset.chartData).filter(d => d.total_votes > 0);
            if (!chartData || chartData.length === 0) throw new Error('No data');
            const labels = chartData.map(d => d.name);
            const scores = chartData.map(d => d.total_votes);
            new Chart(barCanvas.getContext('2d'), { type: 'bar', data: { labels: labels, datasets: [{ label: 'Total Votes', data: scores, backgroundColor: 'rgba(0, 106, 78, 0.7)' }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
            const sortedItems = [...chartData].sort((a, b) => b.total_votes - a.total_votes).slice(0, 5);
            new Chart(topItemsCanvas.getContext('2d'), { type: 'bar', data: { labels: sortedItems.map(d=>d.name), datasets: [{ label: 'Total Votes', data: sortedItems.map(d=>d.total_votes), backgroundColor: 'rgba(59, 130, 246, 0.7)' }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
        } catch (e) {
            chartContainer.innerHTML = '<p class="text-center col-span-full flex items-center justify-center h-full text-slate-500 py-20">No data available for visualization.</p>';
        }
    }

    // Party Toggle Logic
    const toggleBtn = document.getElementById('toggle-parties-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = toggleBtn.dataset.toggled !== 'true';
            document.querySelectorAll('.toggleable-party-col').forEach(col => col.classList.toggle('hidden', !isHidden));
            toggleBtn.dataset.toggled = isHidden;
            toggleBtn.innerHTML = isHidden ? '<i class="bi bi-arrows-collapse mr-2"></i>Show Top 7' : '<i class="bi bi-arrows-expand mr-2"></i>Show All Parties';
        });
    }

    // "Show 5 More" Logic
    const showMoreBtn = document.getElementById('show-more-btn');
    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', () => {
            const hiddenRows = document.querySelectorAll('tr.lga-extra-row.hidden');
            for (let i = 0; i < 5 && i < hiddenRows.length; i++) {
                hiddenRows[i].classList.remove('hidden');
            }
            // Check if there are still any hidden rows left
            if (document.querySelectorAll('tr.lga-extra-row.hidden').length === 0) {
                const footerAction = document.getElementById('table-footer-action');
                if(footerAction) footerAction.innerHTML = `<p class="text-sm text-slate-500">All LGAs are now showing.</p>`;
            }
        });
    }
});
</script>
<?php require_once '../includes/admin_footer.php'; ?>
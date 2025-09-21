<?php
require_once '../core/db_connect.php';
require_admin();

// --- DATA VALIDATION & FETCHING ---
$state_id_filter = isset($_GET['state_id']) ? (int)$_GET['state_id'] : null;
$lga_id_filter = isset($_GET['lga_id']) ? (int)$_GET['lga_id'] : null;

$page_title = "Ward-Level Summary";
$loc_name = "";
$data_rows = [];
$chart_data_json = '[]';

// --- DATA FOR FILTERS ---
// Get ONLY states that have submitted results for the dropdown.
$states_with_results_query = $conn->query("
    SELECT DISTINCT s.id, s.name 
    FROM states s
    JOIN lgas l ON s.id = l.state_id
    JOIN wards w ON l.id = w.lga_id
    JOIN polling_units pu ON w.id = pu.ward_id
    JOIN results r ON pu.id = r.polling_unit_id
    ORDER BY s.name ASC
");
$lgas_data_json = json_encode($conn->query("SELECT id, name, state_id FROM lgas ORDER BY name")->fetch_all(MYSQLI_ASSOC));


// --- PARTY RANKING LOGIC ---
$all_parties_query = $conn->query("SELECT p.id, p.acronym, SUM(ps.score) as total_votes FROM political_parties p LEFT JOIN party_scores ps ON p.id = ps.party_id GROUP BY p.id, p.acronym ORDER BY total_votes DESC");
$all_parties = $all_parties_query->fetch_all(MYSQLI_ASSOC);
$top_parties = array_slice($all_parties, 0, 7);
$other_parties = array_slice($all_parties, 7);


if ($lga_id_filter) {
    // This block runs ONLY when an LGA is selected
    $loc_stmt = $conn->prepare("SELECT l.name as lga_name, s.id as state_id, s.name as state_name FROM lgas l JOIN states s ON l.state_id = s.id WHERE l.id = ?");
    $loc_stmt->bind_param("i", $lga_id_filter);
    $loc_stmt->execute();
    $loc = $loc_stmt->get_result()->fetch_assoc();
    $loc_name = $loc ? (htmlspecialchars($loc['lga_name']) . ", " . htmlspecialchars($loc['state_name'])) : 'Invalid LGA';
    $page_title = "Ward Summary for " . $loc_name;
    
    // Dynamically build SUM clauses
    $select_clauses = ["SUM(r.total_valid_votes) as total_votes", "SUM(r.accredited_voters) as accredited_voters"];
    foreach ($all_parties as $party) {
        $select_clauses[] = "SUM(CASE WHEN ps.party_id = {$party['id']} THEN ps.score ELSE 0 END) AS party_{$party['id']}";
    }
    $select_sql = implode(', ', $select_clauses);

    // Fetch 11 to handle "Show More" logic
    $sql = "SELECT w.id, w.name, $select_sql 
            FROM wards w
            LEFT JOIN polling_units pu ON w.id = pu.ward_id
            LEFT JOIN results r ON pu.id = r.polling_unit_id
            LEFT JOIN party_scores ps ON r.id = ps.result_id
            WHERE w.lga_id = ?
            GROUP BY w.id, w.name ORDER BY w.name ASC LIMIT 11";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lga_id_filter);
    $stmt->execute();
    $data_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $chart_data_json = json_encode(array_map(fn($row) => ['name' => $row['name'], 'total_votes' => (int)($row['total_votes'] ?? 0)], $data_rows));
}

require_once '../includes/admin_header.php';
?>
<main class="flex-1 p-6 bg-slate-100">
    <header class="mb-6">
        <h1 class="font-display text-3xl font-bold text-slate-900"><?php echo $page_title; ?></h1>
        <p class="text-slate-600 mt-1">Aggregated results for Wards.</p>
    </header>

    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <form id="filter-form" method="GET" class="space-y-4">
            <div>
                <label for="state-filter" class="text-sm font-medium text-slate-700">First, Select a State (Only states with results are shown)</label>
                <select id="state-filter" name="state_id" class="w-full mt-1 p-2 border border-slate-300 rounded-lg bg-slate-50">
                    <option value="">-- Select State --</option>
                    <?php while($state = $states_with_results_query->fetch_assoc()): ?>
                        <option value="<?php echo $state['id']; ?>" <?php if($state_id_filter == $state['id']) echo 'selected'; ?>><?php echo htmlspecialchars($state['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="lga-filter" class="text-sm font-medium text-slate-700">Then, Select an LGA</label>
                <select id="lga-filter" name="lga_id" class="w-full mt-1 p-2 border border-slate-300 rounded-lg bg-slate-50" disabled>
                    <option value="">-- Select LGA --</option>
                </select>
            </div>
        </form>
    </div>

    <?php if ($lga_id_filter): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" data-chart-data='<?php echo htmlspecialchars($chart_data_json, ENT_QUOTES, 'UTF-8'); ?>'>
            <div class="bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg text-slate-800">Total Votes per Ward in <?php echo $loc_name; ?></h3><div class="relative h-72"><canvas id="summaryBarChart"></canvas></div></div>
            <div class="bg-white rounded-xl shadow-lg p-5"><h3 class="font-display font-bold text-lg text-slate-800">Top 5 Wards by Turnout</h3><div class="relative h-72"><canvas id="topItemsChart"></canvas></div></div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-end mb-4">
                 <?php if (count($other_parties) > 0): ?><button id="toggle-parties-btn" class="bg-slate-100 text-slate-700 font-semibold px-4 py-2 rounded-lg hover:bg-slate-200 transition text-sm"><i class="bi bi-arrows-expand mr-2"></i>Show All Parties</button><?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                        <tr>
                            <th class="px-6 py-3">Ward</th><th class="px-6 py-3 text-center">Total Votes</th><th class="px-6 py-3 text-center">Accredited Voters</th>
                            <?php foreach ($top_parties as $party): ?><th class="px-6 py-3 text-center"><?php echo htmlspecialchars($party['acronym']); ?></th><?php endforeach; ?>
                            <?php foreach ($other_parties as $party): ?><th class="px-6 py-3 text-center hidden toggleable-party-col"><?php echo htmlspecialchars($party['acronym']); ?></th><?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_fetched = count($data_rows);
                        if ($total_fetched > 0):
                            for ($i = 0; $i < $total_fetched && $i < 10; $i++):
                                $row = $data_rows[$i];
                                $hidden_class = ($i >= 5) ? 'hidden extra-row' : '';
                        ?>
                        <tr class="bg-white border-b hover:bg-slate-50 <?php echo $hidden_class; ?>">
                            <td class="px-6 py-4 font-bold text-slate-800"><a href="polling-unit-results.php?ward_id=<?php echo $row['id']; ?>" class="text-inec-green hover:underline"><?php echo htmlspecialchars($row['name']); ?></a></td>
                            <td class="px-6 py-4 text-center font-semibold"><?php echo number_format($row['total_votes'] ?? 0); ?></td>
                            <td class="px-6 py-4 text-center"><?php echo number_format($row['accredited_voters'] ?? 0); ?></td>
                            <?php foreach ($top_parties as $party): ?><td class="px-6 py-4 text-center"><?php echo number_format($row['party_' . $party['id']] ?? 0); ?></td><?php endforeach; ?>
                            <?php foreach ($other_parties as $party): ?><td class="px-6 py-4 text-center hidden toggleable-party-col"><?php echo number_format($row['party_' . $party['id']] ?? 0); ?></td><?php endforeach; ?>
                        </tr>
                        <?php endfor; else: ?>
                            <tr><td colspan="<?php echo count($all_parties) + 3; ?>" class="text-center py-10 text-slate-500">No results found for Wards in this LGA.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if ($total_fetched > 5): ?>
                    <tfoot>
                        <tr>
                            <td colspan="<?php echo count($all_parties) + 3; ?>" class="text-center p-4">
                                <div id="table-footer-action">
                                    <button id="show-more-btn" class="text-inec-green font-semibold hover:underline transition"><i class="bi bi-chevron-down mr-1"></i> Show <?php echo min(5, $total_fetched - 5); ?> More</button>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-lg p-10 text-center"><i class="bi bi-info-circle text-4xl text-blue-500 mb-4"></i><h3 class="font-display text-xl font-bold text-slate-700">Please select a State and LGA</h3><p class="text-slate-500 mt-2">Use the dropdown menus above to view the Ward results.</p></div>
    <?php endif; ?>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const stateFilter = document.getElementById('state-filter');
    const lgaFilter = document.getElementById('lga-filter');
    const lgasData = <?php echo $lgas_data_json; ?>;
    const currentLgaId = '<?php echo $lga_id_filter; ?>';

    function populateLgaFilter(selectedStateId) { /* ... same as before ... */ }
    stateFilter.addEventListener('change', () => { populateLgaFilter(stateFilter.value); });
    lgaFilter.addEventListener('change', () => { if (lgaFilter.value) document.getElementById('filter-form').submit(); });
    if (stateFilter.value) populateLgaFilter(stateFilter.value);

    // Charting Logic (reusable)
    // ...

    // Party Toggle Logic (reusable)
    // ...

    // --- "SHOW MORE" LOGIC ---
    const showMoreBtn = document.getElementById('show-more-btn');
    if (showMoreBtn) {
        const hasMoreThanTen = <?php echo ($total_fetched > 10) ? 'true' : 'false'; ?>;
        showMoreBtn.addEventListener('click', () => {
            document.querySelectorAll('.extra-row').forEach(row => row.classList.remove('hidden'));
            const footer = document.getElementById('table-footer-action');
            footer.innerHTML = `<a href="polling-unit-results.php?lga_id=<?php echo $lga_id_filter; ?>" class="bg-inec-green text-white font-semibold py-2 px-5 rounded-lg hover:opacity-90">View All Polling Units in this LGA</a>`;
        });
    }
});
</script>
<?php require_once '../includes/admin_footer.php'; ?>
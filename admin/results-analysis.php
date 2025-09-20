<?php
require_once '../core/db_connect.php';
require_admin();

// --- ACTION HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $result_id = (int)($_POST['result_id'] ?? 0);
    if ($result_id > 0) {
        $action = $_POST['action'];
        $new_status = '';

        if ($action === 'flag') { $new_status = 'flagged'; } 
        elseif ($action === 'verify') { $new_status = 'verified'; }

        if ($new_status) {
            $update_stmt = $conn->prepare("UPDATE results SET status = ? WHERE id = ?");
            $update_stmt->bind_param('si', $new_status, $result_id);
            $update_stmt->execute();
        } elseif ($action === 'delete') {
            // Secure delete: first delete child records, then the parent
            $conn->begin_transaction();
            try {
                $stmt_scores = $conn->prepare("DELETE FROM party_scores WHERE result_id = ?");
                $stmt_scores->bind_param('i', $result_id);
                $stmt_scores->execute();
                
                $stmt_results = $conn->prepare("DELETE FROM results WHERE id = ?");
                $stmt_results->bind_param('i', $result_id);
                $stmt_results->execute();
                
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                // Optionally log the error
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}

// --- PAGINATION & FILTERING LOGIC ---
$results_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

$state_filter = $_GET['state_id'] ?? null;
$lga_filter = $_GET['lga_id'] ?? null;
$status_filter = $_GET['status'] ?? null;
$where_clauses = []; $params = []; $param_types = '';

if ($state_filter) { $where_clauses[] = "s.id = ?"; $params[] = $state_filter; $param_types .= 'i'; }
if ($lga_filter) { $where_clauses[] = "l.id = ?"; $params[] = $lga_filter; $param_types .= 'i'; }
if ($status_filter) { $where_clauses[] = "r.status = ?"; $params[] = $status_filter; $param_types .= 's'; }
$where_sql = !empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : '';

// Count total results for pagination
$total_sql = "SELECT COUNT(DISTINCT r.id) as total FROM results r JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id JOIN lgas l ON w.lga_id = l.id JOIN states s ON l.state_id = s.id" . $where_sql;
$total_stmt = $conn->prepare($total_sql);
if (!empty($params)) { $total_stmt->bind_param($param_types, ...$params); }
$total_stmt->execute();
$total_results = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);


// Fetch paginated results
$sql = "SELECT r.id as result_id, r.status, r.result_sheet_path, pu.name as pu_name, w.name as ward_name, l.name as lga_name, s.name as state_name, GROUP_CONCAT(CONCAT(pp.acronym, ':', ps.score) ORDER BY pp.acronym SEPARATOR ', ') as scores FROM results r JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id JOIN lgas l ON w.lga_id = l.id JOIN states s ON l.state_id = s.id LEFT JOIN party_scores ps ON ps.result_id = r.id LEFT JOIN political_parties pp ON ps.party_id = pp.id" . $where_sql . " GROUP BY r.id ORDER BY r.submitted_at DESC LIMIT ? OFFSET ?";
$params[] = $results_per_page;
$params[] = $offset;
$param_types .= 'ii';
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$results_query = $stmt->get_result();

// Data for filters
$states_query = $conn->query("SELECT id, name FROM states ORDER BY name");
$lgas_data_json = json_encode($conn->query("SELECT id, name, state_id FROM lgas ORDER BY name")->fetch_all(MYSQLI_ASSOC));

require_once '../includes/admin_header.php';
?>

<main class="flex-1 p-6 bg-slate-100">
    <header class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
        <h1 class="font-display text-3xl font-bold text-slate-900">All Submitted Results</h1>
        <a href="../core/export_csv.php?<?php echo http_build_query($_GET); ?>" class="bg-inec-green text-white font-semibold py-2 px-4 rounded-lg hover:opacity-90 transition whitespace-nowrap">
            <i class="bi bi-download mr-2"></i> Export Current View
        </a>
    </header>

    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <form action="results-analysis.php" method="GET" id="filter-form" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="state-filter" class="text-sm font-medium text-slate-600">State</label>
                <select name="state_id" id="state-filter" class="w-full p-2 mt-1 border border-slate-300 rounded-lg bg-slate-50">
                    <option value="">All States</option>
                    <?php if ($states_query): $states_query->data_seek(0); while($state = $states_query->fetch_assoc()): ?>
                        <option value="<?php echo $state['id']; ?>" <?php if($state_filter == $state['id']) echo 'selected'; ?>><?php echo htmlspecialchars($state['name']); ?></option>
                    <?php endwhile; endif; ?>
                </select>
            </div>
             <div class="md:col-span-2">
                <label for="lga-filter" class="text-sm font-medium text-slate-600">LGA</label>
                <select name="lga_id" id="lga-filter" class="w-full p-2 mt-1 border border-slate-300 rounded-lg bg-slate-50" <?php echo !$state_filter ? 'disabled' : ''; ?>>
                    <option value="">All LGAs</option>
                </select>
            </div>
             <div>
                <label for="status-filter" class="text-sm font-medium text-slate-600">Status</label>
                <select name="status" id="status-filter" class="w-full p-2 mt-1 border border-slate-300 rounded-lg bg-slate-50">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php if($status_filter == 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="verified" <?php if($status_filter == 'verified') echo 'selected'; ?>>Verified</option>
                    <option value="flagged" <?php if($status_filter == 'flagged') echo 'selected'; ?>>Flagged</option>
                </select>
            </div>
            <div class="md:col-span-5 flex justify-end gap-2">
                <a href="results-analysis.php" class="bg-slate-200 text-slate-700 font-semibold py-2 px-4 rounded-lg hover:bg-slate-300">Reset</a>
                <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-blue-700">Filter</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600">
            <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                <tr><th class="px-6 py-3">Polling Unit</th><th class="px-6 py-3">LGA</th><th class="px-6 py-3">State</th><th class="px-6 py-3">Scores</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-center">Actions</th></tr>
            </thead>
            <tbody>
                <?php if ($results_query && $results_query->num_rows > 0): ?>
                    <?php while($row = $results_query->fetch_assoc()): ?>
                        <tr class="bg-white border-b hover:bg-slate-50">
                            <td class="px-6 py-4 font-bold text-slate-800"><?php echo htmlspecialchars($row['pu_name']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['lga_name']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['state_name']); ?></td>
                            <td class="px-6 py-4 font-mono text-xs"><?php echo htmlspecialchars(str_replace(",", " | ", $row['scores'] ?? 'N/A')); ?></td>
                            <td class="px-6 py-4">
                                <?php 
                                    $status = $row['status'];
                                    $badge_class = 'bg-yellow-100 text-yellow-800';
                                    if ($status === 'verified') $badge_class = 'bg-green-100 text-green-800';
                                    if ($status === 'flagged') $badge_class = 'bg-red-100 text-red-800';
                                    echo '<span class="' . $badge_class . ' text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
                                ?>
                            </td>
                            <td class="px-6 py-4 space-x-1 text-center">
                                <button class="view-btn text-blue-600 hover:text-blue-800 p-1" title="View Details" data-pu-name="<?php echo htmlspecialchars($row['pu_name']); ?>" data-location="<?php echo htmlspecialchars($row['ward_name'] . ', ' . $row['lga_name'] . ', ' . $row['state_name']); ?>" data-scores="<?php echo htmlspecialchars($row['scores'] ?? ''); ?>" data-image-path="../<?php echo htmlspecialchars($row['result_sheet_path']); ?>"><i class="bi bi-eye-fill text-lg"></i></button>
                                <button class="action-btn" title="Mark as Verified" data-action="verify" data-result-id="<?php echo $row['result_id']; ?>"><i class="bi bi-check-circle-fill text-green-500 text-lg"></i></button>
                                <button class="action-btn" title="Flag for Review" data-action="flag" data-result-id="<?php echo $row['result_id']; ?>"><i class="bi bi-flag-fill text-orange-500 text-lg"></i></button>
                                <button class="action-btn" title="Delete Result" data-action="delete" data-result-id="<?php echo $row['result_id']; ?>"><i class="bi bi-trash-fill text-red-600 text-lg"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="px-6 py-4 text-center text-slate-500">No results found for the selected filters.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination Controls -->
    <div class="mt-6 flex flex-col md:flex-row justify-between items-center text-sm text-slate-600">
        <div class="mb-4 md:mb-0">
            Showing <b><?php echo max(1, $offset + 1); ?></b> to <b><?php echo min($offset + $results_per_page, $total_results); ?></b> of <b><?php echo number_format($total_results); ?></b> results
        </div>
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="inline-flex items-center -space-x-px">
                <?php
                $query_params = $_GET;
                // Previous page link
                if ($current_page > 1) {
                    $query_params['page'] = $current_page - 1;
                    echo '<li><a href="?' . http_build_query($query_params) . '" class="py-2 px-3 ml-0 leading-tight text-slate-500 bg-white rounded-l-lg border border-slate-300 hover:bg-slate-100 hover:text-slate-700">&laquo;</a></li>';
                }
                
                // Page number links
                for ($i = 1; $i <= $total_pages; $i++) {
                    $query_params['page'] = $i;
                    $is_current = ($i == $current_page);
                    $class = $is_current ? 'z-10 py-2 px-3 leading-tight text-blue-600 bg-blue-50 border border-blue-300' : 'py-2 px-3 leading-tight text-slate-500 bg-white border border-slate-300 hover:bg-slate-100';
                    echo '<li><a href="?' . http_build_query($query_params) . '" class="' . $class . '">' . $i . '</a></li>';
                }

                // Next page link
                if ($current_page < $total_pages) {
                    $query_params['page'] = $current_page + 1;
                    echo '<li><a href="?' . http_build_query($query_params) . '" class="py-2 px-3 leading-tight text-slate-500 bg-white rounded-r-lg border border-slate-300 hover:bg-slate-100 hover:text-slate-700">&raquo;</a></li>';
                }
                ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</main>


<!-- Modals (View & Confirm) -->
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

<div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 opacity-0 hidden transition-opacity duration-300 pointer-events-none">
    <div id="confirm-modal-content" class="bg-white rounded-xl shadow-2xl w-full max-w-md transform scale-95 transition-all p-8 text-center">
        <h2 id="confirm-title" class="font-display font-bold text-2xl text-slate-800"></h2><p id="confirm-text" class="text-slate-600 my-4"></p>
        <div class="flex justify-center gap-4"><button id="confirm-cancel-btn" class="bg-slate-200 font-semibold w-full py-2 rounded-lg hover:bg-slate-300">Cancel</button><button id="confirm-ok-btn" class="text-white font-semibold w-full py-2 rounded-lg"></button></div>
    </div>
</div>

<form id="action-form" method="POST" action="results-analysis.php?<?php echo http_build_query($_GET); ?>" class="hidden">
    <input type="hidden" name="action" id="action-input"><input type="hidden" name="result_id" id="result-id-input">
</form>

<script>
    // Pass the full LGA data from PHP to JavaScript
    const lgasData = <?php echo $lgas_data_json; ?>;
    const currentLgaId = '<?php echo $lga_filter; ?>';
</script>
<script src="../assets/js/results.js"></script>
</main>
<?php require_once '../includes/admin_footer.php'; ?>
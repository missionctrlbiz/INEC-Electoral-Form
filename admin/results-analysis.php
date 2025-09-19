<?php
require_once '../core/db_connect.php';
// require_admin();

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
            // Placeholder for delete logic
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}

// --- FILTERING LOGIC ---
$state_filter = $_GET['state_id'] ?? null;
$lga_filter = $_GET['lga_id'] ?? null;
$status_filter = $_GET['status'] ?? null;
$where_clauses = []; $params = []; $param_types = '';
if ($state_filter) { $where_clauses[] = "s.id = ?"; $params[] = $state_filter; $param_types .= 'i'; }
if ($lga_filter) { $where_clauses[] = "l.id = ?"; $params[] = $lga_filter; $param_types .= 'i'; }
if ($status_filter) { $where_clauses[] = "r.status = ?"; $params[] = $status_filter; $param_types .= 's'; }

$sql = "SELECT r.id as result_id, r.status, r.result_sheet_path, pu.name as pu_name, w.name as ward_name, l.name as lga_name, s.name as state_name, GROUP_CONCAT(CONCAT(pp.acronym, ':', ps.score) ORDER BY pp.acronym SEPARATOR ', ') as scores FROM results r JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id JOIN lgas l ON w.lga_id = l.id JOIN states s ON l.state_id = s.id LEFT JOIN party_scores ps ON ps.result_id = r.id LEFT JOIN political_parties pp ON ps.party_id = pp.id";
if (!empty($where_clauses)) { $sql .= " WHERE " . implode(' AND ', $where_clauses); }
$sql .= " GROUP BY r.id ORDER BY r.submitted_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) { $stmt->bind_param($param_types, ...$params); }
$stmt->execute();
$results_query = $stmt->get_result();

$states_query = $conn->query("SELECT id, name FROM states ORDER BY name");
$lgas_data = [];
$lgas_result = $conn->query("SELECT id, name, state_id FROM lgas ORDER BY name");
if ($lgas_result) {
    while($row = $lgas_result->fetch_assoc()) {
        $lgas_data[] = $row;
    }
}

require_once '../includes/admin_header.php';
?>

<header class="flex justify-between items-center mb-8">
    <h1 class="font-display text-3xl font-bold text-gray-800">All Submitted Results</h1>
    <a href="../core/export_csv.php" class="bg-inec-green text-white font-semibold py-2 px-4 rounded-md hover:opacity-90 transition"><i class="bi bi-download mr-2"></i> Export to CSV</a>
</header>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="results.php" method="GET" id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <select name="state_id" id="state-filter" class="w-full p-2 border border-gray-300 rounded-md bg-slate-50">
            <option value="">Filter by State</option>
            <?php if ($states_query): while($state = $states_query->fetch_assoc()): ?>
                <option value="<?php echo $state['id']; ?>" <?php if($state_filter == $state['id']) echo 'selected'; ?>><?php echo htmlspecialchars($state['name']); ?></option>
            <?php endwhile; endif; ?>
        </select>
        <select name="lga_id" id="lga-filter" class="w-full p-2 border border-gray-300 rounded-md bg-slate-50" disabled>
            <option value="">Filter by LGA</option>
            <?php foreach($lgas_data as $lga): ?>
                <option value="<?php echo $lga['id']; ?>" data-state-id="<?php echo $lga['state_id']; ?>" class="hidden" <?php if($lga_filter == $lga['id']) echo 'selected'; ?>><?php echo htmlspecialchars($lga['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="w-full p-2 border border-gray-300 rounded-md bg-slate-50"><option value="">Filter by Status</option><option value="pending" <?php if($status_filter == 'pending') echo 'selected'; ?>>Pending</option><option value="verified" <?php if($status_filter == 'verified') echo 'selected'; ?>>Verified</option><option value="flagged" <?php if($status_filter == 'flagged') echo 'selected'; ?>>Flagged</option></select>
        <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700">Filter</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
            <tr><th class="px-6 py-3">Polling Unit</th><th class="px-6 py-3">LGA</th><th class="px-6 py-3">State</th><th class="px-6 py-3">Scores</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-center">Actions</th></tr>
        </thead>
        <tbody>
            <?php if ($results_query && $results_query->num_rows > 0): ?>
                <?php while($row = $results_query->fetch_assoc()): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($row['pu_name']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['lga_name']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['state_name']); ?></td>
                        <td class="px-6 py-4 font-mono text-xs"><?php echo htmlspecialchars($row['scores'] ?? 'N/A'); ?></td>
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
                            <button class="view-btn text-blue-600 p-1" data-pu-name="<?php echo htmlspecialchars($row['pu_name']); ?>" data-location="<?php echo htmlspecialchars($row['ward_name'] . ', ' . $row['lga_name'] . ', ' . $row['state_name']); ?>" data-scores="<?php echo htmlspecialchars($row['scores'] ?? ''); ?>" data-image-path="../<?php echo htmlspecialchars($row['result_sheet_path']); ?>"><i class="bi bi-eye-fill text-lg"></i></button>
                            <button class="action-btn" data-action="verify" data-result-id="<?php echo $row['result_id']; ?>"><i class="bi bi-check-circle-fill text-green-500 text-lg"></i></button>
                            <button class="action-btn" data-action="flag" data-result-id="<?php echo $row['result_id']; ?>"><i class="bi bi-flag-fill text-orange-500 text-lg"></i></button>
                            <button class="action-btn" data-action="delete" data-result-id="<?php echo $row['result_id']; ?>"><i class="bi bi-trash-fill text-red-600 text-lg"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="px-6 py-4 text-center text-slate-500">No results found for the selected filters.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="view-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 transition-opacity hidden pointer-events-none">
    <div id="view-modal-content" class="bg-white rounded-xl shadow-2xl w-full max-w-4xl transform transition-all relative mx-4">
        <button id="close-view-modal-btn" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg text-2xl"></i></button>
        <div class="p-8">
            <h2 id="modal-title" class="font-display font-bold text-2xl text-slate-800"></h2><p id="modal-location" class="text-slate-500 mb-6"></p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><h3 class="font-bold text-lg mb-2">Submitted Scores</h3><div id="modal-scores" class="bg-slate-50 p-4 rounded-lg border text-sm font-mono"></div></div>
                <div><h3 class="font-bold text-lg mb-2">Scanned Result Sheet</h3><div class="border rounded-lg overflow-hidden bg-slate-100"><img id="modal-image" src="" alt="Scanned Result Sheet" class="w-full h-auto"></div></div>
            </div>
        </div>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 transition-opacity hidden pointer-events-none">
    <div id="confirm-modal-content" class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all p-8 text-center">
        <h2 id="confirm-title" class="font-display font-bold text-2xl text-slate-800"></h2><p id="confirm-text" class="text-slate-600 my-4"></p>
        <div class="flex justify-center gap-4"><button id="confirm-cancel-btn" class="bg-slate-200 font-semibold w-full py-2 rounded-lg">Cancel</button><button id="confirm-ok-btn" class="text-white font-semibold w-full py-2 rounded-lg"></button></div>
    </div>
</div>

<form id="action-form" method="POST" action="results.php?<?php echo http_build_query($_GET); ?>" class="hidden">
    <input type="hidden" name="action" id="action-input"><input type="hidden" name="result_id" id="result-id-input">
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lgaFilter = document.getElementById('lga-filter');
    const stateFilter = document.getElementById('state-filter');
    const lgaOptions = Array.from(lgaFilter.options);
    function updateLgaFilter() {
        const selectedStateId = stateFilter.value;
        lgaFilter.value = '';
        lgaOptions.forEach(opt => opt.classList.toggle('hidden', opt.value !== '' && opt.dataset.stateId !== selectedStateId));
        lgaFilter.disabled = !selectedStateId;
    }
    stateFilter.addEventListener('change', updateLgaFilter);
    if (stateFilter.value) updateLgaFilter();

    const viewModal = document.getElementById('view-modal');
    const viewModalContent = document.getElementById('view-modal-content');
    const showModal = (modal, content) => {
        modal.classList.remove('hidden', 'pointer-events-none');
        setTimeout(() => { modal.classList.remove('opacity-0'); content.classList.remove('opacity-0', 'scale-95'); }, 10);
    };
    const hideModal = (modal, content) => {
        modal.classList.add('opacity-0');
        content.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden', 'pointer-events-none'), 300);
    };

    document.getElementById('close-view-modal-btn').addEventListener('click', () => hideModal(viewModal, viewModalContent));

    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('modal-title').textContent = `Result for ${button.dataset.puName}`;
            document.getElementById('modal-location').textContent = button.dataset.location;
            document.getElementById('modal-image').src = button.dataset.imagePath;
            const scoresHtml = '<ul class="space-y-1">' + (button.dataset.scores.length ? button.dataset.scores.split(', ').map(s => `<li class="flex justify-between"><span>${s.split(':')[0]}</span><span class="font-bold">${s.split(':')[1]}</span></li>`).join('') : '<li>No scores recorded.</li>') + '</ul>';
            document.getElementById('modal-scores').innerHTML = scoresHtml;
            showModal(viewModal, viewModalContent);
        });
    });

    const confirmModal = document.getElementById('confirm-modal');
    const confirmModalContent = document.getElementById('confirm-modal-content');
    const confirmTitle = document.getElementById('confirm-title');
    const confirmText = document.getElementById('confirm-text');
    const confirmOkBtn = document.getElementById('confirm-ok-btn');
    const confirmCancelBtn = document.getElementById('confirm-cancel-btn');
    const actionForm = document.getElementById('action-form');
    
    confirmCancelBtn.addEventListener('click', () => hideModal(confirmModal, confirmModalContent));
    
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', e => {
            const { action, resultId } = e.currentTarget.dataset;
            const actions = {
                verify: { title: 'Verify Result?', text: 'Mark as officially verified.', ok: 'Yes, Verify', class: 'bg-green-500' },
                flag: { title: 'Flag Result?', text: 'Flag this result for secondary review.', ok: 'Yes, Flag', class: 'bg-orange-500' },
                delete: { title: 'Delete Result?', text: 'This action is permanent.', ok: 'Yes, Delete', class: 'bg-inec-red' }
            };
            const config = actions[action];
            confirmTitle.textContent = config.title;
            confirmText.textContent = config.text;
            confirmOkBtn.textContent = config.ok;
      
            confirmOkBtn.className = `text-white font-semibold w-full py-2 rounded-lg ${config.class}`;
            
            confirmOkBtn.onclick = () => {
                document.getElementById('action-input').value = action;
                document.getElementById('result-id-input').value = resultId;
                actionForm.submit();
            };
            showModal(confirmModal, confirmModalContent);
        });
    });
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>
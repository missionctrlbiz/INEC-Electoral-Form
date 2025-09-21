<?php
require_once '../core/db_connect.php';
require_admin();

// --- BREADCRUMB AND LOCATION DATA FETCHING ---
$state_id = isset($_GET['state_id']) ? (int)$_GET['state_id'] : null;
$lga_id = isset($_GET['lga_id']) ? (int)$_GET['lga_id'] : null;
$ward_id = isset($_GET['ward_id']) ? (int)$_GET['ward_id'] : null;

$breadcrumbs = [['name' => 'National', 'link' => 'national-summary.php']];
$page_title = 'National Summary of Results';
$current_level = 'national';
$location_name = '';

if ($ward_id) {
    $current_level = 'ward';
    $stmt = $conn->prepare("SELECT w.name as ward_name, l.id as lga_id, l.name as lga_name, s.id as state_id, s.name as state_name FROM wards w JOIN lgas l ON w.lga_id = l.id JOIN states s ON l.state_id = s.id WHERE w.id = ?");
    $stmt->bind_param("i", $ward_id); $stmt->execute();
    $loc_result = $stmt->get_result()->fetch_assoc();
    $page_title = "Polling Unit Results in {$loc_result['ward_name']} Ward";
    $breadcrumbs[] = ['name' => $loc_result['state_name'], 'link' => "national-summary.php?state_id={$loc_result['state_id']}"];
    $breadcrumbs[] = ['name' => $loc_result['lga_name'], 'link' => "national-summary.php?lga_id={$loc_result['lga_id']}"];
    $breadcrumbs[] = ['name' => $loc_result['ward_name'], 'link' => ""];
} elseif ($lga_id) {
    $current_level = 'lga';
    $stmt = $conn->prepare("SELECT l.name as lga_name, s.id as state_id, s.name as state_name FROM lgas l JOIN states s ON l.state_id = s.id WHERE l.id = ?");
    $stmt->bind_param("i", $lga_id); $stmt->execute();
    $loc_result = $stmt->get_result()->fetch_assoc();
    $page_title = "Ward Summary for {$loc_result['lga_name']} LGA";
    $breadcrumbs[] = ['name' => $loc_result['state_name'], 'link' => "national-summary.php?state_id={$loc_result['state_id']}"];
    $breadcrumbs[] = ['name' => $loc_result['lga_name'], 'link' => ""];
} elseif ($state_id) {
    $current_level = 'state';
    $stmt = $conn->prepare("SELECT name FROM states WHERE id = ?");
    $stmt->bind_param("i", $state_id); $stmt->execute();
    $location_name = $stmt->get_result()->fetch_assoc()['name'];
    $page_title = "LGA Summary for {$location_name} State";
    $breadcrumbs[] = ['name' => $location_name, 'link' => ""];
}

// --- TOP PARTIES LOGIC ---
$all_parties = [];
$party_totals_result = $conn->query("SELECT p.id, p.acronym, SUM(ps.score) as total_votes FROM political_parties p JOIN party_scores ps ON p.id = ps.party_id GROUP BY p.id, p.acronym ORDER BY total_votes DESC");
while($row = $party_totals_result->fetch_assoc()) { $all_parties[] = $row; }
$top_parties = array_slice($all_parties, 0, 7);
$other_parties = array_slice($all_parties, 7);

// --- DYNAMIC SQL QUERY CONSTRUCTION ---
$select_clauses = ["SUM(r.total_valid_votes) as total_votes", "SUM(r.accredited_voters) as accredited_voters"];
foreach ($all_parties as $party) { $select_clauses[] = "SUM(CASE WHEN ps.party_id = ".(int)$party['id']." THEN ps.score ELSE 0 END) AS party_".(int)$party['id']; }
$select_sql = implode(', ', $select_clauses);
$sql = ""; $param_type = ""; $param_value = null;

switch ($current_level) {
    case 'ward': $sql = "SELECT pu.id, pu.name, $select_sql FROM results r JOIN party_scores ps ON r.id = ps.result_id JOIN polling_units pu ON r.polling_unit_id = pu.id WHERE pu.ward_id = ? GROUP BY pu.id, pu.name ORDER BY pu.name ASC"; $param_type = "i"; $param_value = $ward_id; break;
    case 'lga': $sql = "SELECT w.id, w.name, $select_sql FROM results r JOIN party_scores ps ON r.id = ps.result_id JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id WHERE w.lga_id = ? GROUP BY w.id, w.name ORDER BY w.name ASC"; $param_type = "i"; $param_value = $lga_id; break;
    case 'state': $sql = "SELECT l.id, l.name, $select_sql FROM results r JOIN party_scores ps ON r.id = ps.result_id JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id JOIN lgas l ON w.lga_id = l.id WHERE l.state_id = ? GROUP BY l.id, l.name ORDER BY l.name ASC"; $param_type = "i"; $param_value = $state_id; break;
    default: $sql = "SELECT s.id, s.name, $select_sql FROM results r JOIN party_scores ps ON r.id = ps.result_id JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id JOIN lgas l ON w.lga_id = l.id JOIN states s ON l.state_id = s.id GROUP BY s.id, s.name ORDER BY s.name ASC"; break;
}

$stmt = $conn->prepare($sql);
if ($param_type && $param_value) { $stmt->bind_param($param_type, $param_value); }
$stmt->execute();
$data_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once '../includes/admin_header.php';
?>
<main class="flex-1 p-6 bg-slate-100">
    <header class="mb-6">
        <h1 class="font-display text-3xl font-bold text-slate-900"><?php echo htmlspecialchars($page_title); ?></h1>
        <nav class="text-sm font-medium mt-2" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <li class="flex items-center">
                        <?php if (!empty($crumb['link'])): ?><a href="<?php echo $crumb['link']; ?>" class="text-inec-green hover:underline"><?php echo htmlspecialchars($crumb['name']); ?></a>
                        <?php else: ?><span class="text-slate-500"><?php echo htmlspecialchars($crumb['name']); ?></span><?php endif; ?>
                        <?php if ($index < count($breadcrumbs) - 1): ?><i class="bi bi-chevron-right text-slate-400 mx-2"></i><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
    </header>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="relative w-full md:w-1/3"><i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i><input type="text" id="filter-input" class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-inec-green" placeholder="Filter results..."></div>
            <?php if (count($other_parties) > 0): ?><button id="toggle-parties-btn" class="bg-slate-100 text-slate-700 font-semibold px-4 py-2 rounded-lg hover:bg-slate-200 transition text-sm"><i class="bi bi-arrows-expand mr-2"></i>Show All Parties (<?php echo count($all_parties); ?>)</button><?php endif; ?>
        </div>
        <div class="overflow-x-auto">
            <table id="results-table" class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                    <tr>
                        <th class="px-6 py-3 cursor-pointer sortable" data-sort-dir="asc"><?php if ($current_level === 'national') echo 'State'; elseif ($current_level === 'state') echo 'LGA'; elseif ($current_level === 'lga') echo 'Ward'; else echo 'Polling Unit';?> <i class="bi bi-arrow-down-up ml-1"></i></th>
                        <th class="px-6 py-3 text-center cursor-pointer sortable" data-sort-dir="asc">Total Votes <i class="bi bi-arrow-down-up ml-1"></i></th>
                        <?php foreach ($top_parties as $party): ?><th class="px-6 py-3 text-center cursor-pointer sortable" data-sort-dir="asc"><?php echo htmlspecialchars($party['acronym']); ?> <i class="bi bi-arrow-down-up ml-1"></i></th><?php endforeach; ?>
                        <?php foreach ($other_parties as $party): ?><th class="px-6 py-3 text-center cursor-pointer sortable hidden toggleable-party-col" data-sort-dir="asc"><?php echo htmlspecialchars($party['acronym']); ?> <i class="bi bi-arrow-down-up ml-1"></i></th><?php endforeach; ?>
                        <?php if ($current_level === 'ward'): ?><th class="px-6 py-3 text-center">Status</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="results-tbody">
                    <?php if (empty($data_rows)): ?><tr><td colspan="100%" class="text-center py-10 text-slate-500">No results found for this level.</td></tr><?php endif; ?>
                    <?php foreach ($data_rows as $row): ?>
                    <tr class="bg-white border-b hover:bg-slate-50">
                        <td class="px-6 py-4 font-bold text-slate-800">
                            <?php $next_link = '#'; $data_level = ''; if ($current_level === 'national') { $next_link = "national-summary.php?state_id={$row['id']}"; $data_level = 'state'; } elseif ($current_level === 'state') { $next_link = "national-summary.php?lga_id={$row['id']}"; $data_level = 'lga'; } elseif ($current_level === 'lga') { $next_link = "national-summary.php?ward_id={$row['id']}"; $data_level = 'ward'; } else { $data_level = 'pu'; } ?>
                            <a href="<?php echo $next_link; ?>" class="summary-link text-inec-green hover:underline" data-id="<?php echo $row['id']; ?>" data-level="<?php echo $data_level; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>"><?php echo htmlspecialchars($row['name']); ?></a>
                        </td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-700"><?php echo number_format($row['total_votes']); ?></td>
                        <?php foreach ($top_parties as $party): ?><td class="px-6 py-4 text-center"><?php echo number_format($row['party_' . $party['id']]); ?></td><?php endforeach; ?>
                        <?php foreach ($other_parties as $party): ?><td class="px-6 py-4 text-center hidden toggleable-party-col"><?php echo number_format($row['party_' . $party['id']]); ?></td><?php endforeach; ?>
                        <?php if ($current_level === 'ward'): ?>
                            <td class="px-6 py-4 text-center">
                                <!-- MODIFICATION: Static "Transmitted" badge -->
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Transmitted</span>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<!-- Detailed Summary Modal -->
<div id="summary-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden transition-opacity duration-300 opacity-0">
    <div id="modal-content" class="bg-slate-50 rounded-xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] flex flex-col">
        <header class="p-4 border-b bg-white rounded-t-xl flex justify-between items-center"><h3 id="modal-title" class="font-display font-bold text-xl text-slate-800">Detailed Summary</h3><button id="modal-close-btn" class="text-slate-500 hover:text-slate-800 text-2xl">&times;</button></header>
        <div id="modal-body" class="p-6 overflow-y-auto"><div class="text-center py-10"><p class="text-slate-500">Loading details...</p></div></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('results-table');
    const tbody = document.getElementById('results-tbody');
    const filterInput = document.getElementById('filter-input');
    const toggleBtn = document.getElementById('toggle-parties-btn');

    // --- FEATURE 1: FILTER / SEARCH ---
    filterInput.addEventListener('keyup', () => {
        const query = filterInput.value.toLowerCase();
        tbody.querySelectorAll('tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });

    // --- FEATURE 2: TOGGLE ALL PARTIES ---
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = toggleBtn.dataset.toggled !== 'true';
            document.querySelectorAll('.toggleable-party-col').forEach(col => {
                col.classList.toggle('hidden', !isHidden);
            });
            toggleBtn.dataset.toggled = isHidden;
            toggleBtn.innerHTML = isHidden 
                ? '<i class="bi bi-arrows-collapse mr-2"></i>Show Top 7 Parties'
                : '<i class="bi bi-arrows-expand mr-2"></i>Show All Parties (<?php echo count($all_parties); ?>)';
        });
    }

    // --- FEATURE 3: TABLE SORTING ---
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const columnIndex = Array.from(header.parentNode.children).indexOf(header);
            const sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';
            header.dataset.sortDir = sortDir;
            
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                let aVal = a.children[columnIndex].textContent.trim();
                let bVal = b.children[columnIndex].textContent.trim();
                
                // Check if values are numeric (for scores) or string (for names)
                const isNum = !isNaN(aVal.replace(/,/g, '')) && aVal !== '';
                
                if (isNum) {
                    aVal = parseFloat(aVal.replace(/,/g, ''));
                    bVal = parseFloat(bVal.replace(/,/g, ''));
                }

                if (aVal < bVal) return sortDir === 'asc' ? -1 : 1;
                if (aVal > bVal) return sortDir === 'asc' ? 1 : -1;
                return 0;
            });

            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    });
    
    // --- FEATURE 4: MODAL FOR DETAILED VIEW ---
    const modal = document.getElementById('summary-modal');
    const modalContent = document.getElementById('modal-content');
    const modalBody = document.getElementById('modal-body');
    const modalTitle = document.getElementById('modal-title');
    const modalCloseBtn = document.getElementById('modal-close-btn');

    const showModal = () => {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('opacity-0', 'scale-95');
        }, 10);
    };

    const hideModal = () => {
        modal.classList.add('opacity-0');
        modalContent.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    };

    modalCloseBtn.addEventListener('click', hideModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) hideModal();
    });

    document.querySelectorAll('a.summary-link').forEach(link => {
        link.addEventListener('click', async (e) => {
            if (e.currentTarget.dataset.level === 'pu') return; // Don't open modal for PUs
            
            e.preventDefault();
            const id = e.currentTarget.dataset.id;
            const level = e.currentTarget.dataset.level;
            const name = e.currentTarget.dataset.name;

            modalTitle.textContent = `Detailed Summary for ${name}`;
            modalBody.innerHTML = '<p class="text-center py-10">Loading details...</p>';
            showModal();

            try {
                // We need to create this API endpoint file
                const response = await fetch(`api_get_summary.php?level=${level}&id=${id}`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();

                let partiesHtml = data.party_scores.map(party => `
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-medium text-slate-600">${party.acronym}</span>
                        <span class="font-bold text-slate-800">${parseInt(party.score).toLocaleString()}</span>
                    </div>
                `).join('');
                
                modalBody.innerHTML = `
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 text-center">
                        <div class="bg-white p-4 rounded-lg shadow-sm"><div class="text-xs text-slate-500 uppercase">Registered Voters</div><div class="text-2xl font-bold text-inec-green">${parseInt(data.summary.registered_voters || 0).toLocaleString()}</div></div>
                        <div class="bg-white p-4 rounded-lg shadow-sm"><div class="text-xs text-slate-500 uppercase">Accredited Voters</div><div class="text-2xl font-bold text-inec-green">${parseInt(data.summary.accredited_voters || 0).toLocaleString()}</div></div>
                        <div class="bg-white p-4 rounded-lg shadow-sm"><div class="text-xs text-slate-500 uppercase">Total Valid Votes</div><div class="text-2xl font-bold text-inec-green">${parseInt(data.summary.total_valid_votes || 0).toLocaleString()}</div></div>
                        <div class="bg-white p-4 rounded-lg shadow-sm"><div class="text-xs text-slate-500 uppercase">Submissions</div><div class="text-2xl font-bold text-inec-green">${parseInt(data.summary.total_submissions || 0).toLocaleString()}</div></div>
                    </div>
                    <h4 class="font-display font-bold text-lg text-slate-700 mb-2">Party Score Breakdown</h4>
                    <div class="bg-white p-4 rounded-lg shadow-sm">${partiesHtml}</div>
                `;
            } catch (error) {
                console.error('Fetch error:', error);
                modalBody.innerHTML = '<p class="text-center py-10 text-red-600">Could not load details. Please try again.</p>';
            }
        });
    });
});
</script>
<?php require_once '../includes/admin_footer.php'; ?>
<?php
require_once __DIR__ . '/db_connect.php';
// require_admin();

// --- DATA FETCHING (TWO-STEP PROCESS) ---

// Step 1: Get the main results data
$main_sql = "
    SELECT 
        r.id as result_id, s.name as state_name, l.name as lga_name, w.name as ward_name,
        pu.name as pu_name, pu.pu_code, r.status, r.registered_voters, r.accredited_voters,
        r.ballot_papers_issued, r.unused_ballots, r.spoiled_ballots, r.rejected_ballots,
        r.total_valid_votes, r.submitted_at, u.full_name as submitted_by
    FROM results r
    JOIN polling_units pu ON r.polling_unit_id = pu.id
    JOIN wards w ON pu.ward_id = w.id
    JOIN lgas l ON w.lga_id = l.id
    JOIN states s ON l.state_id = s.id
    JOIN users u ON r.submitted_by_user_id = u.id
    ORDER BY r.id ASC
";
$main_result = $conn->query($main_sql);
$results_data = [];
if ($main_result) {
    while ($row = $main_result->fetch_assoc()) {
        $results_data[$row['result_id']] = $row;
    }
}

// Step 2: Get all party scores and map them to their results
$scores_sql = "
    SELECT ps.result_id, pp.acronym, ps.score
    FROM party_scores ps
    JOIN political_parties pp ON ps.party_id = pp.id
";
$scores_result = $conn->query($scores_sql);
$scores_map = [];
if ($scores_result) {
    while ($row = $scores_result->fetch_assoc()) {
        $scores_map[$row['result_id']][$row['acronym']] = $row['score'];
    }
}


// --- CSV GENERATION ---
$filename = "inec_results_export_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Get all party acronyms for dynamic headers
$parties_query = $conn->query("SELECT acronym FROM political_parties ORDER BY acronym ASC");
$party_headers = [];
if ($parties_query) {
    while($party = $parties_query->fetch_assoc()) {
        $party_headers[] = $party['acronym'] . ' Score';
    }
}

// Define the full header row
$header = [
    'Result ID', 'State', 'LGA', 'Ward', 'Polling Unit', 'PU Code', 'Status',
    'Registered Voters', 'Accredited Voters', 'Ballot Papers Issued', 'Unused Ballots',
    'Spoiled Ballots', 'Rejected Ballots', 'Total Valid Votes', 'Submitted At', 'Submitted By'
];
$full_header = array_merge($header, $party_headers);
fputcsv($output, $full_header);

// Loop through the results and build each CSV row
foreach ($results_data as $result_id => $data) {
    $row = [
        $data['result_id'], $data['state_name'], $data['lga_name'], $data['ward_name'],
        $data['pu_name'], $data['pu_code'], ucfirst($data['status']),
        $data['registered_voters'], $data['accredited_voters'], $data['ballot_papers_issued'],
        $data['unused_ballots'], $data['spoiled_ballots'], $data['rejected_ballots'],
        $data['total_valid_votes'], $data['submitted_at'], $data['submitted_by']
    ];

    // Add scores for each party in the correct order
    foreach ($party_headers as $party_header) {
        $acronym = str_replace(' Score', '', $party_header);
        // Use the scores map to find the score, default to 0 if not found
        $row[] = $scores_map[$result_id][$acronym] ?? 0;
    }

    fputcsv($output, $row);
}

fclose($output);
exit;
?>
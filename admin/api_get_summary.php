<?php
header('Content-Type: application/json');
require_once '../core/db_connect.php';
// Security Check: Ensure only logged-in administrators can access this API.
require_admin();

$level = $_GET['level'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($level) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

$response = [
    'summary' => [],
    'party_scores' => []
];

// --- SUMMARY DATA ---
$summary_sql = "";
switch($level) {
    case 'state':
        $summary_sql = "SELECT COUNT(r.id) as total_submissions, SUM(r.registered_voters) as registered_voters, SUM(r.accredited_voters) as accredited_voters, SUM(r.total_valid_votes) as total_valid_votes FROM results r JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id JOIN lgas l ON w.lga_id = l.id WHERE l.state_id = ?";
        break;
    case 'lga':
        $summary_sql = "SELECT COUNT(r.id) as total_submissions, SUM(r.registered_voters) as registered_voters, SUM(r.accredited_voters) as accredited_voters, SUM(r.total_valid_votes) as total_valid_votes FROM results r JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id WHERE w.lga_id = ?";
        break;
    case 'ward':
        $summary_sql = "SELECT COUNT(r.id) as total_submissions, SUM(r.registered_voters) as registered_voters, SUM(r.accredited_voters) as accredited_voters, SUM(r.total_valid_votes) as total_valid_votes FROM results r JOIN polling_units pu ON r.polling_unit_id = pu.id WHERE pu.ward_id = ?";
        break;
}

if ($summary_sql) {
    $stmt = $conn->prepare($summary_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $response['summary'] = $stmt->get_result()->fetch_assoc();
}


// --- PARTY SCORE BREAKDOWN ---
$scores_sql = "";
switch($level) {
    case 'state':
        $scores_sql = "SELECT p.acronym, SUM(ps.score) as score FROM party_scores ps JOIN political_parties p ON ps.party_id = p.id JOIN results r ON ps.result_id = r.id JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id JOIN lgas l ON w.lga_id = l.id WHERE l.state_id = ? GROUP BY p.acronym ORDER BY score DESC";
        break;
    case 'lga':
        $scores_sql = "SELECT p.acronym, SUM(ps.score) as score FROM party_scores ps JOIN political_parties p ON ps.party_id = p.id JOIN results r ON ps.result_id = r.id JOIN polling_units pu ON r.polling_unit_id = pu.id JOIN wards w ON pu.ward_id = w.id WHERE w.lga_id = ? GROUP BY p.acronym ORDER BY score DESC";
        break;
    case 'ward':
         $scores_sql = "SELECT p.acronym, SUM(ps.score) as score FROM party_scores ps JOIN political_parties p ON ps.party_id = p.id JOIN results r ON ps.result_id = r.id JOIN polling_units pu ON r.polling_unit_id = pu.id WHERE pu.ward_id = ? GROUP BY p.acronym ORDER BY score DESC";
        break;
}

if ($scores_sql) {
    $stmt = $conn->prepare($scores_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $response['party_scores'] = $result->fetch_all(MYSQLI_ASSOC);
}

echo json_encode($response);
?>
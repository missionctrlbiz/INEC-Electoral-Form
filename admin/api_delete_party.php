<?php
header('Content-Type: application/json');
require_once '../core/db_connect.php';
require_admin();

function json_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$party_id = isset($_POST['party_id']) ? (int)$_POST['party_id'] : 0;
if ($party_id <= 0) {
    json_response(false, 'Invalid Party ID.');
}

// First, get the logo path to delete the file
$logo_stmt = $conn->prepare("SELECT logo_path FROM political_parties WHERE id = ?");
$logo_stmt->bind_param("i", $party_id);
$logo_stmt->execute();
$logo_path = $logo_stmt->get_result()->fetch_assoc()['logo_path'];

if ($logo_path && file_exists('../' . $logo_path)) {
    unlink('../' . $logo_path);
}

// Then, delete the party from the database
$stmt = $conn->prepare("DELETE FROM political_parties WHERE id = ?");
$stmt->bind_param("i", $party_id);

if ($stmt->execute()) {
    json_response(true, 'Party deleted successfully.');
} else {
    // Check for foreign key constraint errors
    if($stmt->errno == 1451) {
         json_response(false, 'Cannot delete this party because it is already associated with election results.');
    }
    json_response(false, 'Database error: Could not delete party.');
}
?>
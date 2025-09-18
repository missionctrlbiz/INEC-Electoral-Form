<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Include the database connection
require_once 'db_connect.php';

// Get the parameters from the URL
$level = $_GET['level'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($level) || $id <= 0) {
    echo json_encode([]);
    exit;
}

$data = [];
$sql = '';
$param_type = 'i';
$param_value = $id;

// Determine which locations to fetch based on the 'level' parameter
switch ($level) {
    case 'lga':
        $sql = "SELECT id, name FROM lgas WHERE state_id = ? ORDER BY name ASC";
        break;
    case 'ward':
        $sql = "SELECT id, name FROM wards WHERE lga_id = ? ORDER BY name ASC";
        break;
    default:
        // If the level is not 'lga' or 'ward', return an empty array
        echo json_encode([]);
        exit;
}

// Prepare and execute the query securely
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($param_type, $param_value);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all results into an associative array
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
}

// Return the data as a JSON response
echo json_encode($data);
?>
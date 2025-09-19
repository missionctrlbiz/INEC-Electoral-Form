<?php
// Set content type to JSON and include dependencies
header('Content-Type: application/json');
require_once '../core/db_connect.php';

// Security check: ensure only logged-in admins can access this API
require_admin();

// Get and validate the ID from the URL query parameter (e.g., ...?id=5)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    // If no valid ID is provided, send a clear error response
    echo json_encode(['success' => false, 'message' => 'Invalid Party ID provided.']);
    exit;
}

// Prepare a secure SQL statement to fetch the party details
$stmt = $conn->prepare("SELECT id, name, acronym, logo_path FROM political_parties WHERE id = ?");
if (!$stmt) {
    // Handle potential SQL errors during preparation
    error_log("SQL Prepare Error: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Database query failed during preparation.']);
    exit;
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    // Handle errors during execution
    error_log("SQL Execute Error: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Database query failed during execution.']);
    exit;
}

$result = $stmt->get_result();

// Check if a party was found
if ($party = $result->fetch_assoc()) {
    // If found, send a success response with the party data
    echo json_encode(['success' => true, 'party' => $party]);
} else {
    // If no party with that ID exists, send a not found error
    echo json_encode(['success' => false, 'message' => 'Party with the specified ID was not found.']);
}

// Close the statement
$stmt->close();
?>
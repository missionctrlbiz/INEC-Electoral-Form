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

$party_id = isset($_POST['party_id']) && !empty($_POST['party_id']) ? (int)$_POST['party_id'] : null;
$name = trim($_POST['name'] ?? '');
$acronym = trim($_POST['acronym'] ?? '');

if (empty($name) || empty($acronym)) {
    json_response(false, 'Party Name and Acronym are required.');
}

$logo_path = null;
$new_logo_uploaded = false;

// --- FILE UPLOAD LOGIC ---
// This section only runs if a file is actually submitted.
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $new_logo_uploaded = true;
    $target_dir = "../assets/images/parties/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    $file = $_FILES['logo'];
    if ($file['size'] > 1024 * 1024) { // 1 MB
        json_response(false, 'Logo file is too large. Maximum size is 1MB.');
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        json_response(false, 'Invalid file type. Only JPG, PNG, and WEBP are allowed.');
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_acronym = preg_replace('/[^a-zA-Z0-9]/', '', $acronym); // Sanitize acronym for filename
    $new_filename = strtolower($safe_acronym) . '_' . time() . '.' . $extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $logo_path = "assets/images/parties/" . $new_filename;
    } else {
        json_response(false, 'Failed to upload logo.');
    }
}

// --- DATABASE LOGIC ---
if ($party_id) { // This is an UPDATE operation
    
    // --- START OF THE CRITICAL FIX ---
    if ($new_logo_uploaded) {
        // SCENARIO A: A new logo WAS uploaded.
        // Get the path of the old logo to delete it from the server.
        $old_logo_stmt = $conn->prepare("SELECT logo_path FROM political_parties WHERE id = ?");
        $old_logo_stmt->bind_param("i", $party_id);
        $old_logo_stmt->execute();
        $old_logo_path = $old_logo_stmt->get_result()->fetch_assoc()['logo_path'];
        if ($old_logo_path && file_exists('../' . $old_logo_path)) {
            unlink('../' . $old_logo_path);
        }
        $old_logo_stmt->close();

        // Prepare a query to update name, acronym, AND the new logo_path.
        $stmt = $conn->prepare("UPDATE political_parties SET name = ?, acronym = ?, logo_path = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $acronym, $logo_path, $party_id);
    } else {
        // SCENARIO B: No new logo was uploaded.
        // Prepare a query that ONLY updates name and acronym. It DOES NOT touch the logo_path column.
        // This is the key to preserving the existing image.
        $stmt = $conn->prepare("UPDATE political_parties SET name = ?, acronym = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $acronym, $party_id);
    }
    // --- END OF THE CRITICAL FIX ---

    $message = 'Party updated successfully.';

} else { // This is an INSERT operation for a new party
    $sql = "INSERT INTO political_parties (name, acronym, logo_path) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $acronym, $logo_path);
    $message = 'Party added successfully.';
}

// Execute the appropriate prepared statement
if ($stmt->execute()) {
    json_response(true, $message);
} else {
    json_response(false, 'Database error: ' . $stmt->error);
}

$stmt->close();
?>
<?php
// Include the database connection and session/security functions.
require_once 'db_connect.php';

// --- SECURITY CHECK ---
// Ensure the user is a logged-in clerk.
require_login();
if ($_SESSION['role'] !== 'clerk') {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Check if the form was submitted via POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../data-entry.php");
    exit;
}

// --- FILE UPLOAD LOGIC (ROBUST VERSION) ---
// Define the target directory using an absolute path for reliability.
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
$result_sheet_path = '';

// Check if the directory exists and is writable.
if (!is_dir(UPLOAD_DIR) || !is_writable(UPLOAD_DIR)) {
    $_SESSION['error'] = "Server configuration error: The upload directory does not exist or is not writable.";
    header("Location: ../data-entry.php");
    exit;
}

// Check if a file was uploaded and there are no initial errors.
if (isset($_FILES['result_sheet']) && $_FILES['result_sheet']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['result_sheet']['tmp_name'];
    $file_name = basename($_FILES['result_sheet']['name']);
    $file_size = $_FILES['result_sheet']['size'];
    
    // --- SERVER-SIDE VALIDATION ---
    // 1. Check file size (e.g., max 5MB).
    if ($file_size > 5 * 1024 * 1024) { // 5 MB
        $_SESSION['error'] = "File is too large. Maximum size is 5MB.";
        header("Location: ../data-entry.php");
        exit;
    }

    // 2. Check file type (allow only common image formats).
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];
    $file_mime_type = mime_content_type($file_tmp_name);
    
    if (!in_array($file_mime_type, $allowed_mime_types)) {
        $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and WEBP images are allowed.";
        header("Location: ../data-entry.php");
        exit;
    }

    // Create a unique filename to prevent overwriting existing files.
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $unique_file_name = uniqid('result_', true) . '.' . $file_extension;
    $target_file_path = UPLOAD_DIR . $unique_file_name;

    // Try to move the uploaded file to its final destination.
    if (move_uploaded_file($file_tmp_name, $target_file_path)) {
        // Store the relative path for the database.
        $result_sheet_path = 'uploads/' . $unique_file_name;
    } else {
        $_SESSION['error'] = "Sorry, there was an error moving the uploaded file. Check server permissions.";
        header("Location: ../data-entry.php");
        exit;
    }
} else {
    // Handle specific upload errors from PHP.
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];
    $error_code = $_FILES['result_sheet']['error'] ?? UPLOAD_ERR_NO_FILE;
    $_SESSION['error'] = $upload_errors[$error_code] ?? 'An unknown upload error occurred.';
    header("Location: ../data-entry.php");
    exit;
}

// --- DATA SANITIZATION & PREPARATION ---
$polling_unit_id = (int)$_POST['polling_unit_id'];
$submitted_by_user_id = (int)$_SESSION['user_id'];
$registered_voters = (int)$_POST['registered_voters'];
$accredited_voters = (int)$_POST['accredited_voters'];
$ballot_papers_issued = (int)$_POST['ballot_papers_issued'];
$unused_ballots = (int)$_POST['unused_ballots'];
$spoiled_ballots = (int)$_POST['spoiled_ballots'];
$rejected_ballots = (int)$_POST['rejected_ballots'];
// Recalculate total valid votes on the server as a security measure.
$party_scores = $_POST['party_scores'] ?? [];
$total_valid_votes = 0;
foreach ($party_scores as $score) {
    $total_valid_votes += (int)$score;
}


// --- DATABASE TRANSACTION ---
$conn->begin_transaction();
try {
    // Step 1: Insert into the main results table
    $sql_results = "INSERT INTO results 
                    (polling_unit_id, submitted_by_user_id, registered_voters, accredited_voters, 
                     ballot_papers_issued, unused_ballots, spoiled_ballots, rejected_ballots, 
                     total_valid_votes, result_sheet_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_results = $conn->prepare($sql_results);
    $stmt_results->bind_param(
        "iiiiiiisis",
        $polling_unit_id,
        $submitted_by_user_id,
        $registered_voters,
        $accredited_voters,
        $ballot_papers_issued,
        $unused_ballots,
        $spoiled_ballots,
        $rejected_ballots,
        $total_valid_votes,
        $result_sheet_path
    );
    $stmt_results->execute();

    // Step 2: Get the ID of the result we just inserted
    $result_id = $conn->insert_id;

    // Step 3: Insert into the party_scores table
    $sql_scores = "INSERT INTO party_scores (result_id, party_id, score) VALUES (?, ?, ?)";
    $stmt_scores = $conn->prepare($sql_scores);
    
    foreach ($party_scores as $party_id => $score) {
        $sanitized_party_id = (int)$party_id;
        $sanitized_score = (int)$score;
        // Only insert if the party ID is valid.
        if ($sanitized_party_id > 0) {
            $stmt_scores->bind_param("iii", $result_id, $sanitized_party_id, $sanitized_score);
            $stmt_scores->execute();
        }
    }
    
    $stmt_results->close();
    $stmt_scores->close();

    // If we made it this far without errors, commit the transaction
    $conn->commit();
    
    // Redirect to the thank you page
    header("Location: ../thank-you.php");
    exit;

} catch (mysqli_sql_exception $exception) {
    // If any part of the transaction fails, roll it back
    $conn->rollback();
    
    // Log the detailed error for the developer
    error_log("Database Transaction Error: " . $exception->getMessage());
    
    // Set a user-friendly error message and redirect back to the form
    $_SESSION['error'] = "Database error: Could not save the result. Please try again.";
    header("Location: ../data-entry.php");
    exit;
}
?>
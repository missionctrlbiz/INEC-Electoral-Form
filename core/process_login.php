<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// Include the database connection, which also starts the session
require_once __DIR__ . '/db_connect.php';

// A utility function to send a standardized JSON response and stop execution
function send_json_response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message] + $data);
    exit;
}

// Ensure the script is called with the POST method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_json_response(false, 'Invalid request method.');
}

// Ensure the required fields are present
if (!isset($_POST['pu_code']) || !isset($_POST['pin'])) {
    send_json_response(false, 'Polling Unit Code and PIN are required.');
}

// --- STEP 1: VERIFY PU CODE AND PIN ---

// Sanitize inputs
$pu_code = trim($_POST['pu_code']);
$pin = $_POST['pin'];

// Prepare a secure query to find the active clerk for the given polling unit
$sql = "SELECT u.id, u.phone_number, u.pin FROM users u
        JOIN polling_units pu ON u.polling_unit_id = pu.id
        WHERE pu.pu_code = ? AND u.role = 'clerk' AND u.status = 'active'";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Handle SQL error
    error_log("SQL Prepare Error: " . $conn->error);
    send_json_response(false, 'A server error occurred. Please try again later.');
}

$stmt->bind_param("s", $pu_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verify the submitted PIN against the hashed PIN in the database
    if (password_verify($pin, $user['pin'])) {
        // --- CREDENTIALS CORRECT ---
        
        // Store the user ID in a temporary session variable for the next verification step.
        // This prevents users from trying to verify a phone number for an account they haven't authenticated for.
        $_SESSION['verification_user_id'] = $user['id'];
        
        // Send a success response back to the JavaScript, including the last 4 digits of the phone number.
        // This will be displayed in the verification modal.
        send_json_response(true, 'Credentials verified. Please enter the last 4 digits of your phone number.', [
            'phone_ending' => substr($user['phone_number'], -4)
        ]);
    }
}

// If we reach this point, either the pu_code was not found or the PIN was incorrect.
// For security, we provide a generic error message.
send_json_response(false, 'Invalid Polling Unit Code or PIN.');
?>
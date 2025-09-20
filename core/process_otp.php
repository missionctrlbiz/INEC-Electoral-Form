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

// --- STEP 2: VERIFY PHONE DIGITS ---

// Security Check: Ensure the user has completed the first login step.
// If this session variable isn't set, they are trying to bypass the first step.
if (!isset($_SESSION['verification_user_id'])) {
    send_json_response(false, 'Authentication process not initiated. Please start over.');
}

// Ensure the 4 digits were actually submitted from the modal form.
if (!isset($_POST['phone_digits']) || strlen(trim($_POST['phone_digits'])) !== 4) {
    send_json_response(false, 'Please enter the 4 digits.');
}

$submitted_digits = trim($_POST['phone_digits']);
$userId = $_SESSION['verification_user_id'];

// Fetch the user's full details to verify the phone number and create the final, persistent session.
$sql = "SELECT id, full_name, email, role, polling_unit_id, phone_number FROM users WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // Get the actual last 4 digits from the database to compare against.
    $correct_digits = substr($user['phone_number'], -4);

    if ($submitted_digits === $correct_digits) {
        // --- VERIFICATION SUCCESSFUL ---

        // Regenerate the session ID to prevent session fixation attacks.
        session_regenerate_id(true);

        // Set the final, persistent session variables that will be used across the application.
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['polling_unit_id'] = $user['polling_unit_id'];

        // Clean up the temporary verification variable, as it's no longer needed.
        unset($_SESSION['verification_user_id']);

        // Send a success response. The JavaScript in main.js will see this and redirect the user.
        send_json_response(true, 'Verification successful. Redirecting...');
    } else {
        // Digits did not match. Send a clear error message.
        send_json_response(false, 'The digits you entered are incorrect. Please try again.');
    }
} else {
    // This is a rare case but good for security. It means the user was deactivated between steps.
    send_json_response(false, 'User account not found or has been deactivated.');
}
?>
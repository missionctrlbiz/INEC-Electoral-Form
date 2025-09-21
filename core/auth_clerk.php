<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

// A utility function to send a standardized JSON response and stop execution
function send_json_response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message] + $data);
    exit;
}

// Ensure we have a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_json_response(false, 'Invalid request method.');
}

// Use a master try-catch block to ensure a JSON response is always returned
try {
    // --- SINGLE STEP AUTHENTICATION ---
    if (!isset($_POST['pu_code']) || !isset($_POST['pin'])) {
        throw new Exception('Polling Unit Code and PIN are required.');
    }

    $pu_code = trim($_POST['pu_code']);
    $pin = trim($_POST['pin']);
    
    // Fetch the full user details needed for the session
    $sql = "SELECT u.id, u.full_name, u.email, u.role, u.polling_unit_id, u.pin 
            FROM users u
            JOIN polling_units pu ON u.polling_unit_id = pu.id
            WHERE pu.pu_code = ? AND u.role = 'clerk' AND u.status = 'active'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pu_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the PIN
        if (password_verify($pin, $user['pin'])) {
            // --- LOGIN SUCCESSFUL ---
            // Immediately create the final, persistent user session.
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['polling_unit_id'] = $user['polling_unit_id'];
            $_SESSION['last_activity'] = time(); // Set initial activity for session timeout

            // Send a success response. The JavaScript will handle the redirect.
            send_json_response(true, 'Login successful. Redirecting...');
        }
    }

    // If we reach this point, either the user was not found or the PIN was incorrect.
    throw new Exception('Invalid Polling Unit Code or PIN.');

} catch (Exception $e) {
    // If any error occurs, destroy any partial session and send an error response.
    session_destroy();
    send_json_response(false, $e->getMessage());
}
?>
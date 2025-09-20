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
    $step = $_POST['step'] ?? null;

    if ($step === '1') {
        // --- STEP 1: VERIFY PU CODE AND PIN ---
        if (!isset($_POST['pu_code']) || !isset($_POST['pin'])) {
            throw new Exception('Polling Unit Code and PIN are required.');
        }

        $pu_code = trim($_POST['pu_code']);
        $pin = $_POST['pin'];
        
        $sql = "SELECT u.id, u.phone_number, u.pin FROM users u
                JOIN polling_units pu ON u.polling_unit_id = pu.id
                WHERE pu.pu_code = ? AND u.role = 'clerk' AND u.status = 'active'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $pu_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($pin, $user['pin'])) {
                // Credentials are correct. Store user ID in a temporary session for step 2.
                $_SESSION['verification_user_id'] = $user['id'];
                send_json_response(true, 'Credentials verified.', ['phone_ending' => substr($user['phone_number'], -4)]);
            }
        }
        // If we reach here, credentials were wrong.
        throw new Exception('Invalid Polling Unit Code or PIN.');

    } elseif ($step === '2') {
        // --- STEP 2: VERIFY PHONE DIGITS ---
        if (!isset($_SESSION['verification_user_id'])) {
            throw new Exception('Authentication process not initiated. Please start over.');
        }
        if (!isset($_POST['phone_digits']) || strlen(trim($_POST['phone_digits'])) !== 4) {
            throw new Exception('Please enter the 4 digits.');
        }
        
        $submitted_digits = trim($_POST['phone_digits']);
        $userId = $_SESSION['verification_user_id'];

        $sql = "SELECT id, full_name, email, role, polling_unit_id, phone_number FROM users WHERE id = ? AND status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $correct_digits = substr($user['phone_number'], -4);

            if ($submitted_digits === $correct_digits) {
                // Verification successful. Create the final user session.
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['polling_unit_id'] = $user['polling_unit_id'];
                unset($_SESSION['verification_user_id']); // Clean up temp variable
                send_json_response(true, 'Verification successful. Redirecting...');
            } else {
                throw new Exception('The digits you entered are incorrect.');
            }
        } else {
            throw new Exception('User account not found or deactivated.');
        }
    } else {
        throw new Exception('Invalid authentication step.');
    }
} catch (Exception $e) {
    // If any error occurs at any point, destroy the session to be safe and send an error response.
    session_destroy();
    send_json_response(false, $e->getMessage());
}
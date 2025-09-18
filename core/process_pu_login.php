<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

function send_json_response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message] + $data);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_json_response(false, 'Invalid request method.');
}

$pu_code = $conn->real_escape_string($_POST['pu_code']);
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
        // PIN is correct. Store details needed for the next step in the session.
        $last_four_digits = substr($user['phone_number'], -4);
        $_SESSION['verification_user_id'] = $user['id'];
        $_SESSION['correct_phone_ending'] = $last_four_digits;
        
        // Send a success response back to JavaScript.
        send_json_response(true, 'Credentials verified.');
    }
}

send_json_response(false, 'Invalid Polling Unit Code or PIN.');
?>
<?php
require_once __DIR__ . '/db_connect.php';

// Check if a user is in the middle of the verification process.
if (!isset($_SESSION['verification_user_id']) || !isset($_SESSION['correct_phone_ending'])) {
    // If not, send them back to the start with an error.
    $_SESSION['error_message'] = "Verification process timed out. Please start over.";
    header("Location: ../polling-unit-login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submitted_code = $_POST['verification_code'] ?? '';

    // Check if the submitted code matches the one we stored in the session.
    if ($submitted_code === $_SESSION['correct_phone_ending']) {
        // SUCCESS! The user is verified.

        $user_id = $_SESSION['verification_user_id'];
        $sql = "SELECT id, full_name, email, role, polling_unit_id FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Clear the temporary verification session variables.
        unset($_SESSION['verification_user_id']);
        unset($_SESSION['correct_phone_ending']);
        
        // Create the final, secure, logged-in session.
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['polling_unit_id'] = $user['polling_unit_id'];
        
        // Redirect to the data entry page.
        header("Location: ../data-entry.php");
        exit;
    } else {
        // FAILURE: The code was wrong. Set an error and send them back.
        $_SESSION['error_message'] = "The 4-digit verification code was incorrect. Please try again.";
        header("Location: ../polling-unit-login.php");
        exit;
    }
}
?>
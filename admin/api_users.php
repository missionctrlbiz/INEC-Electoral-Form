<?php
header('Content-Type: application/json');
require_once '../core/db_connect.php';
require_admin();

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    $action = $_REQUEST['action'] ?? null;

    switch ($action) {
        case 'get':
            // This case remains unchanged, it is working correctly.
            $userId = (int)$_GET['user_id'];
            $stmt = $conn->prepare("SELECT u.*, pu.name as pu_name FROM users u LEFT JOIN polling_units pu ON u.polling_unit_id = pu.id WHERE u.id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                unset($user['password'], $user['pin']);
                $response = ['success' => true, 'user' => $user];
            } else {
                throw new Exception('User not found.');
            }
            break;

        case 'save':
            // This case remains unchanged, it is working correctly.
            $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $fullName = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone_number']);
            $role = $_POST['role'];
            $status = $_POST['status'];
            
            if (empty($fullName) || empty($email) || empty($phone)) throw new Exception("Name, Email, and Phone are required.");
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Invalid email format.");

            if ($userId) { // UPDATE
                $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone_number=?, role=?, status=?, polling_unit_id=? WHERE id=?");
                $pu_id = ($role === 'clerk') ? (int)$_POST['polling_unit_id'] : null;
                $stmt->bind_param("sssssii", $fullName, $email, $phone, $role, $status, $pu_id, $userId);
                $stmt->execute();

                if ($role === 'admin' && !empty($_POST['password'])) {
                    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt_pass = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                    $stmt_pass->bind_param("si", $hash, $userId);
                    $stmt_pass->execute();
                }
                if ($role === 'clerk' && !empty($_POST['pin'])) {
                     if (!preg_match('/^\d{4}$/', $_POST['pin'])) throw new Exception("PIN must be exactly 4 digits.");
                    $hash = password_hash($_POST['pin'], PASSWORD_DEFAULT);
                    $stmt_pin = $conn->prepare("UPDATE users SET pin=? WHERE id=?");
                    $stmt_pin->bind_param("si", $hash, $userId);
                    $stmt_pin->execute();
                }

            } else { // CREATE
                $pin_hash = null;
                $pass_hash = null;
                $pu_id = null;

                if ($role === 'clerk') {
                    if (empty($_POST['pin'])) throw new Exception("A 4-digit PIN is required for new clerks.");
                    if (!preg_match('/^\d{4}$/', $_POST['pin'])) throw new Exception("PIN must be exactly 4 digits.");
                    if (empty($_POST['polling_unit_id'])) throw new Exception("A Polling Unit must be assigned to new clerks.");
                    $pin_hash = password_hash($_POST['pin'], PASSWORD_DEFAULT);
                    $pu_id = (int)$_POST['polling_unit_id'];
                } else {
                    if (empty($_POST['password'])) throw new Exception("A password is required for new admins.");
                    $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone_number, role, status, password, pin, polling_unit_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssi", $fullName, $email, $phone, $role, $status, $pass_hash, $pin_hash, $pu_id);
                $stmt->execute();
            }
            $response = ['success' => true, 'message' => 'User saved successfully.'];
            break;

        case 'delete':
            // --- START: MODIFIED DELETE LOGIC ---
            $userId = (int)$_POST['user_id'];
            if ($userId === (int)$_SESSION['user_id']) {
                throw new Exception("You cannot delete your own account.");
            }

            // 1. Check for related records in the 'results' table.
            $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM results WHERE submitted_by_user_id = ?");
            $check_stmt->bind_param("i", $userId);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();

            if ($result['count'] > 0) {
                // 2. If records exist, throw a user-friendly error.
                throw new Exception("This user cannot be deleted because they have submitted results. Please set their status to 'Inactive' instead.");
            } else {
                // 3. If no records exist, proceed with deletion.
                $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $delete_stmt->bind_param("i", $userId);
                $delete_stmt->execute();
                
                if ($delete_stmt->affected_rows > 0) {
                    $response = ['success' => true, 'message' => 'User deleted successfully.'];
                } else {
                    throw new Exception('Could not delete user or user not found.');
                }
            }
            // --- END: MODIFIED DELETE LOGIC ---
            break;

        default:
            throw new Exception('Invalid action specified.');
    }

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        $response['message'] = 'This email or phone number is already in use.';
    } else {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
<?php
// Include the database connection and robust session start.
require_once __DIR__ . '/db_connect.php';

// Check if the form was submitted via POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Query for an active admin user with the provided email.
    $sql = "SELECT id, full_name, password FROM users WHERE email = ? AND role = 'admin' AND status = 'active'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify the submitted password against the hashed password in the database.
        if (password_verify($password, $user['password'])) {
            // SUCCESS: Password is correct.
            
            // Regenerate session ID for security.
            session_regenerate_id(true); 
            
            // Store all necessary admin data in the session.
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = 'admin';
            
            // Redirect the user to the admin dashboard.
            header("Location: ../admin/dashboard.php");
            exit;
        }
    }
    
    // FAILURE: If we reach here, the login failed.
    $_SESSION['error_message'] = "Invalid email or password.";
    header("Location: ../admin/index.php");
    exit;

} else {
    // If not a POST request, redirect away.
    header("Location: ../admin/index.php");
    exit;
}
?>
<?php
// --- DATABASE CONNECTION ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inec_portal_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// --- ROBUST SESSION MANAGEMENT ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CORE HELPER FUNCTIONS ---
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = "You must be logged in to access that page.";
        // This function is called from the root directory, so this path is correct.
        header("Location: polling-unit-login.php");
        exit;
    }
}

// --- START: ADDED THE MISSING FUNCTION ---
/**
 * Protects an admin page.
 * Redirects to the admin login page if the user is not a logged-in administrator.
 */
function require_admin() {
    // The condition checks three things:
    // 1. Is a user logged in at all?
    // 2. Is their session role set?
    // 3. Is their role specifically 'admin'?
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        
        // For security, if the check fails, destroy any potentially invalid session.
        session_destroy();

        // Start a new session just to pass an error message to the login page.
        session_start();
        $_SESSION['error_message'] = "Admin access required. Please log in.";
        
        // This function is called from scripts inside the /admin/ folder,
        // so this relative path will correctly redirect to /admin/index.php
        header("Location: index.php");
        exit;
    }
}
// --- END: ADDED THE MISSING FUNCTION ---
?>
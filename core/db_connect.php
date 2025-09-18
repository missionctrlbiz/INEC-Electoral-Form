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
// This checks if a session has already been started before starting a new one.
// This prevents "headers already sent" errors and makes session handling reliable.
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
        header("Location: polling-unit-login.php");
        exit;
    }
}
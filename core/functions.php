<?php
/**
 * This file contains reusable helper functions for the entire application,
 * primarily focused on security and session management.
 */

/**
 * Checks if a user is currently logged in by looking for a session variable.
 *
 * @return bool True if a 'user_id' is set in the session, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Protects a page by requiring a user to be logged in.
 *
 * If the user is not logged in, it sets an error message, redirects them
 * to the appropriate login page based on where they were trying to go,
 * and stops script execution.
 */
function require_login() {
    if (!is_logged_in()) {
        // Clear any potentially lingering session data from a failed attempt.
        session_destroy();

        // Determine which login page to send the user to.
        // If the script is being run from inside the /admin/ directory...
        if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
            $redirect_url = "../index.php"; // Admin login is at admin/index.php, but we redirect to main site
        } else {
            $redirect_url = "polling-unit-login.php";
        }

        // Set a helpful error message for the user.
        $_SESSION['error_message'] = "You must be logged in to access that page.";

        header("Location: $redirect_url");
        exit; // Stop script execution immediately.
    }
}

/**
 * Protects a page, ensuring only users with the 'admin' role can access it.
 *
 * It first checks if the user is logged in at all, then checks their role.
 * If the check fails, it logs them out and redirects them to the home page.
 */
function require_admin() {
    // First, ensure the user is logged in.
    require_login();

    // Next, check if their role is 'admin'.
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // If not an admin, something is wrong. Destroy the session for security.
        session_destroy();
        header("Location: ../index.php"); // Redirect to the main landing page.
        exit; // Stop script execution.
    }
}
?>
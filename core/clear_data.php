<?php
// Include the database connection and helper functions.
require_once __DIR__ . '/db_connect.php';

// --- SECURITY CHECK ---
// This is the most important part. Only a logged-in admin can run this script.
require_admin();

// To prevent accidental runs via GET request, ensure this is a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/dashboard.php');
    exit;
}

// --- DATABASE ACTION ---
$conn->begin_transaction();
try {
    // Disable foreign key checks temporarily to avoid errors.
    $conn->query('SET FOREIGN_KEY_CHECKS=0;');
    $conn->query('TRUNCATE TABLE `party_scores`;');
    $conn->query('TRUNCATE TABLE `results`;');
    $conn->query('SET FOREIGN_KEY_CHECKS=1;');
    $conn->commit();

    // Set a success message to display on the dashboard.
    $_SESSION['success_message'] = "All demo result data has been successfully cleared.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "An error occurred while clearing data: " . $e->getMessage();
}

// Redirect back to the dashboard.
header('Location: ../admin/dashboard.php');
exit;
?>
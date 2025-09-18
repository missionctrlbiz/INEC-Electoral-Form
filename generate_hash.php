<?php
// This script is for one-time use to generate a correct password/PIN hash.

// The plain text PIN you want to hash.
$plainPin = '1234';

// Use PHP's standard password hashing function.
// PASSWORD_DEFAULT is the recommended algorithm, which is currently bcrypt.
// This is the same algorithm that password_verify() expects.
$hashedPin = password_hash($plainPin, PASSWORD_DEFAULT);

// Display the new hash.
echo "<h1>New Secure Hash Generated</h1>";
echo "<p>Plain Text PIN: " . htmlspecialchars($plainPin) . "</p>";
echo "<p><strong>Copy this entire line below and paste it into the 'pin' column in your database:</strong></p>";
echo "<textarea rows='3' style='width: 100%; font-family: monospace; font-size: 16px;'>" . htmlspecialchars($hashedPin) . "</textarea>";

?>
<?php
$plain_pin = '1234';
$hash_from_db = '$2y$10$E5b.A3c2d1e0f9g8h7i6j5k4l3m2n1o0p9q8r7s6t5u4'; // Copied from your dummy data for user John Doe

if (password_verify($plain_pin, $hash_from_db)) {
    echo "The PIN is CORRECT. The hashing is working.";
} else {
    echo "The PIN is INCORRECT. There is a problem with the hash.";
}
?>
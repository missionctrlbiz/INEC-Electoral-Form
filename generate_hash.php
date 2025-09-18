<?php
// This script generates a secure hash for a given password or PIN.

// 1. SET THE PLAIN TEXT PASSWORD/PIN
// Change this value to whatever you want to hash.
$plainText = 'admin123';

// 2. GENERATE THE HASH
$hashedText = password_hash($plainText, PASSWORD_DEFAULT);

// 3. DISPLAY THE RESULT
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Hash Generator</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f9; color: #333; padding: 2em; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 2em; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { color: #006A4E; }
        p { font-size: 1.1em; }
        textarea { 
            width: 100%; 
            font-family: monospace; 
            font-size: 1.2em; 
            padding: 10px; 
            border: 2px solid #ccc; 
            border-radius: 4px; 
            resize: none; 
            margin-top: 10px;
        }
        strong { color: #D40028; }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Secure Hash Generated</h1>
        <p>Plain Text Input: <strong><?php echo htmlspecialchars($plainText); ?></strong></p>
        <p>Copy the entire hash string below and paste it into the 'password' column for your admin user:</p>
        <textarea rows="3" readonly onclick="this.select();"><?php echo htmlspecialchars($hashedText); ?></textarea>
    </div>
</body>
</html>
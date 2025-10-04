<?php
// MySQL connection details
$host = "sql101.infinityfree.com";   // MySQL Hostname
$user = "if0_39829885";             // MySQL Username
$pass = "alingremy108";             // MySQL Password
$db   = "if0_39829885_db";         // MySQL Database Name

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    // Using trigger_error will be caught by the custom error handler
    trigger_error("Database connection failed: " . mysqli_connect_error(), E_USER_ERROR);
}
?>
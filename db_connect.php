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
    $error_message = "Database connection failed: " . mysqli_connect_error() . "\n";
    file_put_contents("error_log.txt", $error_message, FILE_APPEND);
    trigger_error($error_message, E_USER_ERROR);
}
?>